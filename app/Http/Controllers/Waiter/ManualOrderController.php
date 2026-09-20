<?php

namespace App\Http\Controllers\Waiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\Waiter\ManualOrderRequest;
use App\Models\MenuItem;
use App\Models\Table;
use App\Services\Waiter\ManualOrderService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ManualOrderController extends Controller
{
    public function __construct(private readonly ManualOrderService $manualOrders)
    {
    }

    public function create()
    {
        $hotel = Auth::user()->hotel;

        return view('waiter.manual-order', [
            // Every table, not just free ones — a waiter also adds to a table that's already seated.
            'tables' => Table::where('hotel_id', $hotel->id)->orderBy('table_number')->get(),
            'menuItems' => MenuItem::where('hotel_id', $hotel->id)->where('is_available', true)->orderBy('name')->get(),
        ]);
    }

    public function store(ManualOrderRequest $request): JsonResponse
    {
        // Table's hotel scope already 404s another hotel's id for this logged-in waiter.
        $table = Table::findOrFail($request->validated('table_id'));

        try {
            $order = $this->manualOrders->place(Auth::user(), $table, $request->validated('items'));
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Order '.$order->order_number.' sent to the kitchen',
            'redirect' => route('waiter.tables.show', $table),
        ]);
    }
}
