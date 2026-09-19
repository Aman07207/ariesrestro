<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\OrderItemStatus;
use App\Enums\SessionStatus;
use App\Enums\WaiterCallStatus;
use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\WaiterCall;
use App\Services\Customer\BillingService;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    public function __construct(private readonly BillingService $billing)
    {
    }

    public function index()
    {
        $hotel = Auth::user()->hotel;

        $tables = Table::where('hotel_id', $hotel->id)
            ->orderBy('table_number')
            ->with(['orderSessions' => fn ($q) => $q->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
                ->with('orders.orderItems')])
            ->get()
            ->map(function (Table $table) {
                $session = $table->orderSessions->first();
                $items = $session
                    ? $session->orders->flatMap->orderItems->filter(fn ($i) => $i->status !== OrderItemStatus::Cancelled)
                    : collect();

                return [
                    'table' => $table,
                    'colorClass' => ! $session ? 'available' : ($session->status === SessionStatus::BillRequested ? 'bill' : 'active'),
                    'memberCount' => $session ? $session->orders->pluck('member_no')->unique()->count() : 0,
                    'itemCount' => $items->count(),
                ];
            });

        return view('waiter.tables', [
            'hotel' => $hotel,
            'tables' => $tables,
            'pendingCallCount' => WaiterCall::whereHas('table', fn ($q) => $q->where('hotel_id', $hotel->id))
                ->where('status', WaiterCallStatus::Pending)->count(),
        ]);
    }

    public function show(Table $table)
    {
        $session = $table->orderSessions()
            ->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
            ->latest('id')
            ->with('orders.orderItems')
            ->firstOrFail();

        // Not ->load('order'): flatMap over a collection of orderItems collections
        // downgrades to a plain Support\Collection (only stays Eloquent\Collection when
        // every mapped element is itself a Model), which has no ->load() method. We
        // already have each order loaded anyway, so attach it directly — cheaper too,
        // since ->load() would have re-queried per item instead of reusing what we have.
        $orderItems = $session->orders->flatMap(
            fn ($order) => $order->orderItems->each(fn ($item) => $item->setRelation('order', $order))
        );

        return view('waiter.table-detail', [
            'table' => $table,
            'orderItems' => $orderItems,
            // Read-only preview — no Bill row created here, that's the customer's own
            // bill-page action. Assumes no service charge until the customer opts in.
            'billPreview' => $this->billing->preview($session, false),
            'memberCount' => $session->orders->pluck('member_no')->unique()->count(),
            'sessionStatusLabel' => $session->status === SessionStatus::BillRequested ? 'Bill requested' : 'Active',
        ]);
    }
}
