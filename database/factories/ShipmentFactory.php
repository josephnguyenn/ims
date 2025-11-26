<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shipment>
 */
class ShipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $orderDate = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'order_date' => $orderDate,
            'expired_date' => fake()->optional()->dateTimeBetween($orderDate, '+2 years'),
            'total_cost' => fake()->randomFloat(2, 1000, 50000),
            'shipment_supplier_id' => \App\Models\ShipmentSupplier::factory(),
            'storage_id' => \App\Models\Storage::factory(),
        ];
    }
}
