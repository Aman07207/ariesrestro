<?php

namespace Database\Seeders;

use App\Enums\OrderItemStatus;
use App\Enums\OrderStatus;
use App\Enums\SessionStatus;
use App\Enums\TableStatus;
use App\Models\Hotel;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderSession;
use App\Models\SessionMember;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoOrderSessionSeeder extends Seeder
{
    public function run(): void
    {
        $hotel = Hotel::where('slug', 'aries-cafe')->firstOrFail();
        $table5 = Table::where('hotel_id', $hotel->id)->where('table_number', 5)->firstOrFail();
        $table5->update(['status' => TableStatus::Active]);

        $session = OrderSession::updateOrCreate(
            ['hotel_id' => $hotel->id, 'table_id' => $table5->id, 'status' => SessionStatus::Active],
            [
                'session_token' => (string) Str::uuid(),
                'member_count' => 2,
            ]
        );

        SessionMember::updateOrCreate(
            ['session_id' => $session->id, 'member_no' => 1],
            ['device_token' => (string) Str::uuid()]
        );
        SessionMember::updateOrCreate(
            ['session_id' => $session->id, 'member_no' => 2],
            ['device_token' => (string) Str::uuid()]
        );

        $paneerTikka = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Paneer Tikka')->firstOrFail();
        $butterChicken = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Butter Chicken')->firstOrFail();
        $masalaChai = MenuItem::where('hotel_id', $hotel->id)->where('name', 'Masala Chai')->firstOrFail();

        $order = Order::updateOrCreate(
            ['session_id' => $session->id, 'table_id' => $table5->id, 'member_no' => 1],
            [
                'order_number' => 'ORD-'.$session->id.'-1',
                'status' => OrderStatus::Preparing,
                'total_amount' => $paneerTikka->price * 2 + $butterChicken->price + $masalaChai->price * 2,
            ]
        );

        $order->orderItems()->updateOrCreate(
            ['menu_item_id' => $paneerTikka->id],
            [
                'name' => $paneerTikka->name,
                'price' => $paneerTikka->price,
                'quantity' => 2,
                'status' => OrderItemStatus::Preparing,
            ]
        );

        $order->orderItems()->updateOrCreate(
            ['menu_item_id' => $butterChicken->id],
            [
                'name' => $butterChicken->name,
                'price' => $butterChicken->price,
                'quantity' => 1,
                'status' => OrderItemStatus::Pending,
                'note' => 'Less spicy',
            ]
        );

        $order->orderItems()->updateOrCreate(
            ['menu_item_id' => $masalaChai->id],
            [
                'name' => $masalaChai->name,
                'price' => $masalaChai->price,
                'quantity' => 2,
                'status' => OrderItemStatus::Served,
            ]
        );

        // Member 2 has an empty cart by design — this is "you" in the finalized demo UI.
    }
}
