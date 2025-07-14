<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    // Store a new user (owner only)
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'role' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $tenant = Auth::user()->tenant;
        $user = $tenant->users()->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('users')->with('status', 'User created successfully.');
    }

    // Update an existing user (owner only)
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string'],
        ]);

        $user->update([
            'name' => $request->input('name'),
            'role' => $request->input('role'),
        ]);

        return redirect()->route('users')->with('status', 'User updated successfully.');
    }

    // Delete a user (owner only)
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();
        return redirect()->route('users')->with('status', 'User deleted successfully.');
    }
}
