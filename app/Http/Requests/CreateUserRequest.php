<?php
// app/Http/Requests/CreateUserRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\UserRole;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::OWNER);
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()?->tenant_id);
                }),
            ],
            'role' => ['required', Rule::enum(UserRole::class)],
            'send_invite' => 'boolean',
        ];
    }
}
