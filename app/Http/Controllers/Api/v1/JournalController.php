<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\JournalRequest;
use App\Models\JournalSubject;
use App\Models\Journal;
use App\Models\Classroom;
use App\Models\Schedule;
use App\Models\AcademicYear;
use App\Models\StudentAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JournalController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Journal::query(),
            Journal::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(JournalRequest $request)
    {
        $validated = $request->validated();
        $subjectIds = $validated['subject_ids'];
        unset($validated['subject_ids']);

        DB::beginTransaction();
        try {
            $journal = Journal::create([
                ...$validated,
                'academic_year_id' => AcademicYear::where('active', true)->first()->id
            ]);

            // Create journal subjects relationships
            foreach ($subjectIds as $subjectId) {
                JournalSubject::create([
                    'journal_id' => $journal->id,
                    'subject_id' => $subjectId
                ]);
            }

            DB::commit();
            return apiResponse('Jurnal guru berhasil dibuat', ['journal' => $journal->load('subjects')]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(JournalRequest $request, $id)
    {
        $validated = $request->validated();
        $subjectIds = $validated['subject_ids'];
        unset($validated['subject_ids']);

        DB::beginTransaction();
        try {
            $journal = Journal::find($id);

            if (!$journal) {
                return apiResponse('Jurnal guru tidak ditemukan', null, 404);
            }

            $journal->update($validated);

            JournalSubject::where('journal_id', $journal->id)->delete();

            foreach ($subjectIds as $subjectId) {
                JournalSubject::create([
                    'journal_id' => $journal->id,
                    'subject_id' => $subjectId
                ]);
            }

            DB::commit();
            return apiResponse('Jurnal guru berhasil diupdate', ['journal' => $journal->load('subjects')]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $journal = Journal::find($id);
            $journal->delete();
            DB::commit();
            return apiResponse('Jurnal guru berhasil dihapus', ['journal' => $journal]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function teacherDashboard(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::now()->format('Y-m-d');
            
            $days = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            
            $dayName = $days[Carbon::now()->format('l')];
            if ($dayName == 'Minggu') {
                return collect([]);
            }
            $currentDay = $dayName;

            // Check if user is homeroom teacher
            $isHomeroomTeacher = false;
            $homeroomClass = null;
            $classroom = Classroom::where('teacher_id', $user->id)
                ->where('active', true)
                ->with('academicYear')
                ->first();

            if ($classroom) {
                $isHomeroomTeacher = true;
                $homeroomClass = $classroom;
            }

            // Get today's schedule
            $todaySchedules = Schedule::where('teacher_id', $user->id)
                ->where('day', $currentDay)
                ->with(['subject', 'classroom'])
                ->orderBy('start_time')
                ->get()
                ->map(function ($schedule) use ($user, $today) {
                    // Check attendance
                    $isAttendanceFilled = StudentAttendance::where('teacher_id', $user->id)
                        ->where('class_id', $schedule->class_id)
                        ->where('date', $today)
                        ->whereHas('subjects', function ($query) use ($schedule) {
                            $query->where('subject_id', $schedule->subject_id);
                        })
                        ->exists();

                    // Check journal
                    $isJournalFilled = Journal::where('teacher_id', $user->id)
                        ->where('class_id', $schedule->class_id)
                        ->where('date', $today)
                        ->whereHas('subjects', function ($query) use ($schedule) {
                            $query->where('subject_id', $schedule->subject_id);
                        })
                        ->exists();

                    $schedule->is_attendance_filled = $isAttendanceFilled;
                    $schedule->is_journal_filled = $isJournalFilled;
                    
                    return $schedule;
                });

            // Get available subjects for today (for multi-subject journal)
            $todaySubjects = $todaySchedules->map(function ($schedule) {
                return [
                    'id' => $schedule->subject->id,
                    'name' => $schedule->subject->name,
                    'class_id' => $schedule->class_id,
                    'class_name' => $schedule->classroom->name,
                    'time' => $schedule->start_time . ' - ' . $schedule->end_time
                ];
            })->unique('id')->values();

            // Get today's journals
            $todayJournals = Journal::where('teacher_id', $user->id)
                ->where('date', $today)
                ->with(['subjects.subject', 'classroom'])
                ->get();

            // Check if teacher already submitted journal today
            $hasSubmittedToday = $todayJournals->count() > 0;

            // Get pending journals (last 7 days + today)
            $pendingDays = [];
            for ($i = 0; $i <= 7; $i++) {
                $date = Carbon::now()->subDays($i);
                $dayName = $days[$date->format('l')];
                
                if ($dayName == 'Minggu') continue; // Skip Sunday

                // Get schedules for this day
                $schedules = Schedule::where('teacher_id', $user->id)
                    ->where('day', $dayName)
                    ->get();

                if ($schedules->count() > 0) {
                    // Get journals for this day
                    $journals = Journal::where('teacher_id', $user->id)
                        ->where('date', $date->format('Y-m-d'))
                        ->with('subjects')
                        ->get();

                    // Check if all schedules are covered
                    $allCovered = true;
                    foreach ($schedules as $schedule) {
                        $isCovered = $journals->contains(function ($journal) use ($schedule) {
                            return $journal->class_id == $schedule->class_id &&
                                   $journal->subjects->contains('subject_id', $schedule->subject_id);
                        });

                        if (!$isCovered) {
                            $allCovered = false;
                            break;
                        }
                    }

                    if (!$allCovered) {
                        $pendingDays[] = [
                            'date' => $date->format('Y-m-d'),
                            'day_name' => $dayName,
                            'day_date' => $date->format('d/m')
                        ];
                    }
                }
            }

            // Get stats
            $thisMonthJournals = Journal::where('teacher_id', $user->id)
                ->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year)
                ->count();

            $workingDaysThisMonth = Schedule::where('teacher_id', $user->id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->distinct('day')
                ->count();

            return apiResponse('Dashboard data retrieved successfully', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_homeroom_teacher' => $isHomeroomTeacher,
                    'homeroom_class' => $homeroomClass
                ],
                'today_schedule' => $todaySchedules,
                'today_subjects' => $todaySubjects,
                'today_journals' => $todayJournals,
                'has_submitted_today' => $hasSubmittedToday,
                'pending_days' => $pendingDays,
                'stats' => [
                    'this_month_journals' => $thisMonthJournals,
                    'pending_journals' => count($pendingDays) - $thisMonthJournals,
                    'working_days_this_month' => $workingDaysThisMonth,
                    'completion_rate' => $workingDaysThisMonth > 0 ? round((count($pendingDays) - $thisMonthJournals) / count($pendingDays) * 100, 1) : 0
                ]
            ]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function todaySubjects(Request $request)
    {
        try {
            $user = $request->user();
            $days = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            
            $dayName = $days[Carbon::now()->format('l')];
            if ($dayName == 'Minggu') {
                return collect([]);
            }
            $currentDay = $dayName;

            $todaySubjects = Schedule::where('teacher_id', $user->id)
                ->where('day', $currentDay)
                ->with(['subject', 'classroom'])
                ->orderBy('start_time')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->subject->id,
                        'name' => $schedule->subject->name,
                        'class_id' => $schedule->class_id,
                        'class_name' => $schedule->classroom->name
                    ];
                })->unique('id')->values();

            return apiResponse('Today subjects retrieved successfully', ['subjects' => $todaySubjects]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
    public function getJournalFormData(Request $request)
    {
        try {
            $user = $request->user();
            $days = [
                'Sunday' => 'Minggu',
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu'
            ];
            
            $dayName = $days[Carbon::now()->format('l')];
            if ($dayName == 'Minggu') {
                return collect([]);
            }
            $currentDay = $dayName;
            $date = Carbon::now()->format('Y-m-d');
            $classId = $request->get('class_id');

            // Get today's classes from schedule
            $todayClasses = Schedule::where('teacher_id', $user->id)
                ->where('day', $currentDay)
                ->with('classroom')
                ->distinct('class_id')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'value' => $schedule->classroom->id,
                        'label' => $schedule->classroom->name,
                        'id' => $schedule->classroom->id,
                        'name' => $schedule->classroom->name,
                    ];
                })->unique('value')->values();

            $subjects = [];
            if ($classId) {
                // Get subjects for the selected class and date
                $subjects = Schedule::where('teacher_id', $user->id)
                    ->where('class_id', $classId)
                    ->where('day', $currentDay)
                    ->with('subject')
                    ->get()
                    ->map(function ($schedule) use ($user, $date, $classId) {
                        // Check if journal already exists for this subject
                        $isFilled = Journal::where('teacher_id', $user->id)
                            ->where('date', $date)
                            ->whereHas('subjects', function ($query) use ($schedule) {
                                $query->where('subject_id', $schedule->subject_id);
                            })
                            ->exists();

                        return [
                            'id' => $schedule->subject->id,
                            'name' => $schedule->subject->name,
                            'value' => $schedule->subject->id,
                            'label' => $schedule->subject->name,
                            'time' => $schedule->start_time . ' - ' . $schedule->end_time,
                            'is_filled' => $isFilled
                        ];
                    })->unique('value')->values();
            }

            return apiResponse('Data form jurnal', [
                'classes' => $todayClasses,
                'subjects' => $subjects
            ]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
