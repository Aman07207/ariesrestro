<?php

namespace App\Http\Requests\HotelAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                Rule::exists('menu_categories', 'id')->where('hotel_id', Auth::user()->hotel_id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'veg_type' => ['required', 'string', 'in:veg,non-veg'],
            'tax_track' => ['required', 'string', 'in:gst,vat'],
            'is_available' => ['nullable', 'boolean'],
            'is_popular' => ['nullable', 'boolean'],
            // 'image' (not 'mimes') checks the real file content, not just the extension/name.
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
