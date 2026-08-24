<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = implode(' ', (array) fake()->unique()->words(3));

        return [
            'category_id' => Category::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'reference' => strtoupper(fake()->unique()->bothify('REF-###')),
            'description' => fake()->paragraph(),
            'status' => PublishStatus::Draft,
            'featured' => false,
            'price' => fake()->randomFloat(2, 50, 5000),
            'width_cm' => fake()->numberBetween(20, 200),
            'height_cm' => fake()->numberBetween(20, 300),
            'weight_kg' => fake()->randomFloat(2, 1, 500),
            'stock' => fake()->numberBetween(0, 20),
            'custom_properties' => null,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the product is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Published,
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }
}
