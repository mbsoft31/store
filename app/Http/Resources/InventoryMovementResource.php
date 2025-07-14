<?php
// app/Http/Resources/InventoryMovementResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->whenLoaded('product', fn() => $this->product->name),
            'quantity' => $this->quantity,
            'note' => $this->note,
            'occurred_at' => $this->occurred_at,
        ];
    }
}
