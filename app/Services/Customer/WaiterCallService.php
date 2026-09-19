<?php

namespace App\Services\Customer;

use App\Enums\WaiterCallStatus;
use App\Events\WaiterCallCreated;
use App\Models\OrderSession;
use App\Models\WaiterCall;

class WaiterCallService
{
    public function create(OrderSession $session, ?string $note = null): WaiterCall
    {
        $call = WaiterCall::create([
            'session_id' => $session->id,
            'table_id' => $session->table_id,
            'status' => WaiterCallStatus::Pending,
            'note' => $note,
        ]);

        broadcast(new WaiterCallCreated($call, $session->hotel_id));

        return $call;
    }
}
