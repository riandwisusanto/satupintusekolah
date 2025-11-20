<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentClassHistory extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'class_id',
        'student_id',
        'academic_year_id',
        'start_date',
        'end_date',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'class_id' => 'integer',
        'student_id' => 'integer',
        'academic_year_id' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return true;
    }

    public function class()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'student.name',
                'class.name',
                'academicYear.name',
            ],
            'with' => ['class', 'student', 'academicYear']
        ];
    }
}
