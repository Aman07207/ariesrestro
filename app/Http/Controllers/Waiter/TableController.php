<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;

class TableController extends Controller
{
    /** The grid itself is the live Livewire component (Waiter\TablesGrid). */
    public function index()
    {
        return view('waiter.tables', ['hotel' => Auth::user()->hotel]);
    }

    /** The detail body is the live Livewire component (Waiter\TableDetail). */
    public function show(Table $table)
    {
        $table->orderSessions()
            ->whereIn('status', [SessionStatus::Active, SessionStatus::BillRequested])
            ->firstOrFail();

        return view('waiter.table-detail', ['table' => $table]);
    }
}
