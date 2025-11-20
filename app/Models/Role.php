<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasApiQueryConfig, HasFactory;

    protected $fillable = [
        'name',
        'active',
    ];

    protected $hidden = ['pivot'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_roles', 'role_id', 'permission_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function syncPermissions(array $permissionIds)
    {
        $this->permissions()->sync($permissionIds);
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'name',
            ]
        ];
    }
}
