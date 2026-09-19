<?php

namespace App\Events;

use App\Models\WaiterCall;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Broadcasts immediately (not via the queue) — this project doesn't run a
 * queue worker, and a waiter call that silently waited in the jobs table
 * until someone happened to run one would defeat the point of realtime.
 */
class WaiterCallCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public WaiterCall $call, public int $hotelId)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("waiter-calls.{$this->hotelId}")];
    }

    public function broadcastAs(): string
    {
        return 'waiter-call.created';
    }

    public function broadcastWith(): array
    {
        return ['callId' => $this->call->id];
    }
}
