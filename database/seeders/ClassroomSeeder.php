<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassroomSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara untuk seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data kelas yang ada (opsional, uncomment jika ingin refresh data)
        // Classroom::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil atau buat academic year aktif
        $academicYear = AcademicYear::firstOrCreate(
            [
                'name' => '2024/2025',
                'semester' => 1
            ],
            [
                'start_date' => '2024-07-01',
                'end_date' => '2025-06-30',
                'active' => true
            ]
        );

        // Ambil teacher pertama atau buat jika tidak ada
        $teacher = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->first();

        if (!$teacher) {
            $teacher = User::factory()->create([
                'name' => 'Guru Contoh',
                'email' => 'teacher@example.com',
                'password' => bcrypt('password'),
                'role_id' => \App\Models\Role::where('name', 'teacher')->first()->id
            ]);
        }

        // Data kelas untuk SD (Sekolah Dasar)
        $sdClasses = [
            'Kelas 1 A',
            'Kelas 1 B',
            'Kelas 1 C',
            'Kelas 2 A',
            'Kelas 2 B',
            'Kelas 2 C',
            'Kelas 3 A',
            'Kelas 3 B',
            'Kelas 3 C',
            'Kelas 4 A',
            'Kelas 4 B',
            'Kelas 4 C',
            'Kelas 5 A',
            'Kelas 5 B',
            'Kelas 5 C',
            'Kelas 6 A',
            'Kelas 6 B',
            'Kelas 6 C',
        ];

        // Data kelas untuk SMP (Sekolah Menengah Pertama)
        $smpClasses = [
            'Kelas VII A',
            'Kelas VII B',
            'Kelas VII C',
            'Kelas VIII A',
            'Kelas VIII B',
            'Kelas VIII C',
            'Kelas IX A',
            'Kelas IX B',
            'Kelas IX C',
        ];

        // Data kelas untuk SMA (Sekolah Menengah Atas)
        $smaClasses = [
            // Kelas X
            'Kelas X IPA 1',
            'Kelas X IPA 2',
            'Kelas X IPA 3',
            'Kelas X IPS 1',
            'Kelas X IPS 2',
            'Kelas X IPS 3',

            // Kelas XI
            'Kelas XI IPA 1',
            'Kelas XI IPA 2',
            'Kelas XI IPA 3',
            'Kelas XI IPS 1',
            'Kelas XI IPS 2',
            'Kelas XI IPS 3',

            // Kelas XII
            'Kelas XII IPA 1',
            'Kelas XII IPA 2',
            'Kelas XII IPA 3',
            'Kelas XII IPS 1',
            'Kelas XII IPS 2',
            'Kelas XII IPS 3',
        ];

        // Gabungkan semua kelas
        $allClasses = array_merge($sdClasses, $smpClasses, $smaClasses);

        // Buat kelas dengan data yang telah ditentukan
        foreach ($allClasses as $className) {
            Classroom::firstOrCreate(
                ['name' => $className],
                [
                    'teacher_id' => $teacher->id,
                    'academic_year_id' => $academicYear->id,
                    'active' => true,
                ]
            );
        }

        $this->command->info('Berhasil membuat ' . count($allClasses) . ' data kelas!');
    }
}
