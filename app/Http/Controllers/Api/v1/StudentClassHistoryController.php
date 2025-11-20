<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentClassHistory\StudentClassHistoryRequest;
use App\Models\StudentClassHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentClassHistoryController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            StudentClassHistory::query(),
            StudentClassHistory::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(StudentClassHistoryRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $history = StudentClassHistory::create($validated);

            DB::commit();
            return apiResponse('Riwayat kelas siswa berhasil dibuat', ['student_class_history' => $history]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $history = StudentClassHistory::with(['class', 'student', 'academicYear'])->find($id);
            if (!$history) {
                return apiResponse('Riwayat kelas siswa tidak ditemukan', null, 404);
            }
            return $history;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(StudentClassHistoryRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $history = StudentClassHistory::find($id);
            if (!$history) {
                return apiResponse('Riwayat kelas siswa tidak ditemukan', null, 404);
            }

            $history->update($validated);
            DB::commit();
            return apiResponse('Riwayat kelas siswa berhasil diupdate', ['student_class_history' => $history]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $history = StudentClassHistory::find($id);
            if (!$history) {
                return apiResponse('Riwayat kelas siswa tidak ditemukan', null, 404);
            }

            $history->delete();
            DB::commit();
            return apiResponse('Riwayat kelas siswa berhasil dihapus', ['student_class_history' => $history]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByStudent($studentId)
    {
        try {
            $histories = StudentClassHistory::with(['class', 'academicYear'])
                ->where('student_id', $studentId)
                ->orderBy('start_date', 'desc')
                ->get();
            return $histories;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByClass($classId)
    {
        try {
            $histories = StudentClassHistory::with(['student', 'academicYear'])
                ->where('class_id', $classId)
                ->orderBy('start_date', 'desc')
                ->get();
            return $histories;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByAcademicYear($academicYearId)
    {
        try {
            $histories = StudentClassHistory::with(['class', 'student'])
                ->where('academic_year_id', $academicYearId)
                ->orderBy('start_date', 'desc')
                ->get();
            return $histories;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getCurrentClass($studentId)
    {
        try {
            $currentClass = StudentClassHistory::with(['class', 'academicYear'])
                ->where('student_id', $studentId)
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->first();

            if (!$currentClass) {
                return apiResponse('Siswa tidak memiliki kelas aktif', null, 404);
            }

            return $currentClass;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
