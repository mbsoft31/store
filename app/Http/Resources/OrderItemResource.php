<?php
// app/Http/Resources/OrderItemResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price, // formatted
            'unit_price_cents' => $this->unit_price_cents,
            'total' => $this->getTotal() / 100,
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
