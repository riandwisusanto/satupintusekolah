<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\ScheduleRequest;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            Schedule::when(!auth()->user()->isAdmin(), function ($query) {
                $query->where('teacher_id', auth()->user()->id);
            }),
            Schedule::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(ScheduleRequest $request)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $schedule = Schedule::create($validated);

            DB::commit();
            return apiResponse('Jadwal berhasil dibuat', ['schedule' => $schedule]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(ScheduleRequest $request, $id)
    {
        $validated = $request->validated();
        DB::beginTransaction();
        try {
            $schedule = Schedule::find($id);
            $schedule->update($validated);
            DB::commit();
            return apiResponse('Jadwal berhasil diupdate', ['schedule' => $schedule]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $schedule = Schedule::find($id);
            $schedule->delete();
            DB::commit();
            return apiResponse('Jadwal berhasil dihapus', ['schedule' => $schedule]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function today()
    {
        $dayOfWeek = now()->dayOfWeek;
        if ($dayOfWeek == 0) {
            return collect([]);
        }
        $dbDay = $dayOfWeek;
        $datas = ApiQueryHelper::apply(
            Schedule::when(!auth()->user()->isAdmin(), function ($query) {
                $query->where('teacher_id', auth()->user()->id);
            })->where('day', $dbDay),
            Schedule::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
