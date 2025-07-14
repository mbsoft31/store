<?php
// app/Http/Requests/StoreOrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\OrderChannel;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cashiers can create orders
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::enum(OrderChannel::class)],
            'customer_email' => 'nullable|email',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_cents' => 'required|integer|min:0',
            'payment_method' => 'nullable|string',
        ];
    }
}
