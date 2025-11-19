<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use Illuminate\Database\Eloquent\Model;

class ModelLog extends Model
{
    use HasApiQueryConfig;
    protected $fillable = ['model_type', 'model_id', 'event', 'changes', 'user_id'];
    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function model()
    {
        return $this->morphTo();
    }
}
