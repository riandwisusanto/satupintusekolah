<?php

namespace App\Models;

use App\Traits\HasApiQueryConfig;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasApiQueryConfig;
    protected $fillable = ['method', 'url', 'ip', 'user_agent', 'payload', 'user_id', 'response_code', 'response_body'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
