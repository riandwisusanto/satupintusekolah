<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return $this->students()->count() === 0;
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
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
