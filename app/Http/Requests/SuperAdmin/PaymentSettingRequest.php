<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => [
                'required', 'exists:hotels,id',
                Rule::unique('hotel_payment_settings', 'hotel_id')->ignore($this->route('payment_setting')),
            ],
            'razorpay_linked_account_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
            'commission_percent' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
