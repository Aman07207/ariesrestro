<?php

namespace App\Services\Customer;

use App\Enums\WaiterCallStatus;
use App\Models\OrderSession;
use App\Models\WaiterCall;

class WaiterCallService
{
    public function create(OrderSession $session, ?string $note = null): WaiterCall
    {
        return WaiterCall::create([
            'session_id' => $session->id,
            'table_id' => $session->table_id,
            'status' => WaiterCallStatus::Pending,
            'note' => $note,
        ]);
    }
}
