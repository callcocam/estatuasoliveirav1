<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UniqueSlug
{
    /**
     * Generate a unique slug for the given model class.
     *
     * @param  class-string<Model>  $modelClass
     */
    public static function for(string $modelClass, string $value, ?string $ignoreId = null): string
    {
        $base = Str::slug($value) !== '' ? Str::slug($value) : Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while ($modelClass::query()
            ->withoutGlobalScopes()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
