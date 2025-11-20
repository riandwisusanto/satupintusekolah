<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherAttendance\TeacherAttendanceRequest;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAttendanceController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            TeacherAttendance::query(),
            TeacherAttendance::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(TeacherAttendanceRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $attendance = TeacherAttendance::create($validated);

            DB::commit();
            return apiResponse('Absensi guru berhasil dibuat', ['teacher_attendance' => $attendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $attendance = TeacherAttendance::with(['teacher'])->find($id);
            if (!$attendance) {
                return apiResponse('Absensi guru tidak ditemukan', null, 404);
            }
            return $attendance;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(TeacherAttendanceRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $attendance = TeacherAttendance::find($id);
            if (!$attendance) {
                return apiResponse('Absensi guru tidak ditemukan', null, 404);
            }

            $attendance->update($validated);
            DB::commit();
            return apiResponse('Absensi guru berhasil diupdate', ['teacher_attendance' => $attendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $attendance = TeacherAttendance::find($id);
            if (!$attendance) {
                return apiResponse('Absensi guru tidak ditemukan', null, 404);
            }

            $attendance->delete();
            DB::commit();
            return apiResponse('Absensi guru berhasil dihapus', ['teacher_attendance' => $attendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByTeacher($teacherId)
    {
        try {
            $attendances = TeacherAttendance::where('teacher_id', $teacherId)
                ->orderBy('date', 'desc')
                ->get();
            return $attendances;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByDate($date)
    {
        try {
            $attendances = TeacherAttendance::with(['teacher'])
                ->whereDate('date', $date)
                ->orderBy('time_in', 'asc')
                ->get();
            return $attendances;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function checkIn(TeacherAttendanceRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            // Check if already checked in today
            $existing = TeacherAttendance::where('teacher_id', $validated['teacher_id'])
                ->whereDate('date', now()->toDateString())
                ->first();

            if ($existing) {
                return apiResponse('Guru sudah melakukan check in hari ini', ['teacher_attendance' => $existing], 400);
            }

            $validated['date'] = now()->toDateString();
            $attendance = TeacherAttendance::create($validated);

            DB::commit();
            return apiResponse('Check in berhasil', ['teacher_attendance' => $attendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function checkOut(Request $request, $id)
    {
        $validated = $request->validate([
            'time_out' => 'nullable|date_format:H:i',
            'photo_out' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $attendance = TeacherAttendance::find($id);
            if (!$attendance) {
                return apiResponse('Absensi guru tidak ditemukan', null, 404);
            }

            if ($attendance->time_out) {
                return apiResponse('Guru sudah melakukan check out', ['teacher_attendance' => $attendance], 400);
            }

            $attendance->update([
                'time_out' => $validated['time_out'] ?? now()->format('H:i'),
                'photo_out' => $validated['photo_out'] ?? null,
            ]);

            DB::commit();
            return apiResponse('Check out berhasil', ['teacher_attendance' => $attendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getTodayAttendance()
    {
        try {
            $attendances = TeacherAttendance::with(['teacher'])
                ->whereDate('date', now()->toDateString())
                ->orderBy('time_in', 'asc')
                ->get();
            return $attendances;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
