<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendanceDetail extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'student_attendance_id',
        'student_id',
        'status',
        'note',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'student_attendance_id' => 'integer',
        'student_id' => 'integer',
        'status' => 'string',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return true;
    }

    public function studentAttendance()
    {
        return $this->belongsTo(StudentAttendance::class, 'student_attendance_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
