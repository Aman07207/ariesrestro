<?php

namespace App\Http\Controllers\Chef;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Services\Chef\KitchenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StockController extends Controller
{
    public function __construct(private readonly KitchenService $kitchen)
    {
    }

    public function index()
    {
        return view('chef.stock', [
            'menuItems' => MenuItem::where('hotel_id', Auth::user()->hotel_id)->with('category')->orderBy('name')->get(),
        ]);
    }

    // {menuItem} binds through MenuItem's hotel scope, so another hotel's item is a 404.
    public function toggle(MenuItem $menuItem): JsonResponse
    {
        $item = $this->kitchen->toggleStock($menuItem);

        return response()->json(['message' => $item->name.($item->is_available ? ' back in stock' : ' marked unavailable'), 'is_available' => $item->is_available]);
    }
}
