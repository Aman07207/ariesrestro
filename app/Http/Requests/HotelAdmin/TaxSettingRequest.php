<?php

namespace App\Http\Requests\HotelAdmin;

use Illuminate\Foundation\Http\FormRequest;

class TaxSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_luxury_hotel' => ['nullable', 'boolean'],
            'default_service_charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
