<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Journal\JournalRequest;
use App\Models\JournalSubject;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
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
        $subjectIds = $validated['subject_ids'];
        unset($validated['subject_ids']);

        DB::beginTransaction();
        try {
            $journal = Journal::create($validated);

            // Create journal subjects relationships
            foreach ($subjectIds as $subjectId) {
                JournalSubject::create([
                    'journal_id' => $journal->id,
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

    public function update(JournalRequest $request, $id)
    {
        $validated = $request->validated();
        $subjectIds = $validated['subject_ids'];
        unset($validated['subject_ids']);

        DB::beginTransaction();
        try {
            $journal = Journal::find($id);

            if (!$journal) {
                return apiResponse('Jurnal guru tidak ditemukan', null, 404);
            }

            $journal->update($validated);

            JournalSubject::where('journal_id', $journal->id)->delete();

            foreach ($subjectIds as $subjectId) {
                JournalSubject::create([
                    'journal_id' => $journal->id,
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
            $journal = Journal::find($id);
            $journal->delete();
            DB::commit();
            return apiResponse('Jurnal guru berhasil dihapus', ['journal' => $journal]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
