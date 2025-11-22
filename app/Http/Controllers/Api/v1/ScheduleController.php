<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\ScheduleRequest;
use App\Models\AcademicYear;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Schedule::when(!auth()->user()->isAdmin(), function ($query) {
                $query->where('teacher_id', auth()->user()->id);
            }),
            Schedule::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(ScheduleRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $schedule = Schedule::create([
                ...$validated,
                'academic_year_id' => AcademicYear::where('active', true)->first()->id
            ]);

            DB::commit();
            return apiResponse('Jadwal berhasil dibuat', ['schedule' => $schedule]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(ScheduleRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $schedule = Schedule::find($id);
            $schedule->update([
                ...$validated,
                'academic_year_id' => AcademicYear::where('active', true)->first()->id
            ]);
            DB::commit();
            return apiResponse('Jadwal berhasil diupdate', ['schedule' => $schedule]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $schedule = Schedule::find($id);
            $schedule->delete();
            DB::commit();
            return apiResponse('Jadwal berhasil dihapus', ['schedule' => $schedule]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function today(Request $request)
    {
        $dayOfWeek = now()->dayOfWeek;
        if ($dayOfWeek == 0) {
            return collect([]);
        }

        $date = $request->get('date', now()->format('Y-m-d'));
        $dbDay = $dayOfWeek;

        $query = Schedule::when(!auth()->user()->isAdmin(), function ($query) {
            $query->where('teacher_id', auth()->user()->id);
        })->where('day', $dbDay);

        $query->whereNotIn('id', function ($subQuery) use ($date) {
            $subQuery->select('schedules.id')
                ->from('schedules')
                ->join('student_attendance_subjects', 'schedules.subject_id', '=', 'student_attendance_subjects.subject_id')
                ->join('student_attendances', 'student_attendance_subjects.student_attendance_id', '=', 'student_attendances.id')
                ->where('student_attendances.date', $date);
        });

        $datas = ApiQueryHelper::apply($query, Schedule::apiQueryConfig());

        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
