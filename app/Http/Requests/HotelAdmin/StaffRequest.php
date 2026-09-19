<?php

namespace App\Http\Requests\HotelAdmin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('staff')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users', 'employee_id')->ignore($userId)],
            'role' => ['required', Rule::in([UserRole::Manager->value, UserRole::Waiter->value, UserRole::Chef->value])],
            'phone' => ['nullable', 'string', 'max:30'],
            'section' => ['nullable', 'string', 'max:255'],
            'shift' => ['nullable', 'string', 'max:100'],
            'password' => [$userId ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }
}
