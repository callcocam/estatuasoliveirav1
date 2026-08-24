<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = Str::random(12).'.jpg';

        return [
            'mediable_type' => (new Product)->getMorphClass(),
            'mediable_id' => Product::factory(),
            'collection' => 'default',
            'disk' => 'public',
            'path' => 'media/'.$fileName,
            'file_name' => $fileName,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 2_000_000),
            'sort_order' => 0,
            'custom_properties' => null,
        ];
    }
}
