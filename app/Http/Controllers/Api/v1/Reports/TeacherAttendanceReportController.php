<?php

namespace App\Http\Controllers\Api\v1\Reports;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherAttendanceExport;

class TeacherAttendanceReportController extends Controller
{
    /**
     * Get teacher attendance report with filtering
     */
    public function index(Request $request)
    {
        try {
            $query = TeacherAttendance::with(['teacher']);

            // Filter by date range
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            // Filter by month (YYYY-MM format)
            if ($request->has('month')) {
                $month = $request->month;
                $query->whereYear('date', substr($month, 0, 4))
                      ->whereMonth('date', substr($month, 5, 2));
            }

            // Filter by academic year
            if ($request->has('academic_year_id')) {
                // Assuming academic year has start_date and end_date
                $academicYear = \App\Models\AcademicYear::find($request->academic_year_id);
                if ($academicYear) {
                    $query->whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
                }
            }

            // Filter by teacher
            if ($request->has('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // Order by date descending
            $query->orderBy('date', 'desc');

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $attendances = $query->paginate($perPage);

            return apiResponse('Data laporan absensi guru', [
                'attendances' => $attendances
            ]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    /**
     * Get summary statistics for teacher attendance
     */
    public function summary(Request $request)
    {
        try {
            $query = TeacherAttendance::query();

            // Apply same filters as index
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date', [$request->start_date, $request->end_date]);
            }

            if ($request->has('month')) {
                $month = $request->month;
                $query->whereYear('date', substr($month, 0, 4))
                      ->whereMonth('date', substr($month, 5, 2));
            }

            if ($request->has('academic_year_id')) {
                $academicYear = \App\Models\AcademicYear::find($request->academic_year_id);
                if ($academicYear) {
                    $query->whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
                }
            }

            if ($request->has('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // Calculate statistics
            $totalRecords = $query->count();
            $presentDays = $query->whereIn('status', ['check_in', 'check_out'])->count();
            $sickDays = $query->where('status', 'sick')->count();
            $permissionDays = $query->where('status', 'permission')->count();
            $leaveDays = $query->where('status', 'on_leave')->count();
            $absentDays = $sickDays + $permissionDays + $leaveDays;

            // Get unique teachers count
            $uniqueTeachers = $query->distinct('teacher_id')->count('teacher_id');

            $summary = [
                'total_records' => $totalRecords,
                'unique_teachers' => $uniqueTeachers,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'sick_days' => $sickDays,
                'permission_days' => $permissionDays,
                'leave_days' => $leaveDays,
                'attendance_percentage' => $totalRecords > 0 ? round(($presentDays / $totalRecords) * 100, 2) : 0,
            ];

            return apiResponse('Summary laporan absensi guru', ['summary' => $summary]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    /**
     * Export report to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $query = TeacherAttendance::with(['teacher']);

            // Apply filters
            $this->applyFilters($query, $request);

            $attendances = $query->orderBy('date', 'desc')->get();
            $summary = $this->calculateSummary($query);

            $data = [
                'attendances' => $attendances,
                'summary' => $summary,
                'filters' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'month' => $request->month,
                    'teacher_name' => $request->teacher_id ? User::find($request->teacher_id)?->name : 'Semua Guru',
                ],
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ];

            $pdf = Pdf::loadView('reports.teacher-attendance-pdf', $data);
            $pdf->setPaper('a4', 'landscape');

            $filename = 'laporan-absensi-guru-' . now()->format('YmdHis') . '.pdf';
            
            return $pdf->download($filename);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    /**
     * Export report to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = TeacherAttendance::with(['teacher']);
            $this->applyFilters($query, $request);

            $filename = 'laporan-absensi-guru-' . now()->format('YmdHis') . '.xlsx';
            
            return Excel::download(
                new TeacherAttendanceExport($query, $request->all()), 
                $filename
            );
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, $request)
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->has('month')) {
            $month = $request->month;
            $query->whereYear('date', substr($month, 0, 4))
                  ->whereMonth('date', substr($month, 5, 2));
        }

        if ($request->has('academic_year_id')) {
            $academicYear = \App\Models\AcademicYear::find($request->academic_year_id);
            if ($academicYear) {
                $query->whereBetween('date', [$academicYear->start_date, $academicYear->end_date]);
            }
        }

        if ($request->has('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }
    }

    /**
     * Calculate summary statistics
     */
    private function calculateSummary($query)
    {
        $clonedQuery = clone $query;
        
        $totalRecords = $clonedQuery->count();
        $presentDays = (clone $query)->whereIn('status', ['check_in', 'check_out'])->count();
        $sickDays = (clone $query)->where('status', 'sick')->count();
        $permissionDays = (clone $query)->where('status', 'permission')->count();
        $leaveDays = (clone $query)->where('status', 'on_leave')->count();

        return [
            'total_records' => $totalRecords,
            'present_days' => $presentDays,
            'sick_days' => $sickDays,
            'permission_days' => $permissionDays,
            'leave_days' => $leaveDays,
            'absent_days' => $sickDays + $permissionDays + $leaveDays,
            'attendance_percentage' => $totalRecords > 0 ? round(($presentDays / $totalRecords) * 100, 2) : 0,
        ];
    }
}
