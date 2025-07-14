<?php
// app/Http/Controllers/Api/AuthController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create tokens
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['refresh'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in' => 3600,
            'user' => new UserResource($user->load('tenant')),
        ]);
    }

    public function refresh(RefreshTokenRequest $request)
    {
        $user = Auth::user();

        // Revoke old tokens
        $user->tokens()->where('name', 'access_token')->delete();

        // Create new access token
        $accessToken = $user->createToken('access_token', ['*'], now()->addHour())->plainTextToken;
        
        return response()->json([
            'access_token' => $accessToken,
            'expires_in' => 3600,
        ]);
    }

    public function me(Request $request)
    {
        return new UserResource($request->user()->load('tenant'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }
}
