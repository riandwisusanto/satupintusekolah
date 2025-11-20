<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'teacher_id',
        'date',
        'time_in',
        'time_out',
        'photo_in',
        'photo_out',
        'status',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'teacher_id' => 'integer',
        'date' => 'date',
        'photo_in' => 'string',
        'photo_out' => 'string',
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

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function histories()
    {
        return $this->hasMany(AttendanceHistory::class, 'teacher_attendance_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'check_in' => '<span class="badge bg-success">Masuk</span>',
            'check_out' => '<span class="badge bg-danger">Pulang</span>',
            'sick' => '<span class="badge bg-warning">Sakit</span>',
            'permission' => '<span class="badge bg-info">Izin</span>',
            'on_leave' => '<span class="badge bg-secondary">Cuti</span>',
            default => '<span class="badge bg-secondary">Unknown</span>'
        };
    }

    public function getWorkDurationAttribute(): string
    {
        if ($this->time_in && $this->time_out) {
            $start = \Carbon\Carbon::parse($this->time_in);
            $end = \Carbon\Carbon::parse($this->time_out);
            return $start->diff($end)->format('%H:%I');
        }
        return '-';
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'date',
                'teacher.name',
                'status',
            ],
            'with' => ['teacher', 'histories']
        ];
    }
}
