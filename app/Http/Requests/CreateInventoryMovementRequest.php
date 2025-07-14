<?php
// app/Http/Requests/CreateInventoryMovementRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInventoryMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageProducts();
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|uuid|exists:products,id',
            'quantity' => 'required|integer|not_in:0',
            'note' => 'nullable|string|max:255',
        ];
    }
}
