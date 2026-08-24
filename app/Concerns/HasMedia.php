<?php

namespace App\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMedia
{
    /**
     * Get all media attached to the model.
     *
     * @return MorphMany<Media, $this>
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')->orderBy('sort_order');
    }

    /**
     * Get the cover media (first item of the given collection).
     */
    public function coverMedia(string $collection = 'default'): ?Media
    {
        return $this->media
            ->firstWhere('collection', $collection);
    }
}
