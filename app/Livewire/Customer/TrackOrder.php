<?php

namespace App\Livewire\Customer;

use App\Enums\OrderItemStatus;
use App\Models\OrderItem;
use App\Support\TrackChannel;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Guests have no login: the component's state is a signed Livewire snapshot, so the
 * #[Locked] session id can't be swapped by the client. The public channel key is an HMAC,
 * not the session token.
 */
class TrackOrder extends Component
{
    #[Locked]
    public int $sessionId;

    #[Locked]
    public int $memberNo;

    #[Locked]
    public string $trackKey;

    /** @var array<int, string> item id => last seen status */
    public array $known = [];

    public function mount(int $sessionId, int $memberNo): void
    {
        $this->sessionId = $sessionId;
        $this->memberNo = $memberNo;
        $this->trackKey = TrackChannel::key($sessionId);
        $this->known = $this->items()->pluck('status', 'id')->map(fn ($s) => $s->value)->all();
    }

    #[On('echo:track.{trackKey},.order-item.status-changed')]
    public function detect(): void
    {
        $items = $this->items();

        foreach ($items as $item) {
            $before = $this->known[$item->id] ?? null;
            $now = $item->status->value;
            if ($before === $now) {
                continue;
            }
            if ($now === 'ready') {
                $this->dispatch('aries-notify', tone: 'customer', message: $item->name.' is ready!');
            } elseif ($now === 'preparing') {
                $this->dispatch('aries-notify', tone: 'customer', message: $item->name.' is being prepared');
            } elseif ($now === 'cancelled') {
                $this->dispatch('aries-notify', message: $item->name.' was cancelled');
            }
        }

        $this->known = $items->pluck('status', 'id')->map(fn ($s) => $s->value)->all();
    }

    private function items()
    {
        return OrderItem::whereHas('order', fn ($q) => $q->where('session_id', $this->sessionId))
            ->with('order')
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        $orderItems = $this->items();
        $active = $orderItems->filter(fn ($i) => $i->status !== OrderItemStatus::Cancelled);
        $steps = ['pending' => 'Placed', 'preparing' => 'Preparing', 'ready' => 'Ready', 'served' => 'Served'];
        $keys = array_keys($steps);

        return view('livewire.customer.track-order', [
            'orderItems' => $orderItems,
            'steps' => $steps,
            'stepKeys' => $keys,
            'overallIdx' => $active->isEmpty() ? 0 : $active->min(fn ($i) => array_search($i->status->value, $keys)),
        ]);
    }
}
