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

            // If the new academic year is set to active, deactivate others and update classrooms
            if ($validated['active']) {
                $academicYear->setActive();
            }

            DB::commit();
            return apiResponse('Tahun ajaran berhasil dibuat', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(AcademicYearRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $academicYear = AcademicYear::find($id);
            $academicYear->update($validated);

            // If the academic year is set to active, deactivate others and update classrooms
            if (isset($validated['active']) && $validated['active']) {
                $academicYear->setActive();
            }

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
            $academicYear->delete();
            DB::commit();
            return apiResponse('Tahun ajaran berhasil dihapus', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getOptions()
    {
        try {
            $academicYears = AcademicYear::where('active', true)
                ->select('id', 'name', 'semester')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name . ' (Semester ' . $item->semester . ')'
                    ];
                });

            return $academicYears;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getActive()
    {
        try {
            $activeAcademicYear = AcademicYear::where('active', true)
                ->orderBy('created_at', 'desc')
                ->first();

            return apiResponse('Data tahun ajaran aktif', ['academic_year' => $activeAcademicYear]);
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

            $academicYear->setActive();

            DB::commit();
            return apiResponse('Tahun ajaran berhasil diaktifkan dan semua kelas telah diperbarui', ['academic_year' => $academicYear]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
