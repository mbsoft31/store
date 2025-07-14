<?php

use App\Actions\UpdateInventoryAction;
use App\Events\InventoryUpdated;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

uses(TestCase::class);
uses(RefreshDatabase::class, WithFaker::class);

describe('UpdateInventoryAction', function () {
    it('creates an inventory movement and returns it', function () {
        $product = Product::factory()->create();
        $quantity = 5;
        $note = 'Stock added';

        $action = new UpdateInventoryAction();
        $movement = $action->execute($product->id, $quantity, $note);

        expect($movement)->toBeInstanceOf(InventoryMovement::class)
            ->and($movement->product_id)->toBe($product->id)
            ->and($movement->quantity)->toBe($quantity)
            ->and($movement->note)->toBe($note);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => $product->id,
            'quantity' => $quantity,
            'note' => $note,
        ]);
    });

    it('dispatches InventoryUpdated event if product is low stock', function () {
        $product = Product::factory()->create();
        $quantity = -1000; // force low stock
        Event::fake();

        $action = new UpdateInventoryAction();
        $movement = $action->execute($product->id, $quantity, 'Low stock test');

        Event::assertDispatched(InventoryUpdated::class, function ($event) use ($product, $movement) {
            return $event->product->id === $product->id && $event->movement->id === $movement->id;
        });
    });
});
