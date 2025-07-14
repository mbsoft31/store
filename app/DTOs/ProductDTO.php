<?php
// app/DTOs/ProductDTO.php

namespace App\DTOs;

use Illuminate\Support\Facades\Auth;

readonly class ProductDTO
{
    public function __construct(
        public string $tenantId,
        public string $sku,
        public string $name,
        public ?string $description,
        public int $priceCents,
        public ?string $imageKey = null,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId: Auth::user()->tenant_id,
            sku: $data['sku'],
            name: $data['name'],
            description: $data['description'] ?? null,
            priceCents: $data['price_cents'],
            imageKey: $data['image_key'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }
}