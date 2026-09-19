<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\WeatherThemeMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\PlatformSettingRequest;
use App\Models\PlatformSetting;
use App\Services\SuperAdmin\PlatformSettingService;

class PlatformSettingController extends Controller
{
    public function __construct(private readonly PlatformSettingService $settings)
    {
    }

    public function show()
    {
        return view('superadmin.platform-settings', [
            'weatherThemeMode' => PlatformSetting::get('weather_theme_mode', WeatherThemeMode::Auto->value),
            'modeIcons' => collect(WeatherThemeMode::cases())->mapWithKeys(fn ($mode) => [$mode->value => $mode->icon()]),
        ]);
    }

    public function update(PlatformSettingRequest $request)
    {
        $this->settings->update($request->validated());

        return redirect()->route('superadmin.platform-settings')->with('status', 'Platform settings updated.');
    }
}
