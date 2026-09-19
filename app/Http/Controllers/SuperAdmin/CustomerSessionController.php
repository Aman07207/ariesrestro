<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\OrderSession;
use Illuminate\Http\Request;

class CustomerSessionController extends Controller
{
    public function index(Request $request)
    {
        return view('superadmin.customers.index', [
            'sessions' => OrderSession::with(['table.hotel'])
                ->where('status', SessionStatus::Active)
                ->when($request->filled('hotel'), fn ($q) => $q->where('hotel_id', $request->integer('hotel')))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'hotels' => Hotel::orderBy('name')->get(),
        ]);
    }
}
