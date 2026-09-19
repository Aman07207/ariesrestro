<?php

namespace App\Http\Requests\HotelAdmin;

use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'table_number' => [
                'required', 'integer', 'min:1',
                Rule::unique('tables', 'table_number')
                    ->where('hotel_id', Auth::user()->hotel_id)
                    ->ignore($this->route('table')),
            ],
            'seating_capacity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'status' => ['required', Rule::enum(TableStatus::class)],
        ];
    }
}
