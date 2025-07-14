<?php
// app/Events/InventoryUpdated.php

namespace App\Events;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Product $product;
    public InventoryMovement $movement;

    public function __construct(Product $product, InventoryMovement $movement)
    {
        $this->product = $product;
        $this->movement = $movement;
    }
}
