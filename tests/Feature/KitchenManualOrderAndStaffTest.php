<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\OrderItemStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KitchenManualOrderAndStaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function staff(UserRole $role, ?Hotel $hotel = null): User
    {
        $u = User::factory()->create(['role' => $role, 'hotel_id' => $hotel?->id, 'employee_id' => strtoupper($role->value).'-'.fake()->unique()->numberBetween(1000, 9999)]);
        $u->assignRole($role->value);

        return $u;
    }

    /** @return array{hotel: Hotel, table: Table, item: MenuItem} */
    private function hotelWithMenu(): array
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Paneer Tikka', 'price' => 200, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 3]);

        return compact('hotel', 'table', 'item');
    }

    public function test_waiter_manual_order_is_persisted_priced_server_side_and_visible_to_waiter_and_chef(): void
    {
        ['hotel' => $hotel, 'table' => $table, 'item' => $item] = $this->hotelWithMenu();
        $waiter = $this->staff(UserRole::Waiter, $hotel);

        $this->actingAs($waiter)->postJson(route('waiter.manual-order.store'), [
            'table_id' => $table->id,
            'items' => [['menu_item_id' => $item->id, 'quantity' => 2, 'price' => 1, 'note' => 'less spicy']],
        ])->assertOk()->assertJsonPath('redirect', route('waiter.tables.show', $table));

        $order = Order::where('table_id', $table->id)->firstOrFail();
        $this->assertEquals('400.00', $order->total_amount); // 2 x 200 — the submitted price of 1 is ignored
        $this->assertEquals(TableStatus::Active, $table->fresh()->status);

        $this->actingAs($waiter)->get(route('waiter.tables.show', $table))->assertOk()->assertSee('Paneer Tikka');
        $this->actingAs($this->staff(UserRole::Chef, $hotel))->get(route('chef.queue'))->assertOk()->assertSee('Paneer Tikka');
    }

    public function test_waiter_cannot_place_a_manual_order_on_another_hotels_table(): void
    {
        ['hotel' => $hotelA, 'item' => $itemA] = $this->hotelWithMenu();
        ['table' => $tableB] = $this->hotelWithMenu();

        $this->actingAs($this->staff(UserRole::Waiter, $hotelA))->postJson(route('waiter.manual-order.store'), [
            'table_id' => $tableB->id, 'items' => [['menu_item_id' => $itemA->id, 'quantity' => 1]],
        ])->assertNotFound();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_chef_advances_an_item_through_each_status_and_cannot_skip_or_cross_hotels(): void
    {
        ['hotel' => $hotel, 'table' => $table, 'item' => $item] = $this->hotelWithMenu();
        $waiter = $this->staff(UserRole::Waiter, $hotel);
        $this->actingAs($waiter)->postJson(route('waiter.manual-order.store'), ['table_id' => $table->id, 'items' => [['menu_item_id' => $item->id, 'quantity' => 1]]])->assertOk();
        $orderItem = OrderItem::firstOrFail();

        $chef = $this->staff(UserRole::Chef, $hotel);
        $this->actingAs($chef)->postJson(route('chef.queue.advance', $orderItem))->assertOk()->assertJsonPath('status', 'preparing');
        $this->actingAs($chef)->postJson(route('chef.queue.advance', $orderItem))->assertOk()->assertJsonPath('status', 'ready');
        $this->assertEquals(OrderItemStatus::Ready, $orderItem->fresh()->status);

        ['hotel' => $otherHotel] = $this->hotelWithMenu();
        $this->actingAs($this->staff(UserRole::Chef, $otherHotel))->postJson(route('chef.queue.advance', $orderItem))->assertForbidden();
    }

    public function test_chef_stock_toggle_persists_and_hides_the_item_from_the_customer_menu(): void
    {
        ['hotel' => $hotel, 'table' => $table, 'item' => $item] = $this->hotelWithMenu();

        $this->actingAs($this->staff(UserRole::Chef, $hotel))->postJson(route('chef.stock.toggle', $item))->assertOk()->assertJsonPath('is_available', false);
        $this->assertFalse($item->fresh()->is_available);

        auth()->logout();
        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = [];
        foreach ($scan->headers->getCookies() as $c) {
            $cookies[$c->getName()] = $c->getValue();
        }
        $this->withUnencryptedCookies($cookies)->get(route('customer.menu'))->assertOk()->assertDontSee('Paneer Tikka');
    }

    public function test_super_admin_manages_staff_across_hotels(): void
    {
        $superAdmin = $this->staff(UserRole::SuperAdmin);
        $hotel = Hotel::factory()->create();
        $other = Hotel::factory()->create();

        $this->actingAs($superAdmin)->post(route('superadmin.staff.store'), [
            'hotel_id' => $hotel->id, 'name' => 'Ravi Kumar', 'email' => 'ravi@example.test', 'employee_id' => 'WTR-9001',
            'role' => 'waiter', 'password' => 'password123',
        ])->assertRedirect(route('superadmin.staff.index'));

        $ravi = User::where('employee_id', 'WTR-9001')->firstOrFail();
        $this->assertEquals($hotel->id, $ravi->hotel_id);
        $this->assertTrue($ravi->hasRole('waiter'));

        // Move to another hotel and promote — no same-hotel restriction for Super Admin.
        $this->actingAs($superAdmin)->put(route('superadmin.staff.update', $ravi), [
            'hotel_id' => $other->id, 'name' => 'Ravi Kumar', 'email' => 'ravi@example.test', 'employee_id' => 'WTR-9001', 'role' => 'hotel_admin',
        ])->assertRedirect(route('superadmin.staff.index'));
        $this->assertEquals($other->id, $ravi->fresh()->hotel_id);
        $this->assertTrue($ravi->fresh()->hasRole('hotel_admin'));

        $this->actingAs($superAdmin)->get(route('superadmin.staff.index', ['hotel' => $other->id]))->assertOk()->assertSee('Ravi Kumar');
        $this->actingAs($superAdmin)->delete(route('superadmin.staff.destroy', $ravi))->assertRedirect(route('superadmin.staff.index'));
        $this->assertDatabaseMissing('users', ['id' => $ravi->id]);
    }

    public function test_only_super_admin_reaches_platform_staff_and_it_cannot_create_a_super_admin(): void
    {
        $hotel = Hotel::factory()->create();
        $this->actingAs($this->staff(UserRole::HotelAdmin, $hotel))->get(route('superadmin.staff.index'))->assertForbidden();

        $this->actingAs($this->staff(UserRole::SuperAdmin))->post(route('superadmin.staff.store'), [
            'hotel_id' => $hotel->id, 'name' => 'X', 'email' => 'x@example.test', 'employee_id' => 'X-1', 'role' => 'super_admin', 'password' => 'password123',
        ])->assertSessionHasErrors('role');
    }
}
