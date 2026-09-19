<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;
use App\Models\MenuCategory;

class MenuController extends Controller
{
    use ResolvesCustomerSession;

    public function index()
    {
        $table = $this->currentTable();

        $categories = MenuCategory::where('hotel_id', $table->hotel_id)
            ->orderBy('display_order')
            ->with(['menuItems' => fn ($q) => $q->orderBy('name')])
            ->get();

        return view('customer.menu', [
            'table' => $table,
            'memberNo' => $this->currentMemberNo(),
            'categories' => $categories,
        ]);
    }
}
