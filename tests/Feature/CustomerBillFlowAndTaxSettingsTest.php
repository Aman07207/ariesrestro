<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\HotelStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerBillFlowAndTaxSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeStaff(UserRole $role, ?Hotel $hotel = null): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'hotel_id' => $hotel?->id,
            'employee_id' => strtoupper($role->value).'-'.fake()->unique()->numberBetween(1000, 9999),
        ]);
        $user->assignRole($role->value);

        return $user;
    }

    /** @return array<string, string> */
    private function cookiesFrom($response): array
    {
        $cookies = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie->getValue();
        }

        return $cookies;
    }

    public function test_bill_page_defaults_service_charge_to_unchecked_and_the_toggle_recomputes_it(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active, 'gst_rate' => 5, 'default_service_charge_percent' => 10]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Paneer Tikka', 'price' => 200, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = $this->cookiesFrom($scan);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
        ])->assertOk();

        $bill = $this->withUnencryptedCookies($cookies)->get(route('customer.bill'));
        $bill->assertOk();
        $stored = Bill::where('hotel_id', $hotel->id)->latest('id')->first();
        $this->assertFalse($stored->service_charge_consent);
        $this->assertEquals('0.00', $stored->service_charge_amount);

        $this->withUnencryptedCookies($cookies)->withCredentials()->post(route('customer.bill.service-charge'), [
            'service_charge_consent' => '1',
        ])->assertRedirect(route('customer.bill'));

        $stored->refresh();
        $this->assertTrue($stored->service_charge_consent);
        $this->assertEquals('20.00', $stored->service_charge_amount); // 10% of 200
    }

    public function test_paying_freezes_the_bill_and_the_success_page_shows_the_frozen_total(): void
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active, 'gst_rate' => 5]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Paneer Tikka', 'price' => 200, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id]);

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = $this->cookiesFrom($scan);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
        ])->assertOk();

        $this->withUnencryptedCookies($cookies)->get(route('customer.bill'))->assertOk();
        $this->withUnencryptedCookies($cookies)->withCredentials()->post(route('customer.pay.confirm'))
            ->assertRedirect(route('customer.success'));

        $bill = Bill::where('hotel_id', $hotel->id)->latest('id')->firstOrFail();
        $this->assertEquals(BillStatus::Paid, $bill->status);

        $success = $this->withUnencryptedCookies($cookies)->get(route('customer.success'));
        $success->assertOk()->assertSee(number_format($bill->grand_total, 2));
    }

    public function test_hotel_admin_updates_their_own_tax_rates(): void
    {
        $hotel = Hotel::factory()->create(['gst_rate' => 5, 'vat_rate' => null, 'is_luxury_hotel' => false]);
        $admin = $this->makeStaff(UserRole::HotelAdmin, $hotel);

        $this->actingAs($admin)->put(route('hoteladmin.tax-settings.update'), [
            'gst_rate' => 18,
            'vat_rate' => 22,
            'is_luxury_hotel' => '1',
            'default_service_charge_percent' => 10,
        ])->assertRedirect(route('hoteladmin.tax-settings'));

        $hotel->refresh();
        $this->assertEquals('18.00', $hotel->gst_rate);
        $this->assertEquals('22.00', $hotel->vat_rate);
        $this->assertTrue($hotel->is_luxury_hotel);
    }

    public function test_super_admin_sets_tax_fields_directly_on_a_hotel_during_onboarding(): void
    {
        $superAdmin = $this->makeStaff(UserRole::SuperAdmin);

        $this->actingAs($superAdmin)->post(route('superadmin.hotels.store'), [
            'name' => 'The Grand Palace', 'slug' => 'the-grand-palace', 'subscription_plan' => 'premium',
            'is_luxury_hotel' => '1', 'gst_rate' => 18, 'vat_rate' => 25, 'default_service_charge_percent' => 10,
        ])->assertRedirect(route('superadmin.hotels.index'));

        $hotel = Hotel::where('slug', 'the-grand-palace')->firstOrFail();
        $this->assertTrue($hotel->is_luxury_hotel);
        $this->assertEquals('18.00', $hotel->gst_rate);
        $this->assertEquals('25.00', $hotel->vat_rate);
    }
}
