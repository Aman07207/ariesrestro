<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Keeps the per-hotel weather-theme cache warm so customer page loads never
// call the weather API themselves (see App\Services\WeatherThemeService).
Schedule::command('weather:refresh')->everyFourHours();
