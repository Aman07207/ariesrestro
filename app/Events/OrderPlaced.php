<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/** Immediate (no queue worker in this project). Fired once the order transaction has committed. */
class OrderPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public int $hotelId,
        public int $orderId,
        public string $tableNumber,
        public int $itemCount,
    ) {
    }

    public static function for(Order $order, int $hotelId): self
    {
        $order->loadMissing('orderItems');
        $tableNumber = \App\Models\Table::withoutGlobalScope('hotel')->where('id', $order->table_id)->value('table_number');

        return new self($hotelId, $order->id, (string) $tableNumber, $order->orderItems->count());
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("hotel.{$this->hotelId}.orders")];
    }

    public function broadcastAs(): string
    {
        return 'order.placed';
    }

    public function broadcastWith(): array
    {
        return ['orderId' => $this->orderId, 'table' => $this->tableNumber, 'items' => $this->itemCount];
    }
}
