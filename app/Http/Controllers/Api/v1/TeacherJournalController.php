<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherJournal\TeacherJournalRequest;
use App\Models\JournalSubject;
use App\Models\TeacherJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherJournalController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            TeacherJournal::query(),
            TeacherJournal::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(TeacherJournalRequest $request)
    {
        $validated = $request->validated();
        $subjectIds = $validated['subject_ids'];
        unset($validated['subject_ids']);

        DB::beginTransaction();
        try {
            $journal = TeacherJournal::create($validated);

            // Create journal subjects relationships
            foreach ($subjectIds as $subjectId) {
                JournalSubject::create([
                    'teacher_journal_id' => $journal->id,
                    'subject_id' => $subjectId
                ]);
            }

            DB::commit();
            return apiResponse('Jurnal guru berhasil dibuat', ['journal' => $journal->load('subjects')]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(TeacherJournalRequest $request, $id)
    {
        $validated = $request->validated();
        $subjectIds = $validated['subject_ids'];
        unset($validated['subject_ids']);

        DB::beginTransaction();
        try {
            $journal = TeacherJournal::find($id);
            $journal->update($validated);

            // Delete existing journal subjects
            JournalSubject::where('teacher_journal_id', $journal->id)->delete();

            // Create new journal subjects relationships
            foreach ($subjectIds as $subjectId) {
                JournalSubject::create([
                    'teacher_journal_id' => $journal->id,
                    'subject_id' => $subjectId
                ]);
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
            $journal = TeacherJournal::find($id);
            $journal->delete();
            DB::commit();
            return apiResponse('Jurnal guru berhasil dihapus', ['journal' => $journal]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
