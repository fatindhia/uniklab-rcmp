<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic key/value store backing the Super Admin Settings page — new
 * settings (Email, Outlook, Booking Rules, Operating Hours, Notifications,
 * ...) can be added as new keys without another migration.
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Request-local cache: this table is read on nearly every request (every
     * booking page load checks maintenance mode) but written rarely, so one
     * query per request is enough regardless of how many keys are read.
     */
    private static ?array $cache = null;

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::loadCache()[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        if (static::$cache !== null) {
            static::$cache[$key] = $value;
        }
    }

    private static function loadCache(): array
    {
        if (static::$cache === null) {
            static::$cache = static::query()->pluck('value', 'key')->all();
        }

        return static::$cache;
    }
}
