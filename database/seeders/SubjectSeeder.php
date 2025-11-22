<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubjectSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Daftar mata pelajaran SD (Sekolah Dasar)
     */
    private $sdSubjects = [
        'Pendidikan Agama Islam',
        'Pendidikan Agama Kristen',
        'Pendidikan Agama Katolik',
        'Pendidikan Agama Hindu',
        'Pendidikan Agama Buddha',
        'Pendidikan Agama Konghucu',
        'Pendidikan Pancasila dan Kewarganegaraan',
        'Bahasa Indonesia',
        'Matematika',
        'Ilmu Pengetahuan Alam',
        'Ilmu Pengetahuan Sosial',
        'Bahasa Inggris',
        'Seni Budaya dan Prakarya',
        'Pendidikan Jasmani, Olahraga, dan Kesehatan',
        'Informatika',
        'Muatan Lokal',
    ];

    /**
     * Daftar mata pelajaran SMP (Sekolah Menengah Pertama)
     */
    private $smpSubjects = [
        'Pendidikan Agama Islam',
        'Pendidikan Agama Kristen',
        'Pendidikan Agama Katolik',
        'Pendidikan Agama Hindu',
        'Pendidikan Agama Buddha',
        'Pendidikan Agama Konghucu',
        'Pendidikan Pancasila dan Kewarganegaraan',
        'Bahasa Indonesia',
        'Matematika',
        'Bahasa Inggris',
        'Ilmu Pengetahuan Alam',
        'Ilmu Pengetahuan Sosial',
        'Seni Budaya',
        'Prakarya',
        'Pendidikan Jasmani, Olahraga, dan Kesehatan',
        'Bahasa Daerah',
        'Informatika',
        'Bahasa Asing',
    ];

    /**
     * Daftar mata pelajaran SMA (Sekolah Menengah Atas) - IPA
     */
    private $smaIpaSubjects = [
        'Pendidikan Agama Islam',
        'Pendidikan Agama Kristen',
        'Pendidikan Agama Katolik',
        'Pendidikan Agama Hindu',
        'Pendidikan Agama Buddha',
        'Pendidikan Agama Konghucu',
        'Pendidikan Pancasila dan Kewarganegaraan',
        'Bahasa Indonesia',
        'Bahasa Inggris',
        'Matematika',
        'Matematika Lanjutan',
        'Fisika',
        'Kimia',
        'Biologi',
        'Sejarah Indonesia',
        'Antropologi',
        'Sosiologi',
        'Geografi',
        'Ekonomi',
        'Seni Budaya',
        'Pendidikan Jasmani, Olahraga, dan Kesehatan',
        'Bahasa Asing',
        'Informatika',
        'Prakarya dan Kewirausahaan',
        'Bimbingan dan Konseling',
    ];

    /**
     * Daftar mata pelajaran SMA (Sekolah Menengah Atas) - IPS
     */
    private $smaIpsSubjects = [
        'Pendidikan Agama Islam',
        'Pendidikan Agama Kristen',
        'Pendidikan Agama Katolik',
        'Pendidikan Agama Hindu',
        'Pendidikan Agama Buddha',
        'Pendidikan Agama Konghucu',
        'Pendidikan Pancasila dan Kewarganegaraan',
        'Bahasa Indonesia',
        'Bahasa Inggris',
        'Matematika',
        'Matematika Terapan',
        'Fisika Terapan',
        'Kimia Terapan',
        'Biologi Terapan',
        'Sejarah Indonesia',
        'Sejarah Dunia',
        'Antropologi',
        'Sosiologi',
        'Geografi',
        'Ekonomi',
        'Seni Budaya',
        'Pendidikan Jasmani, Olahraga, dan Kesehatan',
        'Bahasa Asing',
        'Informatika',
        'Prakarya dan Kewirausahaan',
        'Bimbingan dan Konseling',
    ];

    /**
     * Daftar mata pelajaran umum (untuk semua jenjang)
     */
    private $generalSubjects = [
        'Pendidikan Karakter',
        'Literasi',
        'Numerasi',
        'Kegiatan Ekstrakurikuler',
        'Kegiatan Pramuka',
        'Kegiatan PMR',
        'Kegiatan Rohis',
        'Kegiatan OSIS',
        'Kegiatan Seni',
        'Kegiatan Olahraga',
        'Kegiatan Sains',
        'Kegiatan Bahasa',
        'Kegiatan Teknologi',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara untuk seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data mata pelajaran yang ada (opsional, uncomment jika ingin refresh data)
        // Subject::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Gabungkan semua mata pelajaran
        $allSubjects = array_unique(array_merge(
            $this->sdSubjects,
            $this->smpSubjects,
            $this->smaIpaSubjects,
            $this->smaIpsSubjects,
            $this->generalSubjects
        ));

        $createdCount = 0;

        foreach ($allSubjects as $subjectName) {
            Subject::firstOrCreate(
                ['name' => $subjectName],
                ['active' => true]
            );
            $createdCount++;
        }

        $this->command->info("Berhasil membuat {$createdCount} data mata pelajaran!");
        $this->command->info("Rincian:");
        $this->command->info("- Mata Pelajaran SD: " . count($this->sdSubjects));
        $this->command->info("- Mata Pelajaran SMP: " . count($this->smpSubjects));
        $this->command->info("- Mata Pelajaran SMA IPA: " . count($this->smaIpaSubjects));
        $this->command->info("- Mata Pelajaran SMA IPS: " . count($this->smaIpsSubjects));
        $this->command->info("- Mata Pelajaran Umum/Eskul: " . count($this->generalSubjects));
    }
}
