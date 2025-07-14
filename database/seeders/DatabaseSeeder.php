<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        

        $tenant = Tenant::factory()->create([
            'name' => 'Test Store',
            'slug' => 'test-store',
            'settings' => [
                'logo' => 'https://ui-avatars.com/api/?name=test-store&background=#d71919',
                'color' => '#d71919',
                'currency' => 'DZD',
                'tax_rate' => 0.00,
                'timezone' => 'CET',
                'locale' => 'ar',
            ],
        ]);

        User::factory()->create([
            'role' => 'owner',
            'tenant_id' => $tenant->id,
            'email' => 'admin@mail.com',
        ]);

        User::factory()->create([
            'role' => 'manager',
            'tenant_id' => $tenant->id,
            'email' => 'manager@mail.com',
        ]);

        User::factory()->create([
            'role' => 'cashier',
            'tenant_id' => $tenant->id,
            'email' => 'cashier@mail.com',
        ]);
    }
}
