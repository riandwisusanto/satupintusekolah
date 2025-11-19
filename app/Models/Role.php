<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasApiQueryConfig;
    protected $guarded = [];

    protected $fillable = ['name', 'label'];
    protected $hidden = ['pivot'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles', 'role_id', 'permission_id');
    }

    // public function users()
    // {
    //     return $this->hasMany(User::class);
    // }

    public function syncPermissions(array $permissionIds)
    {
        $this->permissions()->sync($permissionIds);
    }
}
