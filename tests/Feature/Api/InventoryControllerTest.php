<?php

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\getJson;
use function Pest\Laravel\actingAs;

/*beforeEach(function () {
    // Create a tenant and a user for authentication
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'password' => Hash::make('password'),
    ]);
    actingAs($this->user);
});*/

uses(RefreshDatabase::class, WithFaker::class);

it('returns low stock products with default threshold', function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'manager',
        'password' => Hash::make('password'),
    ]);
    actingAs($this->user);

    $lowStockProduct = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    $lowStockProduct->inventoryMovements()->create([
        'quantity' => 2,
        'note' => 'Initial stock',
        'tenant_id' => $this->tenant->id,
        'occurred_at' => now(),
    ]);
    $highStockProduct = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    $highStockProduct->inventoryMovements()->create([
        'quantity' => 20,
        'note' => 'Initial stock',
        'tenant_id' => $this->tenant->id,
        'occurred_at' => now(),
    ]);

    $response = getJson('api/products/low-stock');
    $response->assertOk();
    $response->assertJsonStructure([
        'products',
        'count',
    ]);
    $data = $response->json('products');
    expect(collect($data)->pluck('id'))->toContain($lowStockProduct->id)
        ->not->toContain($highStockProduct->id);
});

it('returns low stock products with custom threshold', function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'manager',
        'password' => Hash::make('password'),
    ]);
    actingAs($this->user);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    $product->inventoryMovements()->create([
        'quantity' => 8,
        'note' => 'Initial stock',
        'tenant_id' => $this->tenant->id,
        'occurred_at' => now(),
    ]);

    $response = getJson('/api/products/low-stock?threshold=9');
    $response->assertOk();
    $data = $response->json('products');
    expect(collect($data)->pluck('id'))->toContain($product->id);
});

it('returns empty if no products are low stock', function () {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create([
        'tenant_id' => $this->tenant->id,
        'role' => 'manager',
        'password' => Hash::make('password'),
    ]);
    actingAs($this->user);

    $product = Product::factory()->create([
        'tenant_id' => $this->tenant->id,
    ]);
    $product->inventoryMovements()->create([
        'quantity' => 50,
        'note' => 'Initial stock',
        'tenant_id' => $this->tenant->id,
        'occurred_at' => now(),
    ]);

    $response = getJson('/api/products/low-stock');

    dump($response->json());

    $response->assertOk();
    $response->assertJson(['count' => 0]);
    expect($response->json('products'))->toBeArray()->toBeEmpty();
});

