<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendanceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_attendance_id',
        'student_id',
        'status',
        'note',
    ];

    public function studentAttendance()
    {
        return $this->belongsTo(StudentAttendance::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
