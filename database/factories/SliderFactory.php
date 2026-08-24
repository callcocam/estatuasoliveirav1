<?php

namespace Database\Factories;

use App\Enums\PublishStatus;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slider>
 */
class SliderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(5),
            'description' => fake()->sentence(),
            'cta_label' => 'Saiba mais',
            'cta_url' => '/estatuas',
            'status' => PublishStatus::Draft,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the slider is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PublishStatus::Published,
        ]);
    }
}
