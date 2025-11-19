<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SyncPermission extends Command
{
    protected $signature = 'permission:sync';
    protected $description = 'Sync permission to database';

    public function handle()
    {
        $this->info('🔄 Syncing permissions...');

        $labelMap = [];

        $permissionList = config('permission');
        $flatPermissions = is_array($permissionList) ? Arr::flatten($permissionList) : [];

        foreach ($flatPermissions as $permissionName) {
            $segments = explode('.', $permissionName);

            $label = collect($segments)->map(function ($segment) use ($labelMap) {
                if (isset($labelMap[$segment])) {
                    return $labelMap[$segment];
                }
                $subSegments = explode('_', $segment);

                return collect($subSegments)
                    ->map(fn($sub) => $labelMap[$sub] ?? Str::ucfirst($sub))
                    ->join('_');
            })->join(' ');

            $permission = \App\Models\Permission::updateOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web', 'label' => $label]
            );

            if ($permission->wasRecentlyCreated) {
                $this->line('🆕 Created: ' . $permissionName . ' → ' . $label);
            } elseif ($permission->wasChanged('label')) {
                $this->line('🔁 Updated: ' . $permissionName . ' → ' . $label);
            }
        }

        // Hapus permission yang tidak ada di config
        $deletedCount = \App\Models\Permission::whereNotIn('name', $flatPermissions)->delete();
        if ($deletedCount) {
            $this->info("🗑️ Deleted $deletedCount permissions not found in config");
        }

        // Assign semua permission ke superadmin
        $superadmin = \App\Models\Role::where('name', 'admin')->first();
        if ($superadmin) {
            $allPermissions = Permission::pluck('id')->toArray();
            $superadmin->syncPermissions($allPermissions);
            $this->info('⭐ All permissions assigned to admin');
        }

        $this->info('✅ Permission sync completed!');
    }
}
