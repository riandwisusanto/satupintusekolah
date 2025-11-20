<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalSubject extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'teacher_journal_id',
        'subject_id',
    ];

    protected $casts = [
        'teacher_journal_id' => 'integer',
        'subject_id' => 'integer',
    ];

    protected $appends = ['editable', 'deleteable'];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return true;
    }

    public function teacherJournal()
    {
        return $this->belongsTo(TeacherJournal::class, 'teacher_journal_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public static function apiQueryConfig(): array
    {
        return [
            'searchable' => [
                'subject.name',
            ],
            'with' => ['subject']
        ];
    }
}
