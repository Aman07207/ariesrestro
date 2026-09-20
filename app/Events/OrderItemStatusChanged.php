<?php

namespace App\Events;

use App\Models\OrderItem;
use App\Models\Table;
use App\Support\TrackChannel;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class OrderItemStatusChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $hotelId,
        public int $sessionId,
        public int $itemId,
        public string $name,
        public string $tableNumber,
        public string $status,
    ) {
    }

    public static function for(OrderItem $item): self
    {
        $item->loadMissing('order');
        $table = Table::withoutGlobalScope('hotel')->where('id', $item->order->table_id)->first(['hotel_id', 'table_number']);

        return new self(
            (int) $table->hotel_id,
            (int) $item->order->session_id,
            $item->id,
            $item->name,
            (string) $table->table_number,
            $item->status->value,
        );
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("hotel.{$this->hotelId}.orders"),
            new Channel(TrackChannel::name($this->sessionId)),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order-item.status-changed';
    }

    public function broadcastWith(): array
    {
        return ['itemId' => $this->itemId, 'name' => $this->name, 'table' => $this->tableNumber, 'status' => $this->status];
    }
}
