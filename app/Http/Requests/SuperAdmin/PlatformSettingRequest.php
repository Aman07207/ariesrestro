<?php

namespace App\Http\Requests\SuperAdmin;

use App\Enums\WeatherThemeMode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PlatformSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weather_theme_mode' => ['required', new Enum(WeatherThemeMode::class)],
        ];
    }
}
