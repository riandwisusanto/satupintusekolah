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
            $dayOfWeek = now()->dayOfWeek;
            if ($dayOfWeek == 0) {
                return collect([]);
            }
            $currentDay = $dayOfWeek;

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
                ->get();

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

            // Get pending journals (last 7 days without submission)
            $pendingDays = [];
            for ($i = 1; $i <= 7; $i++) {
                $date = Carbon::now()->subDays($i);
                $daySchedule = Schedule::where('teacher_id', $user->id)
                    ->where('day', strtolower($date->format('l')))
                    ->count();

                if ($daySchedule > 0) {
                    $journalCheck = Journal::where('teacher_id', $user->id)
                        ->where('date', $date->format('Y-m-d'))
                        ->count();

                    if ($journalCheck === 0) {
                        $pendingDays[] = [
                            'date' => $date->format('Y-m-d'),
                            'day_name' => $date->format('l'),
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
                    'working_days_this_month' => $workingDaysThisMonth,
                    'completion_rate' => $workingDaysThisMonth > 0 ? round(($thisMonthJournals / $workingDaysThisMonth) * 100, 1) : 0
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
            $dayOfWeek = now()->dayOfWeek;
            if ($dayOfWeek == 0) {
                return collect([]);
            }
            $currentDay = $dayOfWeek;

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
}
