<?php

namespace App\Enums;

/**
 * The resolved menu theme — always one of these three, never "auto".
 * WeatherThemeMode is the configuration input; this is the render-time output.
 */
enum Season: string
{
    case Summer = 'summer';
    case Monsoon = 'monsoon';
    case Winter = 'winter';

    public function icon(): string
    {
        return match ($this) {
            self::Summer => '☀️',
            self::Monsoon => '🌧️',
            self::Winter => '❄️',
        };
    }

    public function deco(): string
    {
        return match ($this) {
            self::Summer => '☀️',
            self::Monsoon => '💧',
            self::Winter => '❄️',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Summer => 'a bright, sunny day',
            self::Monsoon => 'the rains outside',
            self::Winter => 'the cool winter air',
        };
    }
}
