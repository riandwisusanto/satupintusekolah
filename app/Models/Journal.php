<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'date',
        'theme',
        'activity',
        'notes',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'teacher_id' => 'integer',
        'class_id' => 'integer',
        'date' => 'date',
        'active' => 'boolean',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return true;
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subjects()
    {
        return $this->hasMany(JournalSubject::class, 'teacher_journal_id');
    }


    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'theme',
                'activity',
                'date',
                'teacher.name',
                'classroom.name',
            ],
            'with' => ['teacher', 'subjects', 'classroom']
        ];
    }
}
