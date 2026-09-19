<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\SubscriptionStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hotel_id' => ['required', 'exists:hotels,id'],
            'plan' => ['required', 'string', 'max:50'],
            'billing_cycle' => ['required', 'string', 'in:monthly,yearly'],
            'price' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'status' => ['required', Rule::enum(SubscriptionStatus::class)],
        ];
    }
}
