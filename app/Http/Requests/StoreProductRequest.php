<?php
// app/Http/Requests/StoreProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::user()?->canManageProducts() ?? false;
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()?->tenant_id);
                }),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_cents' => 'required|integer|min:0',
            'image_key' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'SKU already exists in your store.',
            'price_cents.min' => 'Price must be greater than or equal to 0.',
        ];
    }
}
