<?php

namespace App\Console\Commands;

use App\Enums\HotelStatus;
use App\Models\Hotel;
use App\Services\WeatherThemeService;
use Illuminate\Console\Command;

class RefreshWeatherThemes extends Command
{
    protected $signature = 'weather:refresh';

    protected $description = 'Refresh the cached seasonal weather theme for every active hotel with a location set';

    public function __construct(private readonly WeatherThemeService $weather)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        $hotels = Hotel::where('status', HotelStatus::Active)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $hotels->each(fn (Hotel $hotel) => $this->weather->refreshCacheForHotel($hotel));

        $this->info("Weather themes refreshed for {$hotels->count()} hotel(s).");
    }
}
