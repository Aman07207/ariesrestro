<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\OrderItemStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Whole dining journey through real HTTP + Livewire endpoints, as staff and guests would hit them. */
class EndToEndDiningFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function staff(UserRole $role, Hotel $hotel): User
    {
        $u = User::factory()->create(['role' => $role, 'hotel_id' => $hotel->id, 'employee_id' => strtoupper($role->value).'-'.fake()->unique()->numberBetween(1000, 9999)]);
        $u->assignRole($role->value);

        return $u;
    }

    private function cookies($response): array
    {
        $c = [];
        foreach ($response->headers->getCookies() as $cookie) {
            $c[$cookie->getName()] = $cookie->getValue();
        }

        return $c;
    }

    private function seedHotel(): array
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active, 'gst_rate' => 5]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main', 'type' => 'food', 'display_order' => 1]);
        $a = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Butter Chicken', 'price' => 350, 'veg_type' => 'non-veg', 'is_available' => true, 'tax_track' => 'gst']);
        $b = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Dal Makhani', 'price' => 240, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 4]);

        return compact('hotel', 'a', 'b', 'table');
    }

    public function test_full_journey_survives_reverb_being_down(): void
    {
        // Simulate the reported outage: real reverb driver pointed at a dead port.
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'k',
            'broadcasting.connections.reverb.secret' => 's',
            'broadcasting.connections.reverb.app_id' => '1',
            'broadcasting.connections.reverb.options' => ['host' => '127.0.0.1', 'port' => 1, 'scheme' => 'http', 'useTLS' => false],
        ]);
        ['hotel' => $hotel, 'a' => $a, 'b' => $b, 'table' => $table] = $this->seedHotel();

        // Customer scans and orders.
        $cookies = $this->cookies($this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid])));
        $this->withUnencryptedCookies($cookies)->get(route('customer.menu'))->assertOk()->assertSee('Butter Chicken');
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $a->id, 'quantity' => 1], ['menu_item_id' => $b->id, 'quantity' => 2]],
        ])->assertOk();

        // Chef sees both, advances one all the way, and the dead socket never causes a 500.
        $chef = $this->staff(UserRole::Chef, $hotel);
        $this->actingAs($chef)->get(route('chef.queue'))->assertOk()->assertSee('Butter Chicken')->assertSee('Dal Makhani');
        $item = OrderItem::where('menu_item_id', $a->id)->firstOrFail();
        $this->actingAs($chef)->postJson(route('chef.queue.advance', $item))->assertOk();
        $this->actingAs($chef)->postJson(route('chef.queue.advance', $item))->assertOk()->assertJsonPath('status', 'ready');

        // Waiter sees table active, cancels the still-pending dish.
        $waiter = $this->staff(UserRole::Waiter, $hotel);
        $this->actingAs($waiter)->get(route('waiter.tables'))->assertOk()->assertSee('Table 4');
        $this->actingAs($waiter)->get(route('waiter.tables.show', $table))->assertOk()->assertSee('Butter Chicken')->assertSee('ready');
        $pending = OrderItem::where('menu_item_id', $b->id)->firstOrFail();
        $this->actingAs($waiter)->postJson(route('waiter.order-items.cancel', $pending), ['reason' => 'Customer request'])->assertOk();
        $this->assertEquals(OrderItemStatus::Cancelled, $pending->fresh()->status);

        // Customer tracking + call waiter + bill all still render.
        $this->withUnencryptedCookies($cookies)->get(route('customer.track'))->assertOk()->assertSee('Butter Chicken');
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.call-waiter'), [])->assertSuccessful();
        $this->withUnencryptedCookies($cookies)->get(route('customer.bill'))->assertOk();
    }

    public function test_second_table_and_other_hotel_stay_isolated(): void
    {
        ['hotel' => $h1, 'a' => $a, 'table' => $t1] = $this->seedHotel();
        ['hotel' => $h2] = $this->seedHotel();

        $cookies = $this->cookies($this->get(route('customer.scan', ['hotel' => $h1->slug, 'table_uuid' => $t1->table_uuid])));
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $a->id, 'quantity' => 1]],
        ])->assertOk();

        $this->actingAs($this->staff(UserRole::Chef, $h2))->get(route('chef.queue'))->assertOk()->assertDontSee('Butter Chicken');
        $this->actingAs($this->staff(UserRole::Waiter, $h2))->get(route('waiter.tables.show', $t1))->assertNotFound();
    }

    public function test_out_of_stock_and_junk_orders_are_rejected(): void
    {
        ['hotel' => $hotel, 'a' => $a, 'table' => $table] = $this->seedHotel();
        $a->update(['is_available' => false]);
        $cookies = $this->cookies($this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid])));

        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), ['items' => [['menu_item_id' => $a->id, 'quantity' => 1]]])->assertStatus(422);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), ['items' => []])->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_unauthenticated_and_wrong_role_cannot_reach_staff_screens(): void
    {
        ['hotel' => $hotel] = $this->seedHotel();

        $this->get(route('chef.queue'))->assertRedirect();
        $this->get(route('waiter.tables'))->assertRedirect();
        $this->actingAs($this->staff(UserRole::Waiter, $hotel))->get(route('chef.queue'))->assertForbidden();
        $this->actingAs($this->staff(UserRole::Chef, $hotel))->get(route('waiter.tables'))->assertForbidden();
    }
}
