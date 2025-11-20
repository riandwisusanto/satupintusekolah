<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'academic_year_id',
        'date',
        'theme',
        'activity',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function class()
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'journal_subjects');
    }

    public function journalSubjects()
    {
        return $this->hasMany(JournalSubject::class);
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'theme',
                'activity',
                'date',
                'teacher.name',
                'class.name',
            ],
            'with' => ['teacher', 'class', 'academicYear', 'subjects']
        ];
    }
}
