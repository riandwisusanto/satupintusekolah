<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'day',
        'start_time',
        'end_time',
        'academic_year_id',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'teacher_id' => 'integer',
        'class_id' => 'integer',
        'subject_id' => 'integer',
        'academic_year_id' => 'integer',
        'active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
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

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function class()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'day',
                'teacher.name',
                'subject.name',
                'classroom.name',
            ],
            'with' => ['teacher', 'subject', 'classroom', 'academicYear']
        ];
    }
}
