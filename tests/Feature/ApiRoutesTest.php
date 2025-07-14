<?php

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class, WithFaker::class);


it('public routes are accessible', function () {
    $tenant = Tenant::create([
        'name' => 'Test Store',
        'slug' => Str::slug('Test Store'),
        'settings' => [
            'currency' => 'USD',
            'tax_rate' => 0.08,
            'timezone' => 'UTC',
        ],
    ]);

    // Create owner user
    $owner = User::create([
        'tenant_id' => $tenant->id,
        'email' => 'owner@example.com',
        'password' => Hash::make('password123'),
        'role' => UserRole::OWNER,
    ]);


    $this->postJson('/api/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ])->assertStatus(422); // Validation or unauthorized

    $this->postJson('/api/tenants', [
        'name' => 'Test Store',
        'owner_email' => 'owner@example.com',
        'owner_password' => 'password123',
    ])->assertStatus(422); // Validation error expected
});

it('protected routes require authentication', function () {
    $this->getJson('/api/users')->assertStatus(401);
    $this->getJson('/api/products')->assertStatus(401);
    $this->getJson('/api/orders')->assertStatus(401);
});
