<?php

namespace App\Http\Controllers\Chef;

use App\Enums\OrderItemStatus;
use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    public function index()
    {
        $hotelId = Auth::user()->hotel_id;

        $items = OrderItem::whereIn('status', [OrderItemStatus::Pending, OrderItemStatus::Preparing, OrderItemStatus::Ready])
            ->whereHas('order.table', fn ($q) => $q->where('hotel_id', $hotelId))
            ->with(['order.table'])
            ->get();

        return view('chef.queue', [
            'itemsByStatus' => $items->groupBy(fn ($item) => $item->status->value),
        ]);
    }
}
