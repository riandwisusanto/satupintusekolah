<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            User::query(),
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
            $user->delete();
            DB::commit();
            return apiResponse('Pengguna berhasil dihapus', ['user' => $user]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
