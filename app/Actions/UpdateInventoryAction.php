<?php
// app/Actions/UpdateInventoryAction.php

namespace App\Actions;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Events\InventoryUpdated;

class UpdateInventoryAction
{
    public function execute(string $productId, int $quantity, ?string $note = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'note' => $note,
            'occurred_at' => now(),
        ]);

        $product = Product::find($productId);
        
        // Check for low stock
        if ($product->isLowStock()) {
            // Dispatch low stock event
            InventoryUpdated::dispatch($product, $movement);
        }

        return $movement;
    }
}
