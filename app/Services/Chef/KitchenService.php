<?php

namespace App\Services\Chef;

use App\Enums\OrderItemStatus;
use App\Events\OrderItemStatusChanged;
use App\Support\Realtime;
use App\Models\MenuItem;
use App\Models\OrderItem;
use App\Models\Table;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class KitchenService
{
    /** The only legal forward moves — the client's requested status is never trusted. */
    private const NEXT = [
        'pending' => OrderItemStatus::Preparing,
        'preparing' => OrderItemStatus::Ready,
        'ready' => OrderItemStatus::Served,
    ];

    public function advance(OrderItem $item, User $chef): OrderItem
    {
        $item->loadMissing('order');

        // Table carries the BelongsToHotel scope keyed to the *logged-in* user; bypass it
        // so a cross-hotel attempt is a clean 403 rather than a null-relation crash.
        $hotelId = Table::withoutGlobalScope('hotel')->where('id', $item->order->table_id)->value('hotel_id');
        if ($hotelId !== $chef->hotel_id) {
            throw new AuthorizationException('That order belongs to another hotel.');
        }

        $next = self::NEXT[$item->status->value] ?? null;
        if ($next === null) {
            throw new AuthorizationException('This item can no longer be advanced.');
        }

        $item->update(['status' => $next]);
        Realtime::dispatch(OrderItemStatusChanged::for($item));

        return $item;
    }

    public function toggleStock(MenuItem $menuItem): MenuItem
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return $menuItem;
    }
}
