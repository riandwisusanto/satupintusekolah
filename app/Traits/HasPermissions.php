<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasPermissions
{
    public function hasPermission($key)
    {
        $roles = $this->roles()->with('permissions')->get();

        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                if ($permission->key === $key || Str::is($key, $permission->key)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAllPermissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id')
            ->values();
    }

    public function getPermissionKeys()
    {
        return $this->getAllPermissions()->pluck('key')->toArray();
    }
}
