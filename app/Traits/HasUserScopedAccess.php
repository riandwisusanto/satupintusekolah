<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasUserScopedAccess
{
    public function scopeVisibleForUser(Builder $query, $user = null): Builder
    {
        $user = $user ?: Auth::user();

        if (!$user) {
            return $query->whereRaw('0 = 1'); // deny all if unauthenticated
        }

        if (method_exists($user, 'hasShowAllData') && $user->hasShowAllData()) {
            return $query;
        }

        $path = property_exists($this, 'userRelationPath')
            ? static::$userRelationPath
            : 'employes.users';

        return $query->whereRelation(
            $path,
            'users.id',
            $user->id
        );
    }
}
