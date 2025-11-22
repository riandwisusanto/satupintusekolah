<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateClassroomTeachersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua guru dengan role teacher
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        if ($teachers->isEmpty()) {
            $this->command->error('Tidak ada data guru yang ditemukan. Jalankan TeacherSeeder terlebih dahulu.');
            return;
        }

        // Ambil semua kelas yang ada
        $classrooms = Classroom::all();

        if ($classrooms->isEmpty()) {
            $this->command->error('Tidak ada data kelas yang ditemukan. Jalankan ClassroomSeeder terlebih dahulu.');
            return;
        }

        $updatedCount = 0;

        foreach ($classrooms as $classroom) {
            // Pilih guru random
            $randomTeacher = $teachers->random();

            // Update teacher_id untuk kelas ini
            $classroom->update([
                'teacher_id' => $randomTeacher->id
            ]);

            $updatedCount++;

            $this->command->info("Kelas {$classroom->name} diupdate wali kelas: {$randomTeacher->name}");
        }

        $this->command->info("Berhasil mengupdate wali kelas untuk {$updatedCount} kelas!");

        // Tampilkan rincian distribusi guru
        $this->command->info("\nDistribusi Wali Kelas:");

        $teacherDistribution = Classroom::join('users', 'classes.teacher_id', '=', 'users.id')
            ->select('users.name', DB::raw('count(*) as jumlah_kelas'))
            ->groupBy('users.id', 'users.name')
            ->orderBy('users.name')
            ->get();

        foreach ($teacherDistribution as $distribution) {
            $this->command->info("- {$distribution->name}: {$distribution->jumlah_kelas} kelas");
        }

        // Verifikasi tidak ada kelas tanpa wali kelas
        $classesWithoutTeacher = Classroom::whereNull('teacher_id')->count();

        if ($classesWithoutTeacher > 0) {
            $this->command->warn("Peringatan: Masih ada {$classesWithoutTeacher} kelas tanpa wali kelas");
        } else {
            $this->command->info("✓ Semua kelas sudah memiliki wali kelas");
        }
    }
}
