<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;

class Order extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'channel',
        'status',
        'total_cents',
        'customer_email',
        'paid_at',
    ];

    protected $casts = [
        'channel' => OrderChannel::class,
        'status' => OrderStatus::class,
        'total_cents' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // Accessor for formatted total
    public function total(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->total_cents / 100,
            set: fn($value) => ['total_cents' => $value * 100]
        );
    }

    // Calculate total from items
    public function calculateTotal(): int
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->unit_price_cents);
    }

    // Mark order as paid
    public function markAsPaid(): void
    {
        $this->update([
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    // Check if order is paid
    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    // Scope for paid orders
    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::PAID);
    }
}