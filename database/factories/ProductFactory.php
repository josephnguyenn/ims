<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        return [
            'name' => fake()->words(3, true),
            'code' => strtoupper(fake()->bothify('???###')),
            'original_quantity' => fake()->numberBetween(10, 100),
            'actual_quantity' => fake()->numberBetween(5, 50),
            'price' => fake()->randomFloat(2, 10, 500),
            'cost' => fake()->randomFloat(2, 5, 250),
            'total_cost' => function (array $attributes) {
                return $attributes['cost'] * $attributes['original_quantity'];
            },
            'category_id' => ProductCategory::factory(),
            'shipment_id' => \App\Models\Shipment::factory(),
            'tax' => fake()->randomElement([0, 15, 21]),
            'expired_date' => fake()->optional()->dateTimeBetween('now', '+2 years'),
        ];
    }
}
