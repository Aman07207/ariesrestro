<?php

namespace App\Livewire\Concerns;

use App\Enums\OrderItemStatus;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

/**
 * Waiter screens: works out what changed since the last look (a new order arrived, or the
 * chef marked items ready) and fires the tone. Called from the Echo handler AND from
 * wire:poll, so the tone also plays when the socket is down and polling picks it up.
 */
trait WatchesKitchen
{
    public int $lastOrderId = 0;

    /** @var array<int, int> */
    public array $readyIds = [];

    public function primeKitchenWatch(): void
    {
        $this->lastOrderId = (int) $this->hotelOrders()->max('orders.id');
        $this->readyIds = $this->hotelReadyItems()->pluck('order_items.id')->all();
    }

    public function watchKitchen(): void
    {
        $maxOrder = (int) $this->hotelOrders()->max('orders.id');
        $ready = $this->hotelReadyItems()->with('order.table')->get();

        $newReady = $ready->reject(fn ($i) => in_array($i->id, $this->readyIds, true));

        if ($maxOrder > $this->lastOrderId) {
            $this->dispatch('aries-notify', tone: 'waiterOrder', message: 'New order received');
        } elseif ($newReady->isNotEmpty()) {
            $first = $newReady->first();
            $this->dispatch('aries-notify', tone: 'ready', message: $first->name.' is ready — Table '.$first->order->table->table_number);
        }

        $this->lastOrderId = max($this->lastOrderId, $maxOrder);
        $this->readyIds = $ready->pluck('id')->all();
    }

    private function hotelOrders()
    {
        $hotelId = Auth::user()->hotel_id;

        return Order::whereHas('table', fn ($q) => $q->where('hotel_id', $hotelId));
    }

    private function hotelReadyItems()
    {
        $hotelId = Auth::user()->hotel_id;

        return OrderItem::where('status', OrderItemStatus::Ready)
            ->whereHas('order.table', fn ($q) => $q->where('hotel_id', $hotelId));
    }
}
