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
        'subject_id',
        'class_id',
        'academic_year_id',
        'day',
        'start_time',
        'end_time',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'teacher_id' => 'integer',
        'subject_id' => 'integer',
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

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
