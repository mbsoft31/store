<?php
// app/DTOs/CreateOrderDTO.php

namespace App\DTOs;

use App\Enums\OrderChannel;
use Illuminate\Support\Facades\Auth;

readonly class CreateOrderDTO
{
    public function __construct(
        public string $tenantId,
        public OrderChannel $channel,
        public ?string $customerEmail,
        public array $items,
        public ?string $paymentMethod = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId: Auth::user()->tenant_id,
            channel: OrderChannel::from($data['channel']),
            customerEmail: $data['customer_email'] ?? null,
            items: $data['items'],
            paymentMethod: $data['payment_method'] ?? null,
        );
    }
}
