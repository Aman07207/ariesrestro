<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;
use App\Http\Requests\Customer\PlaceOrderRequest;
use App\Services\Customer\OrderPlacementService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class OrderController extends Controller
{
    use ResolvesCustomerSession;

    public function __construct(private readonly OrderPlacementService $orderPlacement)
    {
    }

    public function track()
    {
        return view('customer.track', [
            'table' => $this->currentTable(),
            'memberNo' => $this->currentMemberNo(),
            'orderItems' => $this->currentOrderItems(),
        ]);
    }

    public function place(PlaceOrderRequest $request): JsonResponse
    {
        try {
            $order = $this->orderPlacement->place(
                $this->currentSession(),
                $this->currentMemberNo(),
                $request->validated('items')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Order placed — kitchen has been notified',
            'order_number' => $order->order_number,
        ]);
    }
}
