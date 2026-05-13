<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Iegūst iestatījuma vērtību pēc atslēgas
     */
    public static function get(string $key, $default = null)
    {
        $settings = Cache::remember('settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Iestata iestatījuma vērtību
     */
    public static function set(string $key, $value, ?string $group = null): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        Cache::forget('settings');
    }

    /**
     * Apkopo visus iestatījumus grupās
     */
    public static function getGrouped(): array
    {
        return self::all()
            ->groupBy('group')
            ->map(fn ($items) => $items->pluck('value', 'key'))
            ->toArray();
    }
}
