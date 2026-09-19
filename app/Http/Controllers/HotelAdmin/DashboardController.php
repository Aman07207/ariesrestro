<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Enums\SessionStatus;
use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\OrderSession;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // BelongsToHotel scopes every one of these queries to the logged-in admin's
        // own hotel automatically — no manual where('hotel_id', ...) needed anywhere here.
        $activeSessions = OrderSession::where('status', SessionStatus::Active)
            ->with('orders.orderItems')
            ->get();

        $todayRevenue = $activeSessions->flatMap->orders->flatMap->orderItems
            ->filter(fn ($i) => $i->status->value !== 'cancelled')
            ->sum(fn ($i) => $i->price * $i->quantity);

        return view('hoteladmin.dashboard', [
            'activeTables' => Table::where('status', TableStatus::Active)->count(),
            'totalTables' => Table::count(),
            'activeSessions' => $activeSessions->count(),
            'todayRevenue' => $todayRevenue,
            'menuItemCount' => MenuItem::count(),
            'staffCount' => Auth::user()->hotel->users()->count(),
        ]);
    }
}
