<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Guru;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = Guru::query()->create([
            'nama_lengkap' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'mapel' => $request->validated('mapel'),
            'role' => 'guru',
        ]);

        event(new Registered($user));

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->nama_lengkap,
                    'email' => $user->email,
                    'role' => $user->role,
                    'mapel' => $user->mapel,
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
                    'name' => $user->nama_lengkap,
                    'email' => $user->email,
                    'role' => $user->role,
                    'mapel' => $user->mapel,
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
                'name' => $user->nama_lengkap,
                'email' => $user->email,
                'role' => $user->role,
                'mapel' => $user->mapel,
            ],
        ]);
    }

    public function verifyEmail(Request $request, $id, $hash): RedirectResponse
    {
        $user = Guru::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect('http://localhost:5173/login?error=invalid_verification_link');
        }

        if ($user->hasVerifiedEmail()) {
            return redirect('http://localhost:5173/login?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return redirect('http://localhost:5173/login?verified=1');
    }

    public function resendVerification(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email sudah terverifikasi.'
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Link verifikasi telah dikirim ulang ke email Anda.'
        ]);
    }
}
