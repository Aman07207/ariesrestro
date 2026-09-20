<?php

namespace App\Services\Customer;

use App\Enums\OrderItemStatus;
use App\Events\OrderPlaced;
use App\Support\Realtime;
use App\Enums\OrderStatus;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class OrderPlacementService
{
    /**
     * @param  array<int, array{menu_item_id: int, quantity?: int, note?: ?string}>  $lines
     *
     * Prices are always server-computed: whatever the client thinks an item costs is
     * never read here at all — only menu_item_id and quantity/note come from the
     * request, the price snapshot always comes fresh from the database.
     *
     * No tax/service-charge math here — that's a session-level Bill concern computed
     * fresh by BillingService whenever the bill is viewed, not something accumulated
     * per order round.
     */
    public function place(OrderSession $session, int $memberNo, array $lines): Order
    {
        $hotelId = $session->hotel_id;
        $menuItemIds = collect($lines)->pluck('menu_item_id')->filter()->unique();

        $menuItems = MenuItem::where('hotel_id', $hotelId)
            ->where('is_available', true)
            ->whereIn('id', $menuItemIds)
            ->get()
            ->keyBy('id');

        if ($menuItems->isEmpty()) {
            throw new InvalidArgumentException('No valid items to order.');
        }

        $order = DB::transaction(function () use ($session, $memberNo, $lines, $menuItems) {
            $order = Order::create([
                'session_id' => $session->id,
                'table_id' => $session->table_id,
                'member_no' => $memberNo,
                'order_number' => 'ORD-'.$session->id.'-'.(Order::where('session_id', $session->id)->count() + 1),
                'status' => OrderStatus::Pending,
                'total_amount' => 0,
            ]);

            $subtotal = 0;

            foreach ($lines as $line) {
                $menuItem = $menuItems->get($line['menu_item_id'] ?? null);
                if (! $menuItem) {
                    continue;
                }

                $quantity = max(1, min(20, (int) ($line['quantity'] ?? 1)));
                $subtotal += $menuItem->price * $quantity;

                $order->orderItems()->create([
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'price' => $menuItem->price,
                    'quantity' => $quantity,
                    'status' => OrderItemStatus::Pending,
                    'note' => isset($line['note']) && $line['note'] !== ''
                        ? Str::limit(strip_tags((string) $line['note']), 250, '')
                        : null,
                ]);
            }

            if ($order->orderItems()->count() === 0) {
                $order->delete();

                throw new InvalidArgumentException('No valid items to order.');
            }

            $order->update(['total_amount' => $subtotal]);

            return $order->fresh('orderItems');
        });

        // After commit, so a rolled-back order never notifies the kitchen.
        Realtime::dispatch(OrderPlaced::for($order, $hotelId));

        return $order;
    }
}
