<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ]);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi salah.'],
            ]);
        }

        $user = auth()->user();

        return response()->json([
            'message' => 'Login berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Exception $e) {
            // ignore
        }

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }
}
