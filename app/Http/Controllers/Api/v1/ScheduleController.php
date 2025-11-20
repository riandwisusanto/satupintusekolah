<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\ScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Schedule::query(),
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
            $schedule = Schedule::create($validated);

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
            $schedule->update($validated);
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

    public function getTeacherTodaySchedules(Request $request)
    {
        try {
            $user = Auth::user();
            $today = now()->format('Y-m-d');
            $dayOfWeek = now()->dayOfWeek;
            if ($dayOfWeek == 0) {
                return collect([]);
            }
            $dbDay = $dayOfWeek;

            $schedules = Schedule::with(['teacher', 'subject', 'classroom'])
                ->where('teacher_id', $user->id)
                ->where('day', $dbDay)
                ->select('id', 'teacher_id', 'subject_id', 'class_id', 'day', 'start_time', 'end_time')
                ->get();

            return $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'display_name' => "{$schedule->classroom->name} - {$schedule->subject->name} - " . date('H:i', strtotime($schedule->start_time)) . " s/d " . date('H:i', strtotime($schedule->end_time)) . "",
                    'class_name' => $schedule->classroom->name,
                    'subject_name' => $schedule->subject->name,
                    'start_time' => date('H:i', strtotime($schedule->start_time)),
                    'end_time' => date('H:i', strtotime($schedule->end_time)),
                    'day' => $schedule->day,
                    'teacher_id' => $schedule->teacher_id,
                    'subject_id' => $schedule->subject_id,
                    'class_id' => $schedule->class_id,
                ];
            });
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getTeacherSchedules(Request $request)
    {
        try {
            $user = Auth::user();

            $query = Schedule::with(['teacher', 'subject', 'classroom'])
                ->where('teacher_id', $user->id)
                ->select('id', 'teacher_id', 'subject_id', 'class_id', 'day', 'start_time', 'end_time');

            // Filter by day if provided
            if ($request->has('day')) {
                $query->where('day', $request->day);
            }

            $schedules = $query->get();

            return $schedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'display_name' => "{$schedule->classroom->name} - {$schedule->subject->name} - " . date('H:i', strtotime($schedule->start_time)) . " s/d " . date('H:i', strtotime($schedule->end_time)) . "",
                    'class_name' => $schedule->classroom->name,
                    'subject_name' => $schedule->subject->name,
                    'start_time' => date('H:i', strtotime($schedule->start_time)),
                    'end_time' => date('H:i', strtotime($schedule->end_time)),
                    'day' => $schedule->day,
                    'teacher_id' => $schedule->teacher_id,
                    'subject_id' => $schedule->subject_id,
                    'class_id' => $schedule->class_id,
                ];
            });
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
