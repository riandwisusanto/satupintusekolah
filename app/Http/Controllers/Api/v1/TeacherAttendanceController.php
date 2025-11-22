<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherAttendance\CheckInRequest;
use App\Http\Requests\TeacherAttendance\CheckOutRequest;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

    public function show($id)
    {
        try {
            $teacherAttendance = TeacherAttendance::with(['teacher'])->find($id);

            if (!$teacherAttendance) {
                return apiResponse('Data kehadiran guru tidak ditemukan', null, 404);
            }

            return apiResponse('Data kehadiran guru ditemukan', ['teacher_attendance' => $teacherAttendance]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByTeacherAndDate($teacher_id, $date)
    {
        try {
            $teacherAttendance = TeacherAttendance::with(['teacher'])
                ->where('teacher_id', $teacher_id)
                ->whereDate('date', $date)
                ->first();

            if (!$teacherAttendance) {
                return apiResponse('Data kehadiran guru tidak ditemukan', null, 404);
            }

            return apiResponse('Data kehadiran guru ditemukan', ['teacher_attendance' => $teacherAttendance]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function checkIn(CheckInRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $validated['status'] = 'check_in';

            // Handle file upload
            if ($request->hasFile('photo_in')) {
                $file = $request->file('photo_in');
                $path = $file->store("attendance/photos/{$validated['date']}/{$validated['teacher_id']}", 'public');
                $validated['photo_in'] = $path;
            }

            $teacherAttendance = TeacherAttendance::create($validated);

            DB::commit();
            return apiResponse('Check-in berhasil', ['teacher_attendance' => $teacherAttendance->load(['teacher'])]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function checkOut(CheckOutRequest $request, $id)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $teacherAttendance = TeacherAttendance::find($id);

            if (!$teacherAttendance) {
                return apiResponse('Data kehadiran guru tidak ditemukan', null, 404);
            }

            // Handle file upload
            if ($request->hasFile('photo_out')) {
                $file = $request->file('photo_out');
                $path = $file->store("attendance/photos/{$teacherAttendance->date}/{$teacherAttendance->teacher_id}", 'public');
                $validated['photo_out'] = $path;
            }

            $validated['status'] = 'check_out';
            $teacherAttendance->update($validated);

            DB::commit();
            return apiResponse('Check-out berhasil', ['teacher_attendance' => $teacherAttendance->load(['teacher'])]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function createSickOrLeave(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'status' => 'required|in:sick,permission,on_leave',
            'notes' => 'nullable|string|max:255',
        ]);

        // Set time_in and time_out to null for absence records
        $validated['time_in'] = null;
        $validated['time_out'] = null;

        try {
            $teacherAttendance = TeacherAttendance::create($validated);

            return apiResponse('Data kehadiran berhasil dicatat', ['teacher_attendance' => $teacherAttendance->load(['teacher'])]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getTodayAttendance($teacher_id = null)
    {
        try {
            $query = TeacherAttendance::with(['teacher'])->today();

            if ($teacher_id) {
                $query->byTeacher($teacher_id);
            }

            $attendances = $query->get();

            return apiResponse('Data kehadiran hari ini', ['attendances' => $attendances]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getAttendanceHistory(Request $request, $teacher_id = null)
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

    public function getMonthlyReport(Request $request)
    {
        try {
            $validated = $request->validate([
                'month' => 'required|date_format:Y-m',
                'teacher_id' => 'nullable|exists:users,id'
            ]);

            $query = TeacherAttendance::with('teacher')
                ->whereYear('date', substr($validated['month'], 0, 4))
                ->whereMonth('date', substr($validated['month'], 5, 2));

            if (isset($validated['teacher_id'])) {
                $query->where('teacher_id', $validated['teacher_id']);
            }

            $attendances = $query->orderBy('date', 'desc')->get();

            // Calculate statistics
            $stats = [
                'total_days' => $attendances->count(),
                'present_days' => $attendances->whereIn('status', ['check_in', 'check_out'])->count(),
                'absent_days' => $attendances->whereIn('status', ['sick', 'permission', 'on_leave'])->count(),
            ];

            return apiResponse('Report bulanan', [
                'attendances' => $attendances,
                'statistics' => $stats
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return apiResponse('Validasi gagal: ' . $e->getMessage(), null, 422);
        } catch (\Throwable $th) {
            return apiResponse('Terjadi kesalahan: ' . $th->getMessage(), null, 500);
        }
    }
}
