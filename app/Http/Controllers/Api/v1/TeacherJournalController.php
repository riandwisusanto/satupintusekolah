<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\JournalRequest;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherJournalController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Journal::query(),
            Journal::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(JournalRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $journal = Journal::create($validated);

            // Handle subjects if provided
            if (isset($validated['subjects']) && is_array($validated['subjects'])) {
                $journal->subjects()->sync($validated['subjects']);
            }

            DB::commit();
            return apiResponse('Jurnal guru berhasil dibuat', ['journal' => $journal->load('subjects')]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $journal = Journal::with(['teacher', 'class', 'academicYear', 'subjects'])->find($id);
            if (!$journal) {
                return apiResponse('Jurnal tidak ditemukan', null, 404);
            }
            return $journal;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(JournalRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $journal = Journal::find($id);
            if (!$journal) {
                return apiResponse('Jurnal tidak ditemukan', null, 404);
            }

            $journal->update($validated);

            // Handle subjects if provided
            if (isset($validated['subjects']) && is_array($validated['subjects'])) {
                $journal->subjects()->sync($validated['subjects']);
            }

            DB::commit();
            return apiResponse('Jurnal guru berhasil diupdate', ['journal' => $journal->load('subjects')]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $journal = Journal::find($id);
            if (!$journal) {
                return apiResponse('Jurnal tidak ditemukan', null, 404);
            }

            $journal->delete();
            DB::commit();
            return apiResponse('Jurnal guru berhasil dihapus', ['journal' => $journal]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByTeacher($teacherId)
    {
        try {
            $journals = Journal::with(['class', 'academicYear', 'subjects'])
                ->where('teacher_id', $teacherId)
                ->orderBy('date', 'desc')
                ->get();
            return $journals;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByClass($classId)
    {
        try {
            $journals = Journal::with(['teacher', 'academicYear', 'subjects'])
                ->where('class_id', $classId)
                ->orderBy('date', 'desc')
                ->get();
            return $journals;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getByDate($date)
    {
        try {
            $journals = Journal::with(['teacher', 'class', 'academicYear', 'subjects'])
                ->whereDate('date', $date)
                ->orderBy('date', 'desc')
                ->get();
            return $journals;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
