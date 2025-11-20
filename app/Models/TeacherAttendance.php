<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'date',
        'time_in',
        'time_out',
        'photo_in',
        'photo_out',
    ];

    protected $casts = [
        'date' => 'date',
        'time_in' => 'datetime:H:i',
        'time_out' => 'datetime:H:i',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'date',
                'teacher.name',
            ],
            'with' => ['teacher']
        ];
    }
}
