<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

    public function updateProfile(ProfileUpdateRequest $request)
    {
        $validated = $request->validated();
        $user = auth()->user();

        DB::beginTransaction();
        try {
            // Handle photo upload
            if (isset($validated['photo'])) {
                // Delete old photo if exists
                if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                // Upload new photo if it's a file
                if ($validated['photo'] instanceof \Illuminate\Http\UploadedFile) {
                    $photoPath = $validated['photo']->store('photos', 'public');
                    $validated['photo'] = $photoPath;
                }
                // If photo is null or empty string, remove it
                elseif (empty($validated['photo'])) {
                    $validated['photo'] = null;
                }
                // If photo is string (existing path), keep it
                else {
                    $validated['photo'] = $user->photo;
                }
            }

            // Handle password update
            if (isset($validated['password']) && !empty($validated['password'])) {
                $validated['password'] = Hash::make($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            // Reload user with role and permissions
            $user->load('role');

            DB::commit();
            return apiResponse('Profile berhasil diupdate', [
                'user' => [
                    ...$user->toArray(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ]
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
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
