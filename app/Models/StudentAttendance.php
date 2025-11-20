<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'date',
        'teacher_id',
        'academic_year_id',
        'class_id',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'date' => 'date',
        'teacher_id' => 'integer',
        'academic_year_id' => 'integer',
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

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function subjects()
    {
        return $this->hasMany(StudentAttendanceSubject::class, 'student_attendance_id');
    }

    public function details()
    {
        return $this->hasMany(StudentAttendanceDetail::class, 'student_attendance_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'date',
                'teacher.name',
                'academicYear.name',
                'classroom.name',
            ],
            'with' => ['teacher', 'academicYear', 'classroom', 'subjects', 'details']
        ];
    }
}
