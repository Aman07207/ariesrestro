<?php

namespace App\Livewire\Chef;

use App\Enums\OrderItemStatus;
use App\Enums\UserRole;
use App\Models\OrderItem;
use App\Services\Chef\KitchenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class QueueBoard extends Component
{
    public int $hotelId;

    /** Highest pending item id already seen — anything above it is a new arrival. */
    public int $lastPendingId = 0;

    public function mount(): void
    {
        $this->hotelId = Auth::user()->hotel_id;
        $this->lastPendingId = (int) $this->query([OrderItemStatus::Pending])->max('order_items.id');
    }

    /** Echo handler + wire:poll both land here, so the tone also fires on the polling fallback. */
    #[On('echo-private:hotel.{hotelId}.orders,.order.placed')]
    #[On('echo-private:hotel.{hotelId}.orders,.order-item.status-changed')]
    public function detect(): void
    {
        $newest = (int) $this->query([OrderItemStatus::Pending])->max('order_items.id');

        if ($newest > $this->lastPendingId) {
            $fresh = $this->query([OrderItemStatus::Pending])->with('order.table')->where('order_items.id', '>', $this->lastPendingId)->get();
            $tables = $fresh->map(fn ($i) => $i->order->table->table_number)->unique()->implode(', ');
            $this->dispatch('aries-notify', tone: 'chefOrder', message: 'New order — Table '.$tables);
            $this->lastPendingId = $newest;
        }
    }

    public function advance(int $itemId, KitchenService $kitchen): void
    {
        $user = Auth::user();
        abort_unless($user && $user->role === UserRole::Chef, 403);

        try {
            $item = $kitchen->advance(OrderItem::findOrFail($itemId), $user);
        } catch (AuthorizationException $e) {
            $this->dispatch('aries-notify', message: $e->getMessage());

            return;
        }

        $this->dispatch('aries-notify', message: $item->name.' marked '.$item->status->value);
    }

    public function render()
    {
        $items = $this->query([OrderItemStatus::Pending, OrderItemStatus::Preparing, OrderItemStatus::Ready])
            ->with(['order.table'])
            ->orderBy('order_items.id')
            ->get();

        return view('livewire.chef.queue-board', [
            'itemsByStatus' => $items->groupBy(fn ($item) => $item->status->value),
        ]);
    }

    private function query(array $statuses)
    {
        return OrderItem::whereIn('status', $statuses)
            ->whereHas('order.table', fn ($q) => $q->where('hotel_id', $this->hotelId));
    }
}
