<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = ['key', 'value', 'label', 'group', 'type'];

    /**
     * Ambil nilai setting berdasarkan key, dengan cache 60 menit.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = 'app_setting_' . $key;

        $value = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($key) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : null;
        });

        return $value ?? $default;
    }

    /**
     * Ambil semua setting dalam satu group.
     */
    public static function getGroup(string $group): \Illuminate\Support\Collection
    {
        return Cache::remember('app_settings_group_' . $group, now()->addMinutes(60), function () use ($group) {
            return static::where('group', $group)->get()->keyBy('key');
        });
    }

    /**
     * Simpan nilai setting dan bersihkan cache-nya.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('app_setting_' . $key);
        // Juga bersihkan cache grup (cari grup setting ini)
        $setting = static::where('key', $key)->first();
        if ($setting) {
            Cache::forget('app_settings_group_' . $setting->group);
        }
    }

    /**
     * Bersihkan semua cache settings.
     */
    public static function clearAllCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget('app_setting_' . $setting->key);
            Cache::forget('app_settings_group_' . $setting->group);
        }
    }

    /**
     * Helper: ambil wa_visible_roles sebagai array.
     */
    public static function getWaVisibleRoles(): array
    {
        $raw = static::get('wa_visible_roles', '["student"]');
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : ['student'];
    }

    /**
     * Cek apakah role tertentu boleh melihat tombol WA.
     */
    public static function isWaVisibleForRole(string $role): bool
    {
        if (!static::get('wa_button_enabled', '1')) {
            return false;
        }
        return in_array($role, static::getWaVisibleRoles());
    }
}
