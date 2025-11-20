<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceHistory extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'teacher_attendance_id',
        'changed_by',
        'action',
        'old_values',
        'new_values',
        'notes',
    ];

    protected $casts = [
        'teacher_attendance_id' => 'integer',
        'changed_by' => 'integer',
        'action' => 'string',
        'old_values' => 'array',
        'new_values' => 'array',
        'notes' => 'string',
    ];

    public function teacherAttendance()
    {
        return $this->belongsTo(TeacherAttendance::class, 'teacher_attendance_id');
    }

    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'action',
                'changedByUser.name',
                'teacherAttendance.date',
            ],
            'with' => ['teacherAttendance', 'changedByUser']
        ];
    }
}
