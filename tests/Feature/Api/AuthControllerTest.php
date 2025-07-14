<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\postJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Use in-memory sqlite and refresh DB
    $this->artisan('migrate:fresh');
});

test('user can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'access_token',
        'refresh_token',
        'expires_in',
        'user',
    ]);
});

test('user cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $response = postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('email');
});

test('authenticated user can get their info', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = getJson('/api/me');
    $response->assertOk();
    $response->assertJsonStructure(['data' => ['id', 'name', 'email', 'tenant']]);
});

test('unauthenticated user cannot get their info', function () {
    $response = getJson('/api/me');
    $response->assertStatus(401);
});

test('user can logout', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $response = postJson('/api/logout');
    $response->assertOk();
    $response->assertJson(['message' => 'Successfully logged out']);
});

// Optionally, add a test for refresh if route exists
// test('user can refresh token', function () {
//     $user = User::factory()->create();
//     Sanctum::actingAs($user);
//
//     $response = postJson('/api/refresh', [
//         'refresh_token' => 'dummy',
//     ]);
//     $response->assertOk();
//     $response->assertJsonStructure(['access_token', 'expires_in']);
// });

