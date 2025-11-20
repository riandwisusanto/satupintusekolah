<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])
            ->with('role')
            ->where('active', true)
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Mail atau password salah.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return apiResponse(
            'Login Berhasil',
            [
                'token' => $token,
                'user' => [
                    ...$user->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ]
            ]
        );
    }

    public function me()
    {
        $user = auth()->user()->load('role');
        return apiResponse(
            'User fetched',
            [
                'user' => [
                    ...$user->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ]
            ]
        );
    }

    public function logout()
    {
        try {
            auth()->user()->currentAccessToken()->delete();
            return apiResponse('Logout Berhasil');
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
