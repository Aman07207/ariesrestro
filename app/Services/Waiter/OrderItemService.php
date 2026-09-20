<?php

namespace App\Services\Waiter;

use App\Enums\OrderItemStatus;
use App\Events\OrderItemStatusChanged;
use App\Support\Realtime;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class OrderItemService
{
    public function cancel(OrderItem $orderItem, string $reason, User $waiter): OrderItem
    {
        $orderItem->loadMissing('order');

        // Table carries the BelongsToHotel global scope, which filters by the
        // *currently authenticated* user's hotel_id — exactly wrong for this check,
        // since the whole point here is comparing across a potential mismatch rather
        // than having one silently scoped away into a confusing null. Bypassed
        // deliberately with withoutGlobalScope('hotel'), not by accident.
        $tableHotelId = Table::withoutGlobalScope('hotel')->where('id', $orderItem->order->table_id)->value('hotel_id');

        if ($tableHotelId !== $waiter->hotel_id) {
            throw new AuthorizationException('You cannot modify another hotel\'s order.');
        }

        // Re-validated server-side, not just hidden client-side: only a still-pending
        // item can be cancelled.
        if ($orderItem->status !== OrderItemStatus::Pending) {
            throw new AuthorizationException('Only a pending item can be cancelled.');
        }

        $orderItem->update([
            'status' => OrderItemStatus::Cancelled,
            'cancel_reason' => $reason,
            'cancelled_by' => $waiter->id,
        ]);

        Realtime::dispatch(OrderItemStatusChanged::for($orderItem));

        // No Order-level total to keep in sync anymore — tax/totals are a session-level
        // Bill concern (BillingService), computed fresh from non-cancelled order_items
        // whenever the bill is generated, so a cancellation is naturally reflected there
        // without any recompute step here.

        return $orderItem;
    }
}
