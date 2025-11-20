<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicYear\AcademicYearRequest;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AcademicYearController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            AcademicYear::query(),
            AcademicYear::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(AcademicYearRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $academicYear = AcademicYear::create($validated);

            DB::commit();
            return apiResponse('Tahun ajaran berhasil dibuat', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $academicYear = AcademicYear::with(['classes', 'schedules', 'journals'])->find($id);
            if (!$academicYear) {
                return apiResponse('Tahun ajaran tidak ditemukan', null, 404);
            }
            return $academicYear;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(AcademicYearRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $academicYear = AcademicYear::find($id);
            if (!$academicYear) {
                return apiResponse('Tahun ajaran tidak ditemukan', null, 404);
            }

            $academicYear->update($validated);
            DB::commit();
            return apiResponse('Tahun ajaran berhasil diupdate', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $academicYear = AcademicYear::find($id);
            if (!$academicYear) {
                return apiResponse('Tahun ajaran tidak ditemukan', null, 404);
            }

            $academicYear->delete();
            DB::commit();
            return apiResponse('Tahun ajaran berhasil dihapus', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getActive()
    {
        try {
            $activeYear = AcademicYear::where('active', true)->first();
            if (!$activeYear) {
                return apiResponse('Tidak ada tahun ajaran aktif', null, 404);
            }
            return $activeYear;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function setActive($id)
    {
        DB::beginTransaction();
        try {
            $academicYear = AcademicYear::find($id);
            if (!$academicYear) {
                return apiResponse('Tahun ajaran tidak ditemukan', null, 404);
            }

            // Deactivate all other academic years
            AcademicYear::where('id', '!=', $id)->update(['active' => false]);

            // Activate this academic year
            $academicYear->update(['active' => true]);

            DB::commit();
            return apiResponse('Tahun ajaran berhasil diaktifkan', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
