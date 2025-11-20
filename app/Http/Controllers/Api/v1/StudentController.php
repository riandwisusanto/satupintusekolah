<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StudentRequest;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Student::query(),
            Student::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(StudentRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $student = Student::create($validated);

            DB::commit();
            return apiResponse('Siswa berhasil dibuat', ['student' => $student]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(StudentRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $student = Student::find($id);
            $student->update($validated);
            DB::commit();
            return apiResponse('Siswa berhasil diupdate', ['student' => $student]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $student = Student::find($id);
            $student->delete();
            DB::commit();
            return apiResponse('Siswa berhasil dihapus', ['student' => $student]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
