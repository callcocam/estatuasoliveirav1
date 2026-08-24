<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Enums\PublishStatus;
use Database\Factories\SliderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $description
 * @property string|null $cta_label
 * @property string|null $cta_url
 * @property PublishStatus $status
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Media> $media
 */
#[Fillable(['title', 'subtitle', 'description', 'cta_label', 'cta_url', 'status', 'sort_order'])]
class Slider extends Model
{
    /** @use HasFactory<SliderFactory> */
    use HasFactory, HasMedia, HasUlids, SoftDeletes;

    /**
     * Scope the query to published sliders.
     *
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PublishStatus::Published);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'sort_order' => 'integer',
        ];
    }
}
