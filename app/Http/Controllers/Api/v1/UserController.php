<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            User::when(!auth()->user()->isAdmin(), function ($query) {
                return $query->where('id', auth()->user()->id);
            }),
            User::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            if (isset($validated['photo']) && $validated['photo'] instanceof \Illuminate\Http\UploadedFile) {
                $photoPath = $validated['photo']->store('photos', 'public');
                $validated['photo'] = $photoPath;
            }

            $user = User::create($validated);

            DB::commit();
            return apiResponse('Pengguna berhasil dibuat', ['user' => $user]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(UserRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $user = User::find($id);

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
                    // Delete old photo
                    if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                        Storage::disk('public')->delete($user->photo);
                    }
                }
                // If photo is string (existing path), keep it
                else {
                    $validated['photo'] = $user->photo;
                }
            }

            $user->update($validated);
            DB::commit();
            return apiResponse('Pengguna berhasil diupdate', ['user' => $user]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);

            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $user->delete();
            DB::commit();
            return apiResponse('Pengguna berhasil dihapus', ['user' => $user]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getTeachers()
    {
        try {
            $teachers = User::where('role_id', 2)->select('id', 'name')->get();

            return $teachers;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
