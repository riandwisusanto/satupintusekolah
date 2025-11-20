<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasPermissions, HasApiTokens, LogsModelChanges, HasApiQueryConfig;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'nip',
        'phone',
        'photo',
        'role_id',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return true;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permissions()
    {
        return $this->role->permissions();
    }

    public function classes()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'teacher_id');
    }

    public function journals()
    {
        return $this->hasMany(Journal::class, 'teacher_id');
    }

    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class, 'teacher_id');
    }

    public function teacherAttendances()
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'name',
                'email',
                'role.label',
            ]
        ];
    }
}
