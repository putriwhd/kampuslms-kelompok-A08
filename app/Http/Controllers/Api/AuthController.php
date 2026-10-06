<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Autentikasi pengguna dan mengembalikan Sanctum PlainTextToken.
     * Dilindungi rate limiting throttle:5,1 di rute.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Pesan error seragam untuk mencegah user enumeration
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau kata sandi yang Anda masukkan salah.'],
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'api-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Revoke token saat ini untuk logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout.',
        ]);
    }

    /**
     * Mengembalikan data profil dan peran pengguna yang sedang login.
     */
    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
