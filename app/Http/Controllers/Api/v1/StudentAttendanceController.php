<?php

namespace App\Http\Controllers\Api\v1;

use App\Helpers\ApiQueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StudentAttendance\StudentAttendanceRequest;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceSubject;
use App\Models\StudentAttendanceDetail;
use App\Models\Student;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentAttendanceController extends Controller
{
    public function index()
    {
        $datas = ApiQueryHelper::apply(
            StudentAttendance::query(),
            StudentAttendance::apiQueryConfig()
        );
        try {
            return $datas;
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function store(StudentAttendanceRequest $request)
    {
        $validated = $request->validated();
        $subjects = $validated['subjects'];
        $details = $validated['details'];
        unset($validated['subjects'], $validated['details']);

        DB::beginTransaction();
        try {
            $studentAttendance = StudentAttendance::create([
                ...$validated,
                'academic_year_id' => AcademicYear::where('active', true)->first()->id,
                'teacher_id' => auth()->id()
            ]);

            // Create subjects relationships
            foreach ($subjects as $subject) {
                StudentAttendanceSubject::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'subject_id' => $subject['subject_id']
                ]);
            }

            // Create details relationships
            foreach ($details as $detail) {
                StudentAttendanceDetail::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'student_id' => $detail['student_id'],
                    'status' => $detail['status'],
                    'note' => $detail['note'] ?? null
                ]);
            }

            DB::commit();
            return apiResponse('Data kehadiran berhasil dibuat', ['student_attendance' => $studentAttendance->load(['subjects', 'details'])]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function show($id)
    {
        try {
            $studentAttendance = StudentAttendance::with(['subjects', 'details', 'teacher', 'academicYear'])->find($id);

            if (!$studentAttendance) {
                return apiResponse('Data kehadiran tidak ditemukan', null, 404);
            }

            return apiResponse('Data kehadiran ditemukan', ['student_attendance' => $studentAttendance]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function update(StudentAttendanceRequest $request, $id)
    {
        $validated = $request->validated();
        $subjects = $validated['subjects'];
        $details = $validated['details'];
        unset($validated['subjects'], $validated['details']);

        DB::beginTransaction();
        try {
            $studentAttendance = StudentAttendance::find($id);

            if (!$studentAttendance) {
                return apiResponse('Data kehadiran tidak ditemukan', null, 404);
            }

            $studentAttendance->update($validated);

            // Delete existing subjects and details
            StudentAttendanceSubject::where('student_attendance_id', $studentAttendance->id)->delete();
            StudentAttendanceDetail::where('student_attendance_id', $studentAttendance->id)->delete();

            // Create new subjects relationships
            foreach ($subjects as $subject) {
                StudentAttendanceSubject::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'subject_id' => $subject['subject_id']
                ]);
            }

            // Create new details relationships
            foreach ($details as $detail) {
                StudentAttendanceDetail::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'student_id' => $detail['student_id'],
                    'status' => $detail['status'],
                    'note' => $detail['note'] ?? null
                ]);
            }

            DB::commit();
            return apiResponse('Data kehadiran berhasil diupdate', ['student_attendance' => $studentAttendance->load(['subjects', 'details'])]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $studentAttendance = StudentAttendance::find($id);

            if (!$studentAttendance) {
                return apiResponse('Data kehadiran tidak ditemukan', null, 404);
            }

            $studentAttendance->delete();
            DB::commit();
            return apiResponse('Data kehadiran berhasil dihapus', ['student_attendance' => $studentAttendance]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getStudentsByDate($date)
    {
        try {
            $studentAttendances = StudentAttendance::with(['details.student'])
                ->whereDate($date)
                ->get();

            $students = [];
            foreach ($studentAttendances as $attendance) {
                foreach ($attendance->details as $detail) {
                    $students[] = [
                        'student_id' => $detail->student_id,
                        'status' => $detail->status,
                        'note' => $detail->note
                    ];
                }
            }

            return apiResponse('Data siswa per tanggal', ['students' => $students]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getTeacherAttendanceData(Request $request)
    {
        try {
            $user = $request->user();
            $date = $request->get('date', Carbon::now()->format('Y-m-d'));
            $classId = $request->get('class_id');

            // Get today's schedule for teacher
            $todaySchedule = Schedule::where('teacher_id', $user->id)
                ->where('day', strtolower(Carbon::parse($date)->format('l')))
                ->with(['classroom', 'subject'])
                ->get();

            // Get students based on class
            $students = [];
            $selectedClass = null;
            $existingAttendance = null;

            if ($classId) {
                $selectedClass = Classroom::find($classId);
                $students = Student::where('class_id', $classId)
                    ->orderBy('name')
                    ->get();

                // Check if attendance already exists
                $existingAttendance = StudentAttendance::with(['details'])
                    ->where('teacher_id', $user->id)
                    ->where('class_id', $classId)
                    ->where('date', $date)
                    ->first();
            }

            return apiResponse('Data absensi guru', [
                'date' => $date,
                'today_schedule' => $todaySchedule,
                'selected_class' => $selectedClass,
                'students' => $students,
                'existing_attendance' => $existingAttendance,
                'academic_year' => AcademicYear::where('active', true)->first()
            ]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function saveClassAttendance(Request $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->user();
            $data = $request->all();
            $date = $data['date'];
            $classId = $data['class_id'];
            $students = $data['students'];

            // Check if attendance already exists
            $existingAttendance = StudentAttendance::where('teacher_id', $user->id)
                ->where('class_id', $classId)
                ->where('date', $date)
                ->first();

            $academicYear = AcademicYear::where('active', true)->first();

            $attendanceData = [
                'teacher_id' => $user->id,
                'class_id' => $classId,
                'academic_year_id' => $academicYear->id,
                'date' => $date
            ];

            if ($existingAttendance) {
                $existingAttendance->update($attendanceData);
                $studentAttendance = $existingAttendance;

                // Delete existing details
                StudentAttendanceDetail::where('student_attendance_id', $existingAttendance->id)->delete();
            } else {
                $studentAttendance = StudentAttendance::create($attendanceData);
            }

            // Create attendance details for each student
            foreach ($students as $studentData) {
                StudentAttendanceDetail::create([
                    'student_attendance_id' => $studentAttendance->id,
                    'student_id' => $studentData['student_id'],
                    'status' => $studentData['status'],
                    'note' => $studentData['note'] ?? null
                ]);
            }

            DB::commit();
            return apiResponse('Absensi siswa berhasil disimpan', [
                'student_attendance' => $studentAttendance->load('details.student')
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    public function getTodayClasses(Request $request)
    {
        try {
            $user = $request->user();
            $today = Carbon::now()->format('l');

            // Get today's classes from schedule
            $todayClasses = Schedule::where('teacher_id', $user->id)
                ->where('day', strtolower($today))
                ->with('classroom')
                ->distinct('class_id')
                ->get()
                ->map(function ($schedule) {
                    return [
                        'id' => $schedule->classroom->id,
                        'name' => $schedule->classroom->name,
                        'subject' => $schedule->subject->name ?? null,
                        'time' => $schedule->start_time . ' - ' . $schedule->end_time
                    ];
                });

            return apiResponse('Data kelas hari ini', ['classes' => $todayClasses]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }
}
