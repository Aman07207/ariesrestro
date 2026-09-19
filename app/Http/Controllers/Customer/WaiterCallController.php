<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;
use App\Http\Requests\Customer\WaiterCallRequest;
use App\Services\Customer\WaiterCallService;
use Illuminate\Http\JsonResponse;

class WaiterCallController extends Controller
{
    use ResolvesCustomerSession;

    public function __construct(private readonly WaiterCallService $waiterCalls)
    {
    }

    public function store(WaiterCallRequest $request): JsonResponse
    {
        $this->waiterCalls->create($this->currentSession(), $request->validated('note'));

        return response()->json(['message' => "Waiter notified — they're on the way"]);
    }
}
