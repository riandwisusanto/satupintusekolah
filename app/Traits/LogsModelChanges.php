<?php

namespace App\Traits;

use App\Models\ModelLog;
use Illuminate\Support\Facades\Auth;

trait LogsModelChanges
{
    public static function bootLogsModelChanges()
    {
        static::created(function ($model) {
            ModelLog::create([
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'event' => 'created',
                'user_id' => Auth::id(),
            ]);
        });

        static::updated(function ($model) {
            ModelLog::create([
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'event' => 'updated',
                'changes' => [
                    'before' => $model->getOriginal(),
                    'after' => $model->getChanges(),
                ],
                'user_id' => Auth::id(),
            ]);
        });

        static::deleted(function ($model) {
            ModelLog::create([
                'model_type' => get_class($model),
                'model_id' => $model->id,
                'event' => 'deleted',
                'user_id' => Auth::id(),
            ]);
        });
    }
}
