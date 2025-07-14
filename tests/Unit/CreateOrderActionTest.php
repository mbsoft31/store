<?php

// This file is now migrated to Pest

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class, WithFaker::class);

test('execute creates order, items, and inventory movements', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // fake products and inventory movements
    \App\Models\Product::factory()->count(2)->create([
        'tenant_id' => $user->tenant_id,
    ]);

    \Illuminate\Support\Facades\Event::fake();

    $items = \App\Models\Product::all()->map(function ($product, $index) {
        return [
            'product_id' => $product->id,
            'quantity' => $index === 0 ? 2 : 1,
            'unit_price_cents' => $index === 0 ? 500 : 1000,
        ];
    })->toArray();

    $dto = \App\DTOs\CreateOrderDTO::fromRequest([
        'tenantId' => $user->tenant_id,
        'channel' => 'online',
        'customerEmail' => 'test@example.com',
        'items' => $items,
    ]);

    $action = new \App\Actions\CreateOrderAction();
    $order = $action->execute($dto);

    expect($order)->toBeInstanceOf(\App\Models\Order::class)
        ->and($order->items)->toHaveCount(2)
        ->and($order->total_cents)->toBe(2000);
    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'customer_email' => $dto->customerEmail,
    ]);
    $this->assertDatabaseHas('order_items', [
        'order_id' => $order->id,
        'product_id' => $items[0]['product_id'],
        'quantity' => 2,
    ]);
    $this->assertDatabaseHas('inventory_movements', [
        'product_id' => $items[0]['product_id'],
        'quantity' => -2,
    ]);
});

test('execute throws exception on empty items', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    $this->expectException(\InvalidArgumentException::class);
    $dto = \App\DTOs\CreateOrderDTO::fromRequest([
        'tenantId' => $user->tenant_id,
        'channel' => 'online',
        'customerEmail' => 'test@example.com',
        'items' => [],
    ]);
    $action = new \App\Actions\CreateOrderAction();
    $action->execute($dto);
});
