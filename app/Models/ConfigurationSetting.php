<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationSetting extends Model
{
    protected $table = 'configuration_settings';

    protected $primaryKey = 'name';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'value',
    ];
}
