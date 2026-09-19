<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function index()
    {
        return view('chef.stock', [
            'menuItems' => MenuItem::where('hotel_id', Auth::user()->hotel_id)->with('category')->orderBy('name')->get(),
        ]);
    }
}
