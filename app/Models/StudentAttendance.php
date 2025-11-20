<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'teacher_id',
        'academic_year_id',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'student_attendance_subjects');
    }

    public function studentAttendanceSubjects()
    {
        return $this->hasMany(StudentAttendanceSubject::class);
    }

    public function studentAttendanceDetails()
    {
        return $this->hasMany(StudentAttendanceDetail::class);
    }
}
