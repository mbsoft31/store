<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * Show the tenant's profile.
     */
    public function edit(Request $request): Response
    {
        $tenant = $request->user()->tenant;

        return Inertia::render('settings/tenant', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'settings' => [
                    'logo' => $tenant->settings['logo'] ?? null,
                    'color' => $tenant->settings['color'] ?? null,
                    'currency' => $tenant->settings['currency'] ?? null,
                    'timezone' => $tenant->settings['timezone'] ?? null,
                    'locale' => $tenant->settings['locale'] ?? null,
                ],
                'created_at' => $tenant->created_at,
                'updated_at' => $tenant->updated_at,
            ],
        ]);
    }

    /**
     * Update the tenant's profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'settings.logo' => ['nullable', 'string', 'max:255'],
            'settings.color' => ['nullable', 'string', 'max:255'],
            'settings.currency' => ['nullable', 'string', 'max:10'],
            'settings.timezone' => ['nullable', 'string', 'max:64'],
            'settings.locale' => ['nullable', 'string', 'max:10'],
        ]);

        $tenant = $request->user()->tenant;
        $oldName = $tenant->name;
        $oldLogo = $tenant->settings['logo'] ?? null;
        $newName = $request->input('name');
        $newSettings = [
            'logo' => $request->input('settings.logo'),
            'color' => $request->input('settings.color'),
            'currency' => $request->input('settings.currency'),
            'timezone' => $request->input('settings.timezone'),
            'locale' => $request->input('settings.locale'),
        ];

        // Check if name changed and logo is default, then update logo
        $defaultLogoPrefix = 'https://ui-avatars.com/api/?name=';
        if ($oldName !== $newName && $oldLogo && str_starts_with($oldLogo, $defaultLogoPrefix)) {
            $slug = str($newName)->slug();
            $newSettings['logo'] = $defaultLogoPrefix . $slug . '&background=' . $newSettings['color'] ?? 'random';
        }

        // Check if color changed and logo is default, then update logo
        $oldColor = $tenant->settings['color'] ?? null;
        $newColor = $newSettings['color'];
        if ($oldColor !== $newColor && $oldLogo && str_starts_with($oldLogo, $defaultLogoPrefix)) {
            $slug = str($newName)->slug();
            $color = $newColor ?? 'random';
            $newSettings['logo'] = $defaultLogoPrefix . $slug . '&background=' . $color;
        }

        $tenant->update([
            'name' => $newName,
            'settings' => $newSettings,
        ]);

        return to_route('tenant.edit')->with('status', 'tenant-updated');
    }
}
