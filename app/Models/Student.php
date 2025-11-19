<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'nis',
        'name',
        'gender',
        'class_id',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'active' => 'boolean',
        'class_id' => 'integer',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return true;
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'name',
                'nis',
                'classroom.name',
            ]
        ];
    }
}
