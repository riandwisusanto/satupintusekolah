<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use App\Traits\HasPermissions;
use App\Traits\LogsModelChanges;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory, HasPermissions, LogsModelChanges, HasApiQueryConfig;

    protected $fillable = [
        'name',
        'active',
    ];

    protected $appends = ['editable', 'deleteable'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function getEditableAttribute(): bool
    {
        return true;
    }

    public function getDeleteableAttribute(): bool
    {
        return $this->schedules()->count() === 0;
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
