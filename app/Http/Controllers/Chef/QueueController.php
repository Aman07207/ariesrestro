<?php

namespace App\Http\Controllers\Chef;

use App\Enums\OrderItemStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Services\Chef\KitchenService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function __construct(private readonly KitchenService $kitchen)
    {
    }

    public function index()
    {
        $hotelId = Auth::user()->hotel_id;

        $items = OrderItem::whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing, OrderItemStatus::Ready])
            ->whereHas('order.table', fn ($q) => $q->where('hotel_id', $hotelId))
            ->with(['order.table'])
            ->orderBy('id')
            ->get();

        return view('chef.queue', [
            'itemsByStatus' => $items->groupBy(fn ($item) => $item->status->value),
        ]);
    }

    public function advance(OrderItem $orderItem): JsonResponse
    {
        try {
            $item = $this->kitchen->advance($orderItem, Auth::user());
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json(['message' => $item->name.' marked '.$item->status->value, 'status' => $item->status->value]);
    }
}
