<?php
// app/Http/Resources/ProductResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'description' => $this->description,
            'price_cents' => $this->price_cents,
            'image_key' => $this->image_key,
            'image_url' => $this->image_url,
            'is_active' => $this->is_active,
            'current_stock' => $this->when($this->relationLoaded('inventoryMovements'), 
                fn() => $this->getCurrentStock()
            ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'recent_movements' => InventoryMovementResource::collection(
                $this->whenLoaded('inventoryMovements', 
                    fn() => $this->inventoryMovements->take(5)
                )
            ),
        ];
    }
}
