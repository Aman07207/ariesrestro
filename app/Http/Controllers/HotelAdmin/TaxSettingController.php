<?php

namespace App\Http\Controllers\HotelAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\HotelAdmin\TaxSettingRequest;
use App\Services\HotelAdmin\TaxSettingService;
use Illuminate\Support\Facades\Auth;

class TaxSettingController extends Controller
{
    public function __construct(private readonly TaxSettingService $taxSettings)
    {
    }

    public function show()
    {
        return view('hoteladmin.tax-settings', ['hotel' => Auth::user()->hotel]);
    }

    public function update(TaxSettingRequest $request)
    {
        $data = $request->validated();
        $data['is_luxury_hotel'] = $request->boolean('is_luxury_hotel');

        $this->taxSettings->update(Auth::user()->hotel, $data);

        return redirect()->route('hoteladmin.tax-settings')->with('status', 'Tax settings updated.');
    }
}
