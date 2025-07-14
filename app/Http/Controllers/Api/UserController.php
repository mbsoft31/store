<?php
// app/Http/Controllers/Api/UserController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::where('tenant_id', $request->user()->tenant_id)
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->paginate($request->get('limit', 20));

        return UserResource::collection($users);
    }

    public function store(CreateUserRequest $request)
    {
        $tempPassword = Str::random(12);
        
        $user = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'email' => $request->email,
            'password' => Hash::make($tempPassword),
            'role' => UserRole::from($request->role),
        ]);

        // TODO: Send invitation email with temporary password
        
        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role->value,
            'created_at' => $user->created_at,
            'invite_sent' => true,
        ], 201);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        
        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);
        
        $user->update($request->only(['role']));
        
        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);
        
        $user->delete();
        
        return response()->json(['message' => 'User deleted successfully']);
    }
}
