<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\WaiterCallStatus;
use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\WaiterCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CallController extends Controller
{
    public function index()
    {
        return view('waiter.calls');
    }

    public function attend(WaiterCall $call)
    {
        // Table carries a BelongsToHotel global scope, which would silently hide the
        // relation (not just restrict it) for a waiter from a different hotel — that's
        // right for normal queries, but here it would turn a 403 into a crash. Bypass
        // it deliberately, just to read the raw hotel_id for the comparison below.
        $tableHotelId = Table::withoutGlobalScopes()->where('id', $call->table_id)->value('hotel_id');

        abort_unless($tableHotelId === Auth::user()->hotel_id, 403);

        $call->update(['status' => WaiterCallStatus::Attended, 'attended_by' => Auth::id()]);

        return back()->with('status', 'Call marked attended.');
    }

    /**
     * Superseded on the frontend by the Reverb-broadcast Livewire components
     * (App\Livewire\Waiter\CallsBadge / CallsIndex), kept as a plain JSON
     * endpoint for anything else that wants a poll-based read of this state.
     */
    public function pending(): JsonResponse
    {
        $hotelId = Auth::user()->hotel_id;

        $pending = WaiterCall::whereHas('table', fn ($q) => $q->where('hotel_id', $hotelId))
            ->where('status', WaiterCallStatus::Pending)
            ->orderByDesc('id');

        return response()->json([
            'count' => $pending->count(),
            'latestId' => $pending->value('id'),
        ]);
    }
}
