<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'string',
                    'min:12',
                    'confirmed',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
                ]
            ],
            [
                'password.regex' => 'Password must contain at least: 
                    1 uppercase letter, 1 lowercase letter, 1 number, and 1 special symbol'
            ]
        );
    
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
    
        $token = $user->createToken(
            name: 'auth-token',
            expiresAt: now()->addHours(24)
        )->plainTextToken;
    
        return response()->json([
            'message' => 'User registered and authenticated successfully',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => config('sanctum.expiration') * 60,
            'user' => $user->makeHidden(['password', 'email_verified_at']),
        ], 201);
    }

    
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate(
            [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ],
            [
                'email.required' => 'The email field is required.',
                'password.required' => 'The password field is required.'
            ]
        );
    
        $user = User::where('email', $credentials['email'])->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials provided.'],
            ]);
        }
    
        $token = $user->createToken(
            name: 'auth-token',
            expiresAt: now()->addHours(24)
        )->plainTextToken;
    
        return response()->json([
            'message' => 'Authentication successful',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'expires_in' => config('sanctum.expiration') * 60,
            'user' => $user->makeHidden(['password', 'email_verified_at']),
        ]);
    }

   
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out from all devices',
        ]);
    }
}