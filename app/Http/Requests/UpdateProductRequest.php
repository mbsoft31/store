<?php
// app/Http/Requests/UpdateProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageProducts() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price_cents' => 'sometimes|required|integer|min:0',
            'image_key' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }
}
