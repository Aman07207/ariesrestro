<?php

namespace Tests\Feature;

use App\Enums\BillStatus;
use App\Enums\HotelStatus;
use App\Enums\SessionStatus;
use App\Enums\TableStatus;
use App\Enums\UserRole;
use App\Models\Bill;
use App\Models\Hotel;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\OrderSession;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableSettlementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function waiter(Hotel $hotel): User
    {
        $u = User::factory()->create(['role' => UserRole::Waiter, 'hotel_id' => $hotel->id, 'employee_id' => 'WTR-'.fake()->unique()->numberBetween(1000, 9999)]);
        $u->assignRole('waiter');

        return $u;
    }

    private function setupHotel(): array
    {
        $hotel = Hotel::factory()->create(['status' => HotelStatus::Active, 'gst_rate' => 5]);
        $cat = MenuCategory::create(['hotel_id' => $hotel->id, 'name' => 'Main', 'type' => 'food', 'display_order' => 1]);
        $item = MenuItem::create(['hotel_id' => $hotel->id, 'category_id' => $cat->id, 'name' => 'Dal Makhani', 'price' => 200, 'veg_type' => 'veg', 'is_available' => true, 'tax_track' => 'gst']);
        $table = Table::factory()->create(['hotel_id' => $hotel->id, 'table_number' => 2]);

        return compact('hotel', 'item', 'table');
    }

    private function scanAndOrder(Hotel $hotel, Table $table, MenuItem $item): array
    {
        $scan = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies = [];
        foreach ($scan->headers->getCookies() as $c) {
            $cookies[$c->getName()] = $c->getValue();
        }
        $this->withUnencryptedCookies($cookies)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
        ])->assertOk();

        return $cookies;
    }

    public function test_paying_online_ends_the_session_and_the_next_guest_gets_a_clean_one(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'table' => $table] = $this->setupHotel();
        $first = $this->scanAndOrder($hotel, $table, $item);

        $this->withUnencryptedCookies($first)->post(route('customer.pay.confirm'))->assertRedirect(route('customer.success'));

        $session = OrderSession::firstOrFail();
        $this->assertEquals(SessionStatus::Paid, $session->status);
        $this->assertEquals(TableStatus::Available, $table->fresh()->status);
        $this->assertEquals('success', Payment::firstOrFail()->status->value);

        // Thank-you page still opens right after paying...
        $this->withUnencryptedCookies($first)->get(route('customer.success'))->assertOk();
        // ...but the old guest can no longer order, see the menu, or view the bill.
        $this->withUnencryptedCookies($first)->get(route('customer.menu'))->assertRedirect(route('customer.scan-help'));
        $this->withUnencryptedCookies($first)->withCredentials()->postJson(route('customer.order.place'), [
            'items' => [['menu_item_id' => $item->id, 'quantity' => 1]],
        ])->assertRedirect(route('customer.scan-help'));

        // Five minutes later a new guest scans the same QR: brand-new session, Member 1, no old orders.
        $second = $this->get(route('customer.scan', ['hotel' => $hotel->slug, 'table_uuid' => $table->table_uuid]));
        $cookies2 = [];
        foreach ($second->headers->getCookies() as $c) {
            $cookies2[$c->getName()] = $c->getValue();
        }
        $this->assertSame(2, OrderSession::count());
        $newSession = OrderSession::latest('id')->first();
        $this->assertEquals(SessionStatus::Active, $newSession->status);
        $this->assertSame(1, $newSession->member_count);
        $this->withUnencryptedCookies($cookies2)->get(route('customer.track'))->assertOk()->assertDontSee('Dal Makhani');
    }

    public function test_waiter_settles_a_table_with_a_payment_mode(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'table' => $table] = $this->setupHotel();
        $this->scanAndOrder($hotel, $table, $item);
        $waiter = $this->waiter($hotel);

        $this->actingAs($waiter)->postJson(route('waiter.tables.settle', $table), ['method' => 'cash'])
            ->assertOk()->assertJsonPath('redirect', route('waiter.tables'));

        $bill = Bill::firstOrFail();
        $this->assertEquals(BillStatus::Paid, $bill->status);
        $this->assertEquals('210.00', $bill->grand_total); // 200 + 5% GST
        $payment = Payment::firstOrFail();
        $this->assertSame('cash', $payment->method);
        $this->assertEquals('210.00', $payment->amount);
        $this->assertEquals(SessionStatus::Paid, OrderSession::firstOrFail()->status);
        $this->assertEquals(TableStatus::Available, $table->fresh()->status);

        // Nothing left to settle a second time.
        $this->actingAs($waiter)->postJson(route('waiter.tables.settle', $table), ['method' => 'cash'])->assertStatus(422);
        $this->actingAs($waiter)->postJson(route('waiter.tables.settle', $table), ['method' => 'bitcoin'])->assertStatus(422);
    }

    public function test_waiter_can_close_a_table_without_payment(): void
    {
        ['hotel' => $hotel, 'item' => $item, 'table' => $table] = $this->setupHotel();
        $this->scanAndOrder($hotel, $table, $item);

        $this->actingAs($this->waiter($hotel))->postJson(route('waiter.tables.close', $table))->assertOk();

        $this->assertEquals(SessionStatus::Closed, OrderSession::firstOrFail()->status);
        $this->assertEquals(TableStatus::Available, $table->fresh()->status);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_waiter_cannot_settle_or_close_another_hotels_table(): void
    {
        ['hotel' => $hotelA, 'item' => $item, 'table' => $tableA] = $this->setupHotel();
        ['hotel' => $hotelB] = $this->setupHotel();
        $this->scanAndOrder($hotelA, $tableA, $item);
        $waiterB = $this->waiter($hotelB);

        $this->actingAs($waiterB)->postJson(route('waiter.tables.settle', $tableA), ['method' => 'cash'])->assertNotFound();
        $this->actingAs($waiterB)->postJson(route('waiter.tables.close', $tableA))->assertNotFound();
        // Read unscoped: the hotel scope would (rightly) hide hotel A's session from waiter B.
        $this->assertEquals(SessionStatus::Active, OrderSession::withoutGlobalScopes()->firstOrFail()->status);
    }
}
