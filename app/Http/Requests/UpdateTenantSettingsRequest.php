<?php
// app/Http/Requests/UpdateTenantSettingsRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\UserRole;

class UpdateTenantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::OWNER);
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:100',
            'settings' => 'sometimes|array',
            'settings.currency' => 'sometimes|string|size:3',
            'settings.tax_rate' => 'sometimes|numeric|min:0|max:1',
            'settings.timezone' => 'sometimes|string',
            'settings.branding' => 'sometimes|array',
            'settings.branding.logo_url' => 'sometimes|url',
            'settings.branding.primary_color' => 'sometimes|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ];
    }
}
