<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Daftar nama guru Indonesia
     */
    private $teacherNames = [
        [
            'name' => 'Dr. Ahmad Suryanto, S.Pd., M.Pd.',
            'email' => 'ahmad.suryanto@sekolah.sch.id',
            'phone' => '081234567001',
            'nip' => '198001012001121001',
            'expertise' => 'Matematika'
        ],
        [
            'name' => 'Dra. Siti Nurhaliza, M.Pd.',
            'email' => 'siti.nurhaliza@sekolah.sch.id',
            'phone' => '081234567002',
            'nip' => '198002022001122002',
            'expertise' => 'Bahasa Indonesia'
        ],
        [
            'name' => 'Bambang Susilo, S.Pd.',
            'email' => 'bambang.susilo@sekolah.sch.id',
            'phone' => '081234567003',
            'nip' => '198503152005041003',
            'expertise' => 'Fisika'
        ],
        [
            'name' => 'Dewi Lestari, S.Si.',
            'email' => 'dewi.lestari@sekolah.sch.id',
            'phone' => '081234567004',
            'nip' => '198704122008042004',
            'expertise' => 'Kimia'
        ],
        [
            'name' => 'Muhammad Rizki, S.Pd.',
            'email' => 'muhammad.rizki@sekolah.sch.id',
            'phone' => '081234567005',
            'nip' => '199001152010011005',
            'expertise' => 'Bahasa Inggris'
        ],
        [
            'name' => 'Ratna Sari, S.Pd.',
            'email' => 'ratna.sari@sekolah.sch.id',
            'phone' => '081234567006',
            'nip' => '199105252011012006',
            'expertise' => 'Biologi'
        ],
        [
            'name' => 'Agus Setiawan, S.Kom.',
            'email' => 'agus.setiawan@sekolah.sch.id',
            'phone' => '081234567007',
            'nip' => '199206152013011007',
            'expertise' => 'Informatika'
        ],
        [
            'name' => 'Maya Anggraini, S.Pd.',
            'email' => 'maya.anggraini@sekolah.sch.id',
            'phone' => '081234567008',
            'nip' => '199307202014022008',
            'expertise' => 'Pendidikan Jasmani'
        ],
        [
            'name' => 'Hadi Wijaya, S.E., M.M.',
            'email' => 'hadi.wijaya@sekolah.sch.id',
            'phone' => '081234567009',
            'nip' => '198808102009011009',
            'expertise' => 'Ekonomi'
        ],
        [
            'name' => 'Fitri Handayani, S.Pd.',
            'email' => 'fitri.handayani@sekolah.sch.id',
            'phone' => '081234567010',
            'nip' => '199401152015012010',
            'expertise' => 'Sejarah'
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara untuk seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data guru yang ada (opsional, uncomment jika ingin refresh data)
        // User::where('role_id', Role::where('name', 'teacher')->first()->id)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil role teacher
        $teacherRole = Role::where('name', 'teacher')->first();

        if (!$teacherRole) {
            $this->command->error('Role teacher tidak ditemukan. Jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        $createdCount = 0;

        foreach ($this->teacherNames as $teacherData) {
            // Cek apakah user dengan email sudah ada
            $existingUser = User::where('email', $teacherData['email'])->first();

            if (!$existingUser) {
                User::create([
                    'name' => $teacherData['name'],
                    'email' => $teacherData['email'],
                    'nip' => $teacherData['nip'],
                    'phone' => $teacherData['phone'],
                    'password' => Hash::make('password'), // Password default
                    'role_id' => $teacherRole->id,
                    'active' => true,
                    'email_verified_at' => now(),
                    'remember_token' => Str::random(10),
                ]);

                $createdCount++;
                $this->command->info("Guru dibuat: {$teacherData['name']} ({$teacherData['expertise']})");
            } else {
                $this->command->warn("Guru sudah ada: {$teacherData['name']} ({$teacherData['expertise']})");
            }
        }

        $this->command->info("Berhasil membuat {$createdCount} data guru!");

        // Tampilkan informasi login
        $this->command->info("\nInformasi Login Guru:");
        $this->command->info("Email: sesuai dengan email guru di atas");
        $this->command->info("Password: password");
        $this->command->info("\nDaftar Guru dan Keahlian:");

        foreach ($this->teacherNames as $teacherData) {
            $this->command->info("- {$teacherData['name']} ({$teacherData['expertise']})");
        }
    }
}
