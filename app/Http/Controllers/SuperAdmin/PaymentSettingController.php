<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\PaymentSettingRequest;
use App\Models\Hotel;
use App\Models\HotelPaymentSetting;
use App\Services\SuperAdmin\PaymentSettingService;

class PaymentSettingController extends Controller
{
    public function __construct(private readonly PaymentSettingService $paymentSettings)
    {
    }

    public function index()
    {
        return view('superadmin.payment-settings.index', [
            'settings' => HotelPaymentSetting::with('hotel')->latest()->paginate(15),
        ]);
    }

    public function create()
    {
        return view('superadmin.payment-settings.create', [
            'hotels' => Hotel::whereDoesntHave('paymentSettings')->orderBy('name')->get(),
        ]);
    }

    public function store(PaymentSettingRequest $request)
    {
        $this->paymentSettings->create($request->validated());

        return redirect()->route('superadmin.payment-settings.index')->with('status', 'Payment settings saved.');
    }

    public function edit(HotelPaymentSetting $paymentSetting)
    {
        return view('superadmin.payment-settings.edit', [
            'setting' => $paymentSetting,
            'hotels' => Hotel::orderBy('name')->get(),
        ]);
    }

    public function update(PaymentSettingRequest $request, HotelPaymentSetting $paymentSetting)
    {
        $this->paymentSettings->update($paymentSetting, $request->validated());

        return redirect()->route('superadmin.payment-settings.index')->with('status', 'Payment settings updated.');
    }

    public function destroy(HotelPaymentSetting $paymentSetting)
    {
        $this->paymentSettings->delete($paymentSetting);

        return redirect()->route('superadmin.payment-settings.index')->with('status', 'Payment settings removed.');
    }
}
