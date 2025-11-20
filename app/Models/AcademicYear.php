<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'name',
        'semester',
        'start_date',
        'end_date',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'semester' => 'integer',
        'active' => 'boolean',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        // TODO: Add logic to check if academic year can be deleted
        // For example, check if there are no related records
        return true;
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'academic_year_id');
    }

    /**
     * Set this academic year as active and deactivate others
     */
    public function setActive()
    {
        // Deactivate all other academic years
        self::where('id', '!=', $this->id)->update(['active' => false]);

        // Activate this academic year
        $this->update(['active' => true]);

        // Update all active classrooms to use this academic year
        Classroom::where('active', true)->update(['academic_year_id' => $this->id]);
    }
}
