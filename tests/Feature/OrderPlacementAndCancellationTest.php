<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\OrderItemStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class OrderPlacementAndCancellationTest extends TestCase
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

    /** @return array{hotel: Hotel, table: Table, food: MenuItem, drink: MenuItem} */
    private function makeHotelWithMenu(): array
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active, 'gst_rate' => 5]);
        $foodCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $drinkCat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Beverages', 'type' => 'beverage', 'display_order' => 2]);
        $food = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $foodCat->id, 'name' => 'Paneer Tikka', 'price' => 200, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $drink = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $drinkCat->id, 'name' => 'Cold Coffee', 'price' => 100, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 5]);

        return compact('hotel', 'table', 'food', 'drink');
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

    public function test_placing_an_order_creates_real_rows_with_server_computed_price(): void
    {
        ['hotel' => $hotel, 'table' => $table, 'food' => $food, 'drink' => $drink] = $this->makeHotelWithMenu();

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = $this->cookiesFrom($scan);

        $response = $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [
                // Client tries to lie about the price — must be ignored entirely.
                ['menu_item_id' => $food->id, 'quantity' => 2, 'price' => 1, 'note' => 'less spicy'],
                ['menu_item_id' => $drink->id, 'quantity' => 1],
            ],
        ]);

        $response->assertOk();

        $order = Order::where('table_id', $table->id)->firstOrFail();
        $this->assertCount(2, $order->orderItems);

        $foodItem = $order->orderItems->firstWhere('menu_item_id', $food->id);
        $this->assertEquals('200.00', $foodItem->price);
        $this->assertEquals('less spicy', $foodItem->note);

        // No tax on Order anymore — that's a session-level Bill concern now. Just the
        // raw item subtotal: 2*200 + 1*100 = 500.
        $this->assertEquals('500.00', $order->total_amount);
    }

    public function test_a_placed_order_is_visible_to_waiter_and_chef(): void
    {
        ['hotel' => $hotel, 'table' => $table, 'food' => $food] = $this->makeHotelWithMenu();

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = $this->cookiesFrom($scan);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $food->id, 'quantity' => 1]],
        ])->assertOk();

        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);
        $this->actingAs($waiter)->get(route('waiter.tables.show', $table))
            ->assertOk()
            ->assertSee('Paneer Tikka');

        $chef = $this->makeStaff(UserRole::Chef, $hotel);
        $this->actingAs($chef)->get(route('chef.queue'))
            ->assertOk()
            ->assertSee('Paneer Tikka');
    }

    public function test_waiter_can_cancel_a_pending_item_with_a_logged_reason(): void
    {
        ['hotel' => $hotel, 'table' => $table, 'food' => $food, 'drink' => $drink] = $this->makeHotelWithMenu();

        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = $this->cookiesFrom($scan);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [
                ['menu_item_id' => $food->id, 'quantity' => 1],
                ['menu_item_id' => $drink->id, 'quantity' => 1],
            ],
        ])->assertOk();

        $order = Order::where('table_id', $table->id)->firstOrFail();
        $foodItem = $order->orderItems->firstWhere('menu_item_id', $food->id);

        $waiter = $this->makeStaff(UserRole::Waiter, $hotel);
        $this->actingAs($waiter)->postJson(route('waiter.order-items.cancel', $foodItem), [
            'reason' => 'Item unavailable',
        ])->assertOk();

        $foodItem->refresh();
        $this->assertEquals(OrderItemStatus::Cancelled, $foodItem->status);
        $this->assertEquals('Item unavailable', $foodItem->cancel_reason);
        $this->assertEquals($waiter->id, $foodItem->cancelled_by);
        $this->assertTrue(Activity::where('log_name', 'order_item')->where('subject_id', $foodItem->id)->exists());
    }

    public function test_waiter_cannot_cancel_an_already_served_item_or_another_hotels_item(): void
    {
        ['hotel' => $hotelA, 'table' => $tableA, 'food' => $foodA] = $this->makeHotelWithMenu();
        ['hotel' => $hotelB] = $this->makeHotelWithMenu();

        $scan = $this->get(route('customer.scan', ['hotel' => $hotelA->slug, 'table_uuid' => $tableA->table_uuid]));
        $cookies = $this->cookiesFrom($scan);
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $foodA->id, 'quantity' => 1]],
        ])->assertOk();

        $order = Order::where('table_id', $tableA->id)->firstOrFail();
        $item = $order->orderItems->first();
        $item->update(['status' => OrderItemStatus::Served]);

        $waiterA = $this->makeStaff(UserRole::Waiter, $hotelA);
        $this->actingAs($waiterA)->postJson(route('waiter.order-items.cancel', $item), ['reason' => 'Other'])
            ->assertForbidden();

        $waiterB = $this->makeStaff(UserRole::Waiter, $hotelB);
        $item->update(['status' => OrderItemStatus::Pending]);
        $this->actingAs($waiterB)->postJson(route('waiter.order-items.cancel', $item), ['reason' => 'Other'])
            ->assertForbidden();
    }
}
