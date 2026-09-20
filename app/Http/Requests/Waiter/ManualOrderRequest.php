<?php

namespace App\Http\Requests\Waiter;

use Illuminate\Foundation\Http\FormRequest;

class ManualOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_id' => ['required', 'integer'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_item_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'items.*.note' => ['nullable', 'string', 'max:250'],
        ];
    }
}
