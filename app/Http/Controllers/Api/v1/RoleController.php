<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Role::query(),
            Role::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        $role = Role::with(['permissions'])->find($id);
        try {
            return apiResponse('Role fetched successfully', ['role' => $role]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(StoreRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $role = Role::create([
                'label' => $validated['name'],
                'name' => str_replace(' ', '_', strtolower($validated['name'])),
            ]);
            $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);
            DB::commit();
            return apiResponse(__('message.role_create_success'), ['role' => $role]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $role = Role::find($id);
            $role->update([
                'label' => $validated['name'],
                'name' => str_replace(' ', '_', strtolower($validated['name'])),
            ]);
            $permissionIds = Permission::whereIn('name', $validated['permissions'])->pluck('id')->toArray();
            $role->permissions()->sync($permissionIds);
            DB::commit();
            return apiResponse(__('message.role_update_success'), ['role' => $role]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $role = Role::find($id);
            if (! $role) {
                return apiResponse('Role not found', null, 404);
            }
            $role->permissions()->detach();
            $role->delete();

            DB::commit();
            return apiResponse(__('message.role_delete_success'), ['role' => $role]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getPermissions()
    {
        $permissions = Permission::all();
        try {
            return apiResponse(__('message.permission_get_success'), ['permissions' => $permissions]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
