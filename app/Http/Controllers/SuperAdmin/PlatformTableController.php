<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Table;
use Illuminate\Http\Request;

class PlatformTableController extends Controller
{
    /**
     * A flat table list stopped scaling past a handful of hotels — with 100+ hotels
     * this needs to be "pick a hotel, then see its tables," the same shape Hotel
     * Admin already sees for their own hotel, not one merged list of everyone's.
     */
    public function index(Request $request)
    {
        $hotels = Hotel::withCount('tables')
            ->withCount(['tables as active_tables_count' => fn ($q) => $q->where('status', 'active')])
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('superadmin.tables.hotels', ['hotels' => $hotels]);
    }

    /**
     * One hotel's tables + QR actions. BelongsToHotel's scope is inert for a Super
     * Admin (hotel_id is null), so scoping this explicitly to the given $hotel
     * rather than relying on the global scope is deliberate here.
     */
    public function show(Hotel $hotel)
    {
        return view('superadmin.tables.index', [
            'hotel' => $hotel,
            'tables' => Table::where('hotel_id', $hotel->id)->orderBy('table_number')->paginate(20),
        ]);
    }
}
