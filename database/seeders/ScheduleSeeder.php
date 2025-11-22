<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Hari-hari sekolah
     */
    private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    /**
     * Jam pelajaran
     */
    private $timeSlots = [
        ['start' => '07:00', 'end' => '07:45'],  // Jam 1
        ['start' => '07:45', 'end' => '08:30'],  // Jam 2
        ['start' => '08:30', 'end' => '09:15'],  // Jam 3
        ['start' => '09:30', 'end' => '10:15'],  // Jam 4 (istirahat 09:15-09:30)
        ['start' => '10:15', 'end' => '11:00'],  // Jam 5
        ['start' => '11:00', 'end' => '11:45'],  // Jam 6
        ['start' => '12:30', 'end' => '13:15'],  // Jam 7 (istirahat 11:45-12:30)
        ['start' => '13:15', 'end' => '14:00'],  // Jam 8
        ['start' => '14:00', 'end' => '14:45'],  // Jam 9
    ];

    /**
     * Mata pelajaran wajib per hari
     */
    private $coreSubjects = [
        'Bahasa Indonesia',
        'Matematika',
        'Bahasa Inggris',
        'Pendidikan Pancasila dan Kewarganegaraan',
    ];

    /**
     * Mata pelajaran IPA
     */
    private $ipaSubjects = [
        'Fisika',
        'Kimia',
        'Biologi',
        'Matematika Lanjutan'
    ];

    /**
     * Mata pelajaran IPS
     */
    private $ipsSubjects = [
        'Sejarah Indonesia',
        'Geografi',
        'Ekonomi',
        'Sosiologi'
    ];

    /**
     * Mata pelajaran umum
     */
    private $generalSubjects = [
        'Seni Budaya',
        'Pendidikan Jasmani, Olahraga, dan Kesehatan',
        'Informatika',
        'Prakarya',
        'Pendidikan Agama Islam'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks sementara untuk seeder
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus data jadwal yang ada (opsional, uncomment jika ingin refresh data)
        // Schedule::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Ambil data yang dibutuhkan
        $academicYear = AcademicYear::where('active', true)->first();
        $classrooms = Classroom::with('teacher')->get();
        $subjects = Subject::where('active', true)->get();
        $teachers = User::whereHas('role', function ($query) {
            $query->where('name', 'teacher');
        })->get();

        if ($academicYear === null) {
            $this->command->error('Tidak ada academic year aktif yang ditemukan.');
            return;
        }

        if ($classrooms->isEmpty()) {
            $this->command->error('Tidak ada kelas yang ditemukan. Jalankan ClassroomSeeder terlebih dahulu.');
            return;
        }

        if ($subjects->isEmpty()) {
            $this->command->error('Tidak ada mata pelajaran yang ditemukan. Jalankan SubjectSeeder terlebih dahulu.');
            return;
        }

        if ($teachers->isEmpty()) {
            $this->command->error('Tidak ada guru yang ditemukan. Jalankan TeacherSeeder terlebih dahulu.');
            return;
        }

        $totalSchedules = 0;

        foreach ($classrooms as $classroom) {
            $this->command->info("Membuat jadwal untuk kelas {$classroom->name}");

            foreach ($this->days as $dayIndex => $day) {
                $daySchedules = $this->generateDaySchedule(
                    $classroom,
                    $day,
                    $academicYear->id,
                    $subjects,
                    $teachers
                );

                $totalSchedules += count($daySchedules);
            }
        }

        $this->command->info("Berhasil membuat {$totalSchedules} jadwal pelajaran untuk {$classrooms->count()} kelas!");
        $this->command->info("Rata-rata " . round($totalSchedules / $classrooms->count()) . " jadwal per kelas");
    }

    /**
     * Generate jadwal untuk satu hari
     */
    private function generateDaySchedule($classroom, $day, $academicYearId, $subjects, $teachers)
    {
        $schedules = [];
        $usedSubjects = [];
        $usedTimeSlots = [];

        // Tentukan mata pelajaran berdasarkan jenis kelas
        $availableSubjects = $this->getSubjectsForClass($classroom->name, $subjects);

        // Generate 6-8 jam pelajaran per hari
        $maxSubjects = rand(6, 8);

        foreach ($this->timeSlots as $slotIndex => $timeSlot) {
            if (count($schedules) >= $maxSubjects) {
                break;
            }

            // Skip beberapa slot untuk istirahat atau kegiatan lain
            if ($this->shouldSkipSlot($day, $slotIndex)) {
                continue;
            }

            // Pilih mata pelajaran random
            $subject = $this->selectRandomSubject($availableSubjects, $usedSubjects);

            if ($subject) {
                // Pilih guru yang sesuai (atau random jika tidak ada keahlian spesifik)
                $teacher = $this->selectTeacherForSubject($subject, $teachers, $classroom);

                $schedule = Schedule::create([
                    'teacher_id' => $teacher->id,
                    'subject_id' => $subject->id,
                    'class_id' => $classroom->id,
                    'academic_year_id' => $academicYearId,
                    'day' => $day,
                    'start_time' => $timeSlot['start'],
                    'end_time' => $timeSlot['end'],
                ]);

                $schedules[] = $schedule;
                $usedSubjects[] = $subject->id;
                $usedTimeSlots[] = $slotIndex;

                $this->command->info("  {$day} {$timeSlot['start']}-{$timeSlot['end']}: {$subject->name} ({$teacher->name})");
            }
        }

        return $schedules;
    }

    /**
     * Tentukan mata pelajaran berdasarkan jenis kelas
     */
    private function getSubjectsForClass($className, $subjects)
    {
        $classSubjects = collect([]);

        // Mata pelajaran wajib untuk semua kelas
        foreach ($this->coreSubjects as $subjectName) {
            $subject = $subjects->firstWhere('name', $subjectName);
            if ($subject) {
                $classSubjects->push($subject);
            }
        }

        // Mata pelajaran berdasarkan jenjang
        if (preg_match('/Kelas (X|XI|XII)/', $className)) {
            // SMA
            if (strpos($className, 'IPA') !== false) {
                // Kelas IPA
                foreach ($this->ipaSubjects as $subjectName) {
                    $subject = $subjects->firstWhere('name', $subjectName);
                    if ($subject) {
                        $classSubjects->push($subject);
                    }
                }
            } elseif (strpos($className, 'IPS') !== false) {
                // Kelas IPS
                foreach ($this->ipsSubjects as $subjectName) {
                    $subject = $subjects->firstWhere('name', $subjectName);
                    if ($subject) {
                        $classSubjects->push($subject);
                    }
                }
            }
        } elseif (preg_match('/Kelas VII|VIII|IX/', $className)) {
            // SMP
            $smpSubjects = ['Ilmu Pengetahuan Alam', 'Ilmu Pengetahuan Sosial'];
            foreach ($smpSubjects as $subjectName) {
                $subject = $subjects->firstWhere('name', $subjectName);
                if ($subject) {
                    $classSubjects->push($subject);
                }
            }
        } else {
            // SD
            $sdSubjects = ['Ilmu Pengetahuan Alam', 'Ilmu Pengetahuan Sosial'];
            foreach ($sdSubjects as $subjectName) {
                $subject = $subjects->firstWhere('name', $subjectName);
                if ($subject) {
                    $classSubjects->push($subject);
                }
            }
        }

        // Tambahkan mata pelajaran umum
        foreach ($this->generalSubjects as $subjectName) {
            $subject = $subjects->firstWhere('name', $subjectName);
            if ($subject) {
                $classSubjects->push($subject);
            }
        }

        return $classSubjects;
    }

    /**
     * Pilih mata pelajaran random
     */
    private function selectRandomSubject($availableSubjects, $usedSubjects)
    {
        $availableSubjects = $availableSubjects->filter(function ($subject) use ($usedSubjects) {
            return !in_array($subject->id, $usedSubjects);
        });

        if ($availableSubjects->isEmpty()) {
            return null;
        }

        return $availableSubjects->random();
    }

    /**
     * Pilih guru untuk mata pelajaran
     */
    private function selectTeacherForSubject($subject, $teachers, $classroom)
    {
        // Prioritaskan wali kelas
        if ($classroom->teacher) {
            return $classroom->teacher;
        }

        // Random guru jika tidak ada prioritas
        return $teachers->random();
    }

    /**
     * Tentukan apakah slot waktu harus dilewati
     */
    private function shouldSkipSlot($day, $slotIndex)
    {
        // Skip slot untuk istirahat (sudah dihandle dengan timeSlots)
        if ($slotIndex === 3) {
            return true; // Istirahat pertama
        }

        if ($slotIndex === 6) {
            return true; // Istirahat kedua
        }

        // Sabtu lebih sedikit jam pelajaran
        if ($day === 'Sabtu' && $slotIndex > 5) {
            return true;
        }

        return false;
    }
}
