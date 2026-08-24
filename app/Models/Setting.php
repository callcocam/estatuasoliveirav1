<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $id
 * @property string $group
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['group', 'key', 'value'])]
class Setting extends Model
{
    use HasUlids, SoftDeletes;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(function (Setting $setting) {
            Cache::forget("settings.{$setting->key}");
        });

        static::deleted(function (Setting $setting) {
            Cache::forget("settings.{$setting->key}");
        });
    }

    /**
     * Get a setting value by key, cached forever until changed.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever(
            "settings.{$key}",
            fn () => static::query()->where('key', $key)->value('value')
        ) ?? $default;
    }

    /**
     * Create or update a setting value by key.
     */
    public static function set(string $key, ?string $value, string $group = 'general'): static
    {
        return static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group],
        );
    }
}
