<?php

namespace App\Services\SuperAdmin;

use App\Models\HotelPaymentSetting;

class PaymentSettingService
{
    public function create(array $data): HotelPaymentSetting
    {
        return HotelPaymentSetting::create($data);
    }

    public function update(HotelPaymentSetting $setting, array $data): HotelPaymentSetting
    {
        // Blank secret fields mean "leave unchanged" — the edit form never re-displays
        // the decrypted values, so an empty submission shouldn't overwrite them with blanks.
        foreach (['razorpay_key_id', 'razorpay_key_secret'] as $secretField) {
            if (blank($data[$secretField] ?? null)) {
                unset($data[$secretField]);
            }
        }

        $setting->update($data);

        return $setting;
    }

    public function delete(HotelPaymentSetting $setting): void
    {
        $setting->delete();
    }
}
