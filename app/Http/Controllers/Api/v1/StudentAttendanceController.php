<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentAttendance\StudentAttendanceRequest;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSubject;
use App\Models\StudentAttendanceDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAttendanceController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            StudentAttendance::query(),
            StudentAttendance::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(StudentAttendanceRequest $request)
    {
        $validated = $request->validated();
        $subjects = $validated['subjects'];
        $details = $validated['details'];
        unset($validated['subjects'], $validated['details']);

        DB::beginTransaction();
        try {
            $studentAttendance = StudentAttendance::create($validated);

            // Create subjects relationships
            foreach ($subjects as $subject) {
                StudentAttendanceSubject::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'subject_id' => $subject['subject_id']
                ]);
            }

            // Create details relationships
            foreach ($details as $detail) {
                StudentAttendanceDetail::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'student_id' => $detail['student_id'],
                    'status' => $detail['status'],
                    'note' => $detail['note'] ?? null
                ]);
            }

            DB::commit();
            return apiResponse('Data kehadiran berhasil dibuat', ['student_attendance' => $studentAttendance->load(['subjects', 'details'])]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $studentAttendance = StudentAttendance::with(['subjects', 'details', 'teacher', 'academicYear'])->find($id);

            if (!$studentAttendance) {
                return apiResponse('Data kehadiran tidak ditemukan', null, 404);
            }

            return apiResponse('Data kehadiran ditemukan', ['student_attendance' => $studentAttendance]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(StudentAttendanceRequest $request, $id)
    {
        $validated = $request->validated();
        $subjects = $validated['subjects'];
        $details = $validated['details'];
        unset($validated['subjects'], $validated['details']);

        DB::beginTransaction();
        try {
            $studentAttendance = StudentAttendance::find($id);

            if (!$studentAttendance) {
                return apiResponse('Data kehadiran tidak ditemukan', null, 404);
            }

            $studentAttendance->update($validated);

            // Delete existing subjects and details
            StudentAttendanceSubject::where('student_attendance_id', $studentAttendance->id)->delete();
            StudentAttendanceDetail::where('student_attendance_id', $studentAttendance->id)->delete();

            // Create new subjects relationships
            foreach ($subjects as $subject) {
                StudentAttendanceSubject::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'subject_id' => $subject['subject_id']
                ]);
            }

            // Create new details relationships
            foreach ($details as $detail) {
                StudentAttendanceDetail::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'student_id' => $detail['student_id'],
                    'status' => $detail['status'],
                    'note' => $detail['note'] ?? null
                ]);
            }

            DB::commit();
            return apiResponse('Data kehadiran berhasil diupdate', ['student_attendance' => $studentAttendance->load(['subjects', 'details'])]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $studentAttendance = StudentAttendance::find($id);

            if (!$studentAttendance) {
                return apiResponse('Data kehadiran tidak ditemukan', null, 404);
            }

            $studentAttendance->delete();
            DB::commit();
            return apiResponse('Data kehadiran berhasil dihapus', ['student_attendance' => $studentAttendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getStudentsByDate($date)
    {
        try {
            $studentAttendances = StudentAttendance::with(['details.student'])
                ->whereDate($date)
                ->get();

            $students = [];
            foreach ($studentAttendances as $attendance) {
                foreach ($attendance->details as $detail) {
                    $students[] = [
                        'student_id' => $detail->student_id,
                        'status' => $detail->status,
                        'note' => $detail->note
                    ];
                }
            }

            return apiResponse('Data siswa per tanggal', ['students' => $students]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
