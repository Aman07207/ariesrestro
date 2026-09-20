<?php

namespace Tests\Feature;

use App\Enums\HotelStatus;
use App\Enums\OrderItemStatus;
use App\Enums\UserRole;
use App\Events\OrderItemStatusChanged;
use App\Events\OrderPlaced;
use App\Livewire\Chef\QueueBoard;
use App\Livewire\Customer\TrackOrder;
use App\Livewire\Waiter\TablesGrid;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\OrderSession;
use App\Models\Table;
use App\Models\User;
use App\Services\Customer\OrderPlacementService;
use App\Support\TrackChannel;
use Database\Seeders\RoleSeeder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class RealtimeOrderFlowTest extends TestCase
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

    /** @return array{hotel: Hotel, table: Table, item: MenuItem, session: OrderSession} */
    private function hotelWithSession(): array
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Starters', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Paneer Tikka', 'price' => 200, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 7]);
        $session = OrderSession::create(['hotel_id' => $hotel->id, 'table_id' => $table->id, 'member_count' => 1, 'status' => 'active']);

        return compact('hotel', 'table', 'item', 'session');
    }

    public function test_placing_an_order_broadcasts_to_the_hotel_private_staff_channel(): void
    {
        Event::fake([OrderPlaced::class]);
        ['hotel' => $hotel, 'item' => $item, 'session' => $session] = $this->hotelWithSession();

        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id, 'quantity' => 2]]);

        Event::assertDispatched(OrderPlaced::class, function (OrderPlaced $e) use ($hotel) {
            $channel = $e->broadcastOn()[0];

            return $channel instanceof PrivateChannel
                && $channel->name === "private-hotel.{$hotel->id}.orders"
                && $e->tableNumber === '7' && $e->itemCount === 1;
        });
    }

    public function test_a_failed_order_broadcasts_nothing(): void
    {
        Event::fake([OrderPlaced::class]);
        ['session' => $session] = $this->hotelWithSession();

        try {
            app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => 999999, 'quantity' => 1]]);
        } catch (\InvalidArgumentException) {
        }

        Event::assertNotDispatched(OrderPlaced::class);
    }

    public function test_status_change_goes_to_staff_and_to_the_customer_public_track_channel(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'session' => $session] = $this->hotelWithSession();
        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id]]);
        $orderItem = OrderItem::firstOrFail();

        Event::fake([OrderItemStatusChanged::class]);
        $this->actingAs($this->staff(UserRole::Chef, $hotel))->postJson(route('chef.queue.advance', $orderItem))->assertOk();

        Event::assertDispatched(OrderItemStatusChanged::class, function (OrderItemStatusChanged $e) use ($hotel, $session) {
            $names = array_map(fn ($c) => $c->name, $e->broadcastOn());

            return $e->status === 'preparing'
                && in_array("private-hotel.{$hotel->id}.orders", $names, true)
                && in_array(TrackChannel::name($session->id), $names, true)
                && $e->broadcastOn()[1] instanceof Channel;
        });
    }

    public function test_waiter_cancel_broadcasts_a_cancelled_status(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'session' => $session] = $this->hotelWithSession();
        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id]]);
        $orderItem = OrderItem::firstOrFail();

        Event::fake([OrderItemStatusChanged::class]);
        $this->actingAs($this->staff(UserRole::Waiter, $hotel))
            ->postJson(route('waiter.order-items.cancel', $orderItem), ['reason' => 'Customer request'])->assertOk();

        Event::assertDispatched(OrderItemStatusChanged::class, fn ($e) => $e->status === 'cancelled');
    }

    public function test_track_channel_key_is_deterministic_hidden_and_per_session(): void
    {
        ['session' => $a] = $this->hotelWithSession();
        ['session' => $b] = $this->hotelWithSession();

        $this->assertSame(TrackChannel::key($a->id), TrackChannel::key($a->id));
        $this->assertNotSame(TrackChannel::key($a->id), TrackChannel::key($b->id));
        $this->assertStringNotContainsString($a->session_token, TrackChannel::name($a->id));
    }

    public function test_staff_channel_authorization_is_per_hotel_and_staff_only(): void
    {
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'k',
            'broadcasting.connections.reverb.secret' => 's',
            'broadcasting.connections.reverb.app_id' => '1',
            'broadcasting.connections.reverb.options' => ['host' => 'localhost', 'port' => 8080, 'scheme' => 'http', 'useTLS' => false],
        ]);
        // Channel callbacks were registered on the (null) driver at boot; register them on the swapped-in one.
        require base_path('routes/channels.php');
        ['hotel' => $a] = $this->hotelWithSession();
        ['hotel' => $b] = $this->hotelWithSession();
        $auth = fn (User $u, Hotel $h) => $this->actingAs($u)->post('/broadcasting/auth', [
            'channel_name' => "private-hotel.{$h->id}.orders", 'socket_id' => '123.456',
        ]);

        $auth($this->staff(UserRole::Chef, $a), $a)->assertOk();
        $auth($this->staff(UserRole::Waiter, $a), $a)->assertOk();
        $auth($this->staff(UserRole::Chef, $a), $b)->assertForbidden();
        $auth($this->staff(UserRole::Waiter, $b), $a)->assertForbidden();
    }

    public function test_chef_board_detects_new_orders_and_advances_without_reload(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'session' => $session] = $this->hotelWithSession();
        $chef = $this->staff(UserRole::Chef, $hotel);

        $board = Livewire::actingAs($chef)->test(QueueBoard::class)->assertSee('Nothing here');

        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id]]);

        $board->call('detect')->assertDispatched('aries-notify', tone: 'chefOrder', message: 'New order — Table 7')->assertSee('Paneer Tikka');
        // Same state again → no second tone.
        $board->call('detect')->assertNotDispatched('aries-notify');

        $board->call('advance', OrderItem::firstOrFail()->id)->assertSee('Mark ready');
        $this->assertEquals(OrderItemStatus::Preparing, OrderItem::firstOrFail()->status);
    }

    public function test_other_roles_cannot_advance_via_the_chef_board(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'session' => $session] = $this->hotelWithSession();
        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id]]);

        Livewire::actingAs($this->staff(UserRole::Waiter, $hotel))->test(QueueBoard::class)
            ->call('advance', OrderItem::firstOrFail()->id)->assertForbidden();
        $this->assertEquals(OrderItemStatus::Pending, OrderItem::firstOrFail()->status);
    }

    public function test_waiter_grid_rings_for_new_orders_and_for_ready_items(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'session' => $session] = $this->hotelWithSession();
        $grid = Livewire::actingAs($this->staff(UserRole::Waiter, $hotel))->test(TablesGrid::class);

        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id]]);
        $grid->call('watchKitchen')->assertDispatched('aries-notify', tone: 'waiterOrder', message: 'New order received');

        OrderItem::firstOrFail()->update(['status' => OrderItemStatus::Ready]);
        $grid->call('watchKitchen')->assertDispatched('aries-notify', tone: 'ready', message: 'Paneer Tikka is ready — Table 7');
    }

    public function test_customer_tracker_updates_and_chimes_when_the_chef_moves_an_item(): void
    {
        ['item' => $item, 'session' => $session] = $this->hotelWithSession();
        app(OrderPlacementService::class)->place($session, 1, [['menu_item_id' => $item->id]]);

        $track = Livewire::test(TrackOrder::class, ['sessionId' => $session->id, 'memberNo' => 1])->assertSee('pending');

        OrderItem::firstOrFail()->update(['status' => OrderItemStatus::Ready]);
        $track->call('detect')->assertDispatched('aries-notify', tone: 'customer', message: 'Paneer Tikka is ready!')->assertSee('ready');
    }
}
