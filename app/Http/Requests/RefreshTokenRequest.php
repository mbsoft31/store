<?php
// app/Http/Requests/RefreshTokenRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RefreshTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'refresh_token' => 'required|string',
        ];
    }
}
