<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerQrSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function makeHotelWithTables(): Hotel
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 5]);
        Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 2]);

        return $hotel;
    }

    public function test_scanning_a_table_qr_starts_a_session_and_reaches_the_menu(): void
    {
        $hotel = $this->makeHotelWithTables();
        $table = Table::where('hotel_id', $hotel->id)->where('table_number', 5)->firstOrFail();

        $response = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $response->assertRedirect(route('customer.menu'));

        $menu = $this->withUnencryptedCookies($this->cookiesFrom($response))->get(route('customer.menu'));
        $menu->assertOk();
        $menu->assertSee('Table 5');
    }

    public function test_two_different_tables_resolve_to_two_different_sessions(): void
    {
        $hotel = $this->makeHotelWithTables();
        $table5 = Table::where('hotel_id', $hotel->id)->where('table_number', 5)->firstOrFail();
        $table2 = Table::where('hotel_id', $hotel->id)->where('table_number', 2)->firstOrFail();

        $resp5 = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table5->table_uuid]));
        $menu5 = $this->withUnencryptedCookies($this->cookiesFrom($resp5))->get(route('customer.menu'));
        $menu5->assertOk()->assertSee('Table 5');

        $resp2 = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table2->table_uuid]));
        $menu2 = $this->withUnencryptedCookies($this->cookiesFrom($resp2))->get(route('customer.menu'));
        $menu2->assertOk()->assertSee('Table 2');
    }

    public function test_revisiting_the_scan_url_with_the_same_cookies_reuses_the_member_number(): void
    {
        $hotel = $this->makeHotelWithTables();
        $table = Table::where('hotel_id', $hotel->id)->where('table_number', 5)->firstOrFail();

        $first = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = $this->cookiesFrom($first);

        // Revisiting from "the same browser" (same cookie jar) must not create a second
        // SessionMember row — it should recognize the existing member and reuse it.
        $this->withUnencryptedCookies($cookies)
            ->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));

        $session = $table->fresh()->orderSessions()->latest('id')->firstOrFail();
        $this->assertEquals(1, $session->members()->count());
        $this->assertEquals(1, $session->member_count);
    }

    public function test_visiting_menu_without_a_session_cookie_redirects_to_scan_help(): void
    {
        $this->get(route('customer.menu'))->assertRedirect(route('customer.scan-help'));
    }

    public function test_hotel_admin_can_view_a_qr_image_for_their_table_and_others_are_blocked(): void
    {
        $hotel = $this->makeHotelWithTables();
        $table = Table::where('hotel_id', $hotel->id)->where('table_number', 5)->firstOrFail();

        $admin = User::factory()->create(['role' => UserRole::HotelAdmin, 'hotel_id' => $hotel->id, 'employee_id' => 'ADM-1']);
        $admin->assignRole(UserRole::HotelAdmin->value);

        $this->actingAs($admin)->get(route('hoteladmin.tables.qr', $table))->assertOk();
        $this->actingAs($admin)->get(route('hoteladmin.tables.qr-image', $table))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/svg+xml');

        $waiter = User::factory()->create(['role' => UserRole::Waiter, 'hotel_id' => $hotel->id, 'employee_id' => 'WTR-1']);
        $waiter->assignRole(UserRole::Waiter->value);
        $this->actingAs($waiter)->get(route('hoteladmin.tables.qr', $table))->assertForbidden();
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
}
