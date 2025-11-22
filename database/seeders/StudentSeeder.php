<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Daftar nama depan Indonesia (laki-laki dan perempuan)
     */
    private $firstNames = [
        // Laki-laki
        'Ahmad',
        'Muhammad',
        'Abdul',
        'Rizki',
        'Budi',
        'Eko',
        'Joko',
        'Agus',
        'Supri',
        'Samsul',
        'Fajar',
        'Riko',
        'Dimas',
        'Rizal',
        'Andi',
        'Fahmi',
        'Hadi',
        'Yudi',
        'Wahyu',
        'Eka',
        'Bayu',
        'Rendra',
        'Gatot',
        'Rudi',
        'Hendra',
        'Joni',
        'Indra',
        'Toni',
        'Doni',
        'Rama',
        'Arif',
        'Bambang',
        'Cahyo',
        'Dedi',
        'Edi',
        'Fikri',
        'Gilang',
        'Hakim',
        'Ibrahim',
        'Jamil',
        // Perempuan
        'Siti',
        'Aisyah',
        'Fatimah',
        'Siti',
        'Nur',
        'Dewi',
        'Ratna',
        'Sari',
        'Maya',
        'Rina',
        'Fitri',
        'Ani',
        'Wati',
        'Susanti',
        'Permata',
        'Melati',
        'Sakura',
        'Cinta',
        'Anggun',
        'Cahaya',
        'Bunga',
        'Mawar',
        'Rose',
        'Lily',
        'Tulip',
        'Sakura',
        'Aurora',
        'Zahra',
        'Khadijah',
        'Aisyah',
        'Maria',
        'Kristina',
        'Yuni',
        'Sinta',
        'Kartika',
        'Puspita',
        'Mawaddah',
        'Warahmah',
        'Jannah',
        'Firdaus'
    ];

    /**
     * Daftar nama tengah/belakang Indonesia
     */
    private $lastNames = [
        'Pratama',
        'Wijaya',
        'Saputra',
        'Hidayat',
        'Fauzi',
        'Rahman',
        'Hakim',
        'Sutrisno',
        'Wibowo',
        'Nugroho',
        'Santoso',
        'Gunawan',
        'Setiawan',
        'Susilo',
        'Purnama',
        'Surya',
        'Budi',
        'Utomo',
        'Putra',
        'Kusuma',
        'Sari',
        'Dewi',
        'Lestari',
        'Pertiwi',
        'Indah',
        'Mulyani',
        'Rahayu',
        'Wulandari',
        'Anggraini',
        'Permata',
        'Cahaya',
        'Mustika',
        'Kirana',
        'Safitri',
        'Amalia',
        'Nabila',
        'Zahira',
        'Sakinah',
        'Adawiyah',
        'Maulida',
        'Rizkia',
        'Fadhilah',
        'Jamilah'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara untuk seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data siswa yang ada (opsional, uncomment jika ingin refresh data)
        // Student::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil semua kelas yang ada
        $classrooms = Classroom::all();

        if ($classrooms->isEmpty()) {
            $this->command->error('Tidak ada kelas yang ditemukan. Jalankan ClassroomSeeder terlebih dahulu.');
            return;
        }

        $totalStudents = 0;

        foreach ($classrooms as $classroom) {
            // Generate jumlah siswa random antara 20-40
            $studentCount = rand(20, 40);

            $this->command->info("Membuat {$studentCount} siswa untuk kelas {$classroom->name}");

            for ($i = 1; $i <= $studentCount; $i++) {
                $student = $this->createStudent($classroom);
                $totalStudents++;
            }
        }

        $this->command->info("Berhasil membuat total {$totalStudents} data siswa untuk {$classrooms->count()} kelas!");
    }

    /**
     * Create a student for a given classroom
     */
    private function createStudent(Classroom $classroom): Student
    {
        // Generate nama Indonesia
        $firstName = $this->firstNames[array_rand($this->firstNames)];
        $lastName = $this->lastNames[array_rand($this->lastNames)];
        $fullName = $firstName . ' ' . $lastName;

        // Tentukan gender berdasarkan nama (logic sederhana)
        $femaleNames = ['Siti', 'Aisyah', 'Fatimah', 'Nur', 'Dewi', 'Ratna', 'Sari', 'Maya', 'Rina', 'Fitri', 'Ani', 'Wati', 'Susanti', 'Permata', 'Melati', 'Sakura', 'Cinta', 'Anggun', 'Cahaya', 'Bunga', 'Mawar', 'Rose', 'Lily', 'Tulip', 'Zahra', 'Khadijah', 'Maria', 'Kristina', 'Yuni', 'Sinta', 'Kartika', 'Puspita', 'Mawaddah', 'Warahmah', 'Jannah', 'Firdaus'];
        $gender = in_array($firstName, $femaleNames) ? 'perempuan' : 'laki-laki';

        // Generate NIS unik
        $nis = $this->generateUniqueNis();

        // Generate nomor telepon Indonesia
        $phone = $this->generateIndonesianPhone();

        return Student::create([
            'name' => $fullName,
            'gender' => $gender,
            'nis' => $nis,
            'phone' => $phone,
            'class_id' => $classroom->id,
            'active' => true,
        ]);
    }

    /**
     * Generate unique NIS (Nomor Induk Siswa)
     */
    private function generateUniqueNis(): string
    {
        do {
            // Format: Tahun masuk + random 4 digit
            $year = date('Y');
            $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $nis = $year . $random;
        } while (Student::where('nis', $nis)->exists());

        return $nis;
    }

    /**
     * Generate Indonesian phone number
     */
    private function generateIndonesianPhone(): ?string
    {
        $prefixes = ['0812', '0813', '0821', '0822', '0823', '0852', '0853', '0856', '0857', '0858', '0811', '0817', '0818', '0819', '0859', '0877', '0878', '0895', '0896', '0897', '0898', '0899'];
        $prefix = $prefixes[array_rand($prefixes)];
        $number = str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT);

        return $prefix . $number;
    }
}
