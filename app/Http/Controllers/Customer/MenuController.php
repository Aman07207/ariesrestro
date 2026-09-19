<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Customer\Concerns\ResolvesCustomerSession;
use App\Models\MenuCategory;
use App\Services\Customer\GreetingService;
use App\Services\WeatherThemeService;

class MenuController extends Controller
{
    use ResolvesCustomerSession;

    public function __construct(
        private readonly GreetingService $greeting,
        private readonly WeatherThemeService $weatherTheme,
    ) {
    }

    public function index()
    {
        $table = $this->currentTable();

        $categories = MenuCategory::where('hotel_id', $table->hotel_id)
            ->orderBy('display_order')
            ->with(['menuItems' => fn ($q) => $q->orderBy('name')])
            ->get();

        $season = $this->weatherTheme->resolveForHotel($table->hotel);

        return view('customer.menu', [
            'table' => $table,
            'memberNo' => $this->currentMemberNo(),
            'categories' => $categories,
            'greeting' => $this->greeting->forHotel($table->hotel),
            'season' => $season,
        ]);
    }
}
