<?php

namespace Database\Factories;

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
            'tenant_id' => \App\Models\Tenant::factory(),
            'sku' => $this->faker->unique()->ean8(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price_cents' => $this->faker->numberBetween(100, 10000),
            'image_key' => null,
            'is_active' => true,
        ];
    }
}
