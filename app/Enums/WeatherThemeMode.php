<?php

namespace App\Enums;

/**
 * Configuration input for the menu's weather theme — 'auto' defers to real
 * weather (or the calendar-month fallback); the rest force a specific Season.
 */
enum WeatherThemeMode: string
{
    case Auto = 'auto';
    case Summer = 'summer';
    case Monsoon = 'monsoon';
    case Winter = 'winter';

    public function toSeason(): ?Season
    {
        return match ($this) {
            self::Auto => null,
            self::Summer => Season::Summer,
            self::Monsoon => Season::Monsoon,
            self::Winter => Season::Winter,
        };
    }

    public function icon(): string
    {
        return $this->toSeason()?->icon() ?? '🌦️';
    }
}
