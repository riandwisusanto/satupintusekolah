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

        $labelMap = [
            'configuration' => 'Konfigurasi',
            'branch' => 'Cabang',
            'employee' => 'Karyawan',
            'customer' => 'Pelanggan',
            'worker' => 'Kuli',
            'vehicle' => 'Armada',
            'user' => 'Pengguna',
            'courier' => 'Kurir',
            'type_of_fee' => 'Jenis_Biaya',
            'master_price' => 'Master_Harga',
            'loading_list' => 'Daftar_Muat',
            'view' => 'Lihat',
            'create' => 'Tambah',
            'update' => 'Ubah',
            'delete' => 'Hapus',
            'qty' => 'Jumlah',
            'dm' => 'Daftar_Muat',
            'sj' => 'Surat_Jalan',
            'in' => 'Masuk',
            'document' => 'Dokumen',
            'shipment' => 'Pengiriman',
            'operational_fee' => 'Biaya_Operasional',
            'delivery' => 'Penerimaan',
            'receive' => 'Penerimaan',
            'finance' => 'Keuangan',
            'payment' => 'Pembayaran',
            'billing' => 'Tagihan',
            'warehouse' => 'Gudang',
            'handover' => 'Penitipan',
            'payroll' => 'Gaji',
            'report' => 'Laporan'
        ];

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
        $superadmin = \App\Models\Role::where('name', 'superadmin')->first();
        if ($superadmin) {
            $allPermissions = Permission::pluck('id')->toArray();
            $superadmin->syncPermissions($allPermissions);
            $this->info('⭐ All permissions assigned to superadmin');
        }

        $this->info('✅ Permission sync completed!');
    }
}
