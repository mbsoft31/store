<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Product extends BaseModel
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'price_cents',
        'image_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_cents' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor for formatted price
    public function price(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->price_cents / 100,
            set: fn($value) => ['price_cents' => $value * 100]
        );
    }

    // Accessor for image URL
    public function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image_key ? Storage::url($this->image_key) : null
        );
    }

    // Get current stock
    public function getCurrentStock(): int
    {
        return $this->inventoryMovements()->sum('quantity');
    }

    // Check if product is low stock
    public function isLowStock(int $threshold = 10): bool
    {
        return $this->getCurrentStock() <= $threshold;
    }

    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for low stock products
    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->whereHas('inventoryMovements', function ($q) use ($threshold) {
            $q->havingRaw('SUM(quantity) <= ?', [$threshold]);
        });
    }
}