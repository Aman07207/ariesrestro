<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\HotelStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('hotels', 'slug')->ignore($this->route('hotel'))],
            'gstin' => ['nullable', 'string', 'regex:/^\d{2}[A-Z]{5}\d{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'google_review_link' => ['nullable', 'url', 'max:500'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'subscription_plan' => ['required', 'string', 'max:50'],
            'status' => ['required', Rule::enum(HotelStatus::class)],
            'is_luxury_hotel' => ['nullable', 'boolean'],
            'gst_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'default_service_charge_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
