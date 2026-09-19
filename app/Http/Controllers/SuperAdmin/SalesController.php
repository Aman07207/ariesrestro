<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Order;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Every hotel's orders — Order isn't hotel-scoped directly (no BelongsToHotel), but
     * since it has no super-admin-only concept of "my hotel" to filter by anyway, this
     * naturally already spans every hotel. The ?hotel= filter is the scale valve for
     * when there are 100+ hotels' worth of orders in here.
     */
    public function index(Request $request)
    {
        return view('superadmin.sales.index', [
            'orders' => Order::with(['table.hotel'])
                ->when($request->filled('hotel'), fn ($q) => $q->whereHas('table', fn ($t) => $t->where('hotel_id', $request->integer('hotel'))))
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'hotels' => Hotel::orderBy('name')->get(),
        ]);
    }
}
