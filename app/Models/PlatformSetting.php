<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Generic key/value settings controlled by Super Admin. Reads are cached
 * indefinitely (the table changes rarely and is read on every customer page
 * load), invalidated on write via set().
 */
class PlatformSetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = Cache::rememberForever(
            static::cacheKey($key),
            fn () => static::where('key', $key)->value('value')
        );

        return $value ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(static::cacheKey($key));
    }

    private static function cacheKey(string $key): string
    {
        return "platform_setting:{$key}";
    }
}
