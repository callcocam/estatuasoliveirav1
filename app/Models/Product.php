<?php

namespace App\Models;

use App\Concerns\HasMedia;
use App\Enums\PublishStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $reference
 * @property string|null $description
 * @property PublishStatus $status
 * @property bool $featured
 * @property string|null $price
 * @property int|null $width_cm
 * @property int|null $height_cm
 * @property string|null $weight_kg
 * @property int $stock
 * @property array<string, mixed>|null $custom_properties
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Category|null $category
 * @property-read Collection<int, Media> $media
 */
#[Fillable([
    'category_id',
    'name',
    'slug',
    'reference',
    'description',
    'status',
    'featured',
    'price',
    'width_cm',
    'height_cm',
    'weight_kg',
    'stock',
    'custom_properties',
    'sort_order',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasMedia, HasUlids, SoftDeletes;

    /**
     * Get the category that the product belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope the query to published products.
     *
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PublishStatus::Published);
    }

    /**
     * Scope the query to featured products.
     *
     * @param  Builder<static>  $query
     */
    public function scopeFeatured(Builder $query): void
    {
        $query->where('featured', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
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
            'featured' => 'boolean',
            'price' => 'decimal:2',
            'weight_kg' => 'decimal:2',
            'width_cm' => 'integer',
            'height_cm' => 'integer',
            'stock' => 'integer',
            'custom_properties' => 'array',
            'sort_order' => 'integer',
        ];
    }
}
