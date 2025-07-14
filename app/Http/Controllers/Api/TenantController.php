<?php
// app/Http/Controllers/Api/TenantController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\UpdateTenantSettingsRequest;
use App\Http\Resources\TenantResource;
use App\Enums\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class TenantController extends Controller
{
    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function store(CreateTenantRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // Create tenant
            $tenant = Tenant::create([
                'name' => $request->name,
                'slug' => $request->slug ? $request->slug : Str::slug($request->name),
                'settings' => [
                    'currency' => 'USD',
                    'tax_rate' => 0.08,
                    'timezone' => 'UTC',
                ],
            ]);

            // Create owner user
            $owner = User::create([
                'tenant_id' => $tenant->id,
                'email' => $request->owner_email,
                'password' => Hash::make($request->owner_password),
                'role' => UserRole::OWNER,
            ]);

            $tenant->load('users');
            $tenant->owner = $owner;

            return response()->json([
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'created_at' => $tenant->created_at,
                'owner' => [
                    'id' => $owner->id,
                    'email' => $owner->email,
                    'role' => $owner->role->value,
                ],
            ], 201);
        });
    }

    public function settings(\Illuminate\Http\Request $request)
    {
        $tenant = $request->user()->tenant;

        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'settings' => $tenant->settings,
        ]);
    }

    public function updateSettings(UpdateTenantSettingsRequest $request)
    {
        $tenant = $request->user()->tenant;

        $tenant->update($request->only(['name', 'settings']));

        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'settings' => $tenant->settings,
        ]);
    }
}
