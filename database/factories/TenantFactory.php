<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        $slug = str($name)->slug();
        return [
            'name' => $name,
            'slug' => $slug,
            'settings' => json_encode([
                'logo' => 'https://ui-avatars.com/api/?name=' . $slug . '&background=random',
                'color' => '#000000',
                'currency' => 'USD',
                'timezone' => 'UTC',
                'locale' => 'en',
            ]),
        ];
    }
}
