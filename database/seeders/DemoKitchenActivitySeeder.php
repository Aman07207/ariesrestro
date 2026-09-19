<?php

namespace Database\Seeders;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SessionStatus;
use App\Models\Hotel;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Extends the Table 5 demo (Section 5 of the brief) with activity on Tables 2, 3 and 7 —
 * found necessary once building the Chef/Waiter theme pages against the finalized
 * chef.html/waiter.html files, which show a populated kitchen queue and tables grid
 * across multiple tables, not just Table 5. Table 5's own Member-2-empty state (per the
 * brief's explicit instruction and index.html) is left untouched.
 */
class DemoKitchenActivitySeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();

        $vegBiryani = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Veg Biryani')->firstOrFail();
        $gulabJamun = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Gulab Jamun')->firstOrFail();
        $chickenWings = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Chicken Wings')->firstOrFail();
        $coldCoffee = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Cold Coffee')->firstOrFail();

        // Table 2 — no waiter call seeded for this one, just a normal active order.
        $table2 = Table::where('hotel_id', $hotel->id)->where('table_number', 2)->firstOrFail();
        $session2 = OrderSession::firstOrCreate(
            ['hotel_id' => $hotel->id, 'table_id' => $table2->id, 'status' => SessionStatus::Active],
            ['session_token' => (string) Str::uuid(), 'member_count' => 1]
        );
        $this->addOrderItem($session2, $table2, $vegBiryani, 2, OrderItemStatus::Preparing);

        // Table 3 — reuse the session WaiterCallSeeder already created for the pending call.
        $table3 = Table::where('hotel_id', $hotel->id)->where('table_number', 3)->firstOrFail();
        $session3 = OrderSession::where('hotel_id', $hotel->id)->where('table_id', $table3->id)
            ->where('status', SessionStatus::Active)->latest('id')->firstOrFail();
        $this->addOrderItem($session3, $table3, $gulabJamun, 4, OrderItemStatus::Ready);

        // Table 7 — same, reuse WaiterCallSeeder's session.
        $table7 = Table::where('hotel_id', $hotel->id)->where('table_number', 7)->firstOrFail();
        $session7 = OrderSession::where('hotel_id', $hotel->id)->where('table_id', $table7->id)
            ->where('status', SessionStatus::Active)->latest('id')->firstOrFail();
        $this->addOrderItem($session7, $table7, $chickenWings, 3, OrderItemStatus::Pending, 'Extra spicy');
        $this->addOrderItem($session7, $table7, $coldCoffee, 2, OrderItemStatus::Ready);
    }

    private function addOrderItem(OrderSession $session, Table $table, MenuItem $item, int $qty, OrderItemStatus $status, ?string $note = null): void
    {
        $order = Order::firstOrCreate(
            ['session_id' => $session->id, 'table_id' => $table->id, 'member_no' => 1],
            ['order_number' => 'ORD-'.$session->id.'-1', 'status' => OrderStatus::Preparing, 'total_amount' => 0]
        );

        $order->orderItems()->updateOrCreate(
            ['menu_item_id' => $item->id],
            ['name' => $item->name, 'price' => $item->price, 'quantity' => $qty, 'status' => $status, 'note' => $note]
        );

        $order->update(['total_amount' => $order->orderItems()->get()->sum(fn ($i) => $i->price * $i->quantity)]);
    }
}
