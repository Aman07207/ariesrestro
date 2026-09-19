<?php

namespace App\Http\Requests\HotelAdmin;

use Illuminate\Foundation\Http\FormRequest;

class MenuCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:food,beverage'],
            'display_order' => ['required', 'integer', 'min:0'],
        ];
    }
}
