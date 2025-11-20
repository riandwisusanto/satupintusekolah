<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectRequest;
use App\Models\Schedule;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Subject::when(!auth()->user()->isAdmin(), function ($query) {
                $query->whereHas('schedules', function ($query) {
                    $query->where('teacher_id', auth()->user()->id);
                });
            }),
            Subject::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(SubjectRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $subject = Subject::create($validated);

            DB::commit();
            return apiResponse('Mata pelajaran berhasil dibuat', ['subject' => $subject]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(SubjectRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $subject = Subject::find($id);
            $subject->update($validated);
            DB::commit();
            return apiResponse('Mata pelajaran berhasil diupdate', ['subject' => $subject]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $subject = Subject::find($id);
            $subject->delete();
            DB::commit();
            return apiResponse('Mata pelajaran berhasil dihapus', ['subject' => $subject]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getOptions()
    {
        try {
            $subjects = Subject::where('active', true)->select('id', 'name')->get();

            return $subjects;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getOptionBySchedule($id)
    {
        try {
            $subjects = Schedule::find($id)->subject->select('id', 'name')->get();

            return $subjects;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
