<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:hotels,slug'],
            // Standard 15-character GSTIN format: 2-digit state code, 10-char PAN,
            // entity code, literal Z, checksum. Nullable — not every hotel has it on file yet.
            'gstin' => ['nullable', 'string', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'google_review_link' => ['nullable', 'url', 'max:500'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'subscription_plan' => ['required', 'string', 'max:50'],
            'is_luxury_hotel' => ['nullable', 'boolean'],
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_service_charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
