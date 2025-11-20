<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Classroom\ClassroomRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClassroomController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Classroom::query(),
            Classroom::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(ClassroomRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $classroom = Classroom::create([
                ...$validated,
                'academic_year_id' => AcademicYear::where('active', true)->first()->id
            ]);

            DB::commit();
            return apiResponse('Kelas berhasil dibuat', ['classroom' => $classroom]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(ClassroomRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $classroom = Classroom::find($id);
            $classroom->update($validated);
            DB::commit();
            return apiResponse('Kelas berhasil diupdate', ['classroom' => $classroom]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $classroom = Classroom::find($id);
            $classroom->delete();
            DB::commit();
            return apiResponse('Kelas berhasil dihapus', ['classroom' => $classroom]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getOptions()
    {
        try {
            $classrooms = Classroom::where('active', true)->select('id', 'name')->get();

            return $classrooms;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
