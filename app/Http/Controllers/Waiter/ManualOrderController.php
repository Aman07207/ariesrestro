<?php

namespace App\Http\Controllers\Waiter;

use App\Enums\TableStatus;
use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;

class ManualOrderController extends Controller
{
    public function create()
    {
        $hotel = Auth::user()->hotel;

        return view('waiter.manual-order', [
            'availableTables' => Table::where('hotel_id', $hotel->id)
                ->where('status', TableStatus::Available)
                ->orderBy('table_number')
                ->get(),
            'menuItems' => MenuItem::where('hotel_id', $hotel->id)->orderBy('name')->get(),
        ]);
    }
}
