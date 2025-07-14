<?php
// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price_cents',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor for formatted unit price
    public function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->unit_price_cents / 100,
            set: fn($value) => ['unit_price_cents' => $value * 100]
        );
    }

    // Get total for this item
    public function getTotal(): int
    {
        return $this->quantity * $this->unit_price_cents;
    }
}