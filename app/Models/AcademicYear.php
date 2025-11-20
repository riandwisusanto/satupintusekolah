<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'semester',
        'start_date',
        'end_date',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    public function classes()
    {
        return $this->hasMany(Classroom::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    public function studentAttendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function studentClassHistories()
    {
        return $this->hasMany(StudentClassHistory::class);
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'name',
                'semester',
            ]
        ];
    }
}
