<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
        'label',
        'options',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember("system_setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });

        // Track the key for bulk flush
        $keys = Cache::get('system_setting_keys', []);
        if (!in_array($key, $keys)) {
            $keys[] = $key;
            Cache::put('system_setting_keys', $keys, 3600);
        }

        return $value;
    }

    public static function setValue(string $key, mixed $value): void
    {
        $existing = static::where('key', $key)->first();
        if ($existing) {
            $existing->update(['value' => $value]);
        } else {
            static::create([
                'key' => $key,
                'value' => $value,
                'group' => 'general',
                'type' => 'text',
                'label' => ucwords(str_replace('_', ' ', $key)),
            ]);
        }
        static::flushCache($key);
    }

    public static function flushCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("system_setting:{$key}");
        } else {
            // Flush all cached settings by tag or pattern
            $keys = Cache::get('system_setting_keys', []);
            foreach ($keys as $cachedKey) {
                Cache::forget("system_setting:{$cachedKey}");
            }
            Cache::forget('system_setting_keys');
        }
    }

    public static function getGroup(string $group): \Illuminate\Support\Collection
    {
        return static::where('group', $group)->orderBy('label')->get();
    }

    public static function getAllGrouped(): \Illuminate\Support\Collection
    {
        return static::orderBy('group')->orderBy('label')
            ->get()
            ->groupBy('group');
    }
}
