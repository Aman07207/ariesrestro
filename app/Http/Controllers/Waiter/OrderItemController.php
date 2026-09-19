<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Waiter\CancelOrderItemRequest;
use App\Models\OrderItem;
use App\Services\Waiter\OrderItemService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class OrderItemController extends Controller
{
    public function __construct(private readonly OrderItemService $orderItems)
    {
    }

    public function cancel(CancelOrderItemRequest $request, OrderItem $orderItem)
    {
        $reason = $request->validated('reason');
        if ($note = $request->validated('note')) {
            $reason .= ' — '.$note;
        }

        try {
            $this->orderItems->cancel($orderItem, $reason, Auth::user());
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json(['message' => 'Item cancelled']);
    }
}
