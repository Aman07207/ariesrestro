<?php

namespace App\Services;

use App\Enums\Season;
use App\Enums\WeatherThemeMode;
use App\Models\Hotel;
use App\Models\PlatformSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WeatherThemeService
{
    private const PLATFORM_DEFAULT_KEY = 'weather_theme_mode';

    private const CACHE_TTL_HOURS = 6;

    /**
     * Cheap, request-time resolution — never calls the weather API. Precedence:
     * the hotel's own forced mode, then the platform default, then whatever the
     * scheduled command last cached, then the calendar-month heuristic.
     */
    public function resolveForHotel(Hotel $hotel): Season
    {
        if ($season = $hotel->weather_theme_mode->toSeason()) {
            return $season;
        }

        $platformMode = WeatherThemeMode::from(
            PlatformSetting::get(self::PLATFORM_DEFAULT_KEY, WeatherThemeMode::Auto->value)
        );

        if ($season = $platformMode->toSeason()) {
            return $season;
        }

        $cached = Cache::get($this->cacheKey($hotel));

        return $cached ? Season::from($cached) : $this->calendarFallback();
    }

    public function calendarFallback(): Season
    {
        $month = Carbon::now('Asia/Kolkata')->month;

        return match (true) {
            $month >= 3 && $month <= 6 => Season::Summer,
            $month >= 7 && $month <= 9 => Season::Monsoon,
            default => Season::Winter, // Oct–Feb
        };
    }

    /**
     * Called only from the scheduled command — this is the one place that ever
     * talks to the weather API. A failure here just leaves the previous cached
     * value (or none, falling back to the calendar heuristic) — it never bubbles
     * up to a page request.
     */
    public function refreshCacheForHotel(Hotel $hotel): void
    {
        if (! $hotel->latitude || ! $hotel->longitude) {
            return;
        }

        $apiKey = config('services.openweathermap.key');

        if (! $apiKey) {
            return;
        }

        try {
            $response = Http::timeout(8)->get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $hotel->latitude,
                'lon' => $hotel->longitude,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);

            if (! $response->successful()) {
                Log::warning("Weather refresh failed for hotel {$hotel->id}: HTTP {$response->status()}");

                return;
            }

            $season = $this->mapConditionToSeason(
                $response->json('weather.0.main'),
                (float) $response->json('main.temp')
            );

            Cache::put($this->cacheKey($hotel), $season->value, now()->addHours(self::CACHE_TTL_HOURS));
        } catch (Throwable $e) {
            Log::warning("Weather refresh failed for hotel {$hotel->id}: {$e->getMessage()}");
        }
    }

    /**
     * OpenWeatherMap has no notion of "season" — approximate an Indian seasonal
     * theme from the current condition + temperature. Active rain always reads
     * as monsoon regardless of temperature; otherwise a simple heat threshold
     * splits the rest into summer/winter.
     */
    private function mapConditionToSeason(?string $condition, float $tempCelsius): Season
    {
        if (in_array($condition, ['Rain', 'Drizzle', 'Thunderstorm'], true)) {
            return Season::Monsoon;
        }

        return $tempCelsius >= 30 ? Season::Summer : Season::Winter;
    }

    private function cacheKey(Hotel $hotel): string
    {
        return "weather:{$hotel->id}";
    }
}
