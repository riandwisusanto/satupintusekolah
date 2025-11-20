<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'teacher_id',
        'academic_year_id',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'teacher_id' => 'integer',
        'active' => 'boolean',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return $this->students()->count() === 0;
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
