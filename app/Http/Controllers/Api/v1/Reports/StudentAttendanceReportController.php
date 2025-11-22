<?php

namespace App\Http\Controllers\Api\v1\Reports;

use App\Http\Controllers\Controller;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceDetail;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentAttendanceExport;

class StudentAttendanceReportController extends Controller
{
    /**
     * Get student attendance report with filtering
     */
    public function index(Request $request)
    {
        try {
            $query = StudentAttendance::with(['teacher', 'classroom', 'academicYear', 'details.student']);

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
                $query->where('academic_year_id', $request->academic_year_id);
            }

            // Filter by classroom
            if ($request->has('class_id')) {
                $query->where('class_id', $request->class_id);
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

            return apiResponse('Data laporan absensi siswa', [
                'attendances' => $attendances
            ]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    /**
     * Get summary statistics for student attendance
     */
    public function summary(Request $request)
    {
        try {
            $query = StudentAttendance::query();
            $this->applyFilters($query, $request);

            // Calculate statistics from details
            $detailsQuery = StudentAttendanceDetail::whereHas('studentAttendance', function($q) use ($request) {
                $this->applyFilters($q, $request);
            });

            $totalRecords = $query->count();
            $totalStudents = $detailsQuery->distinct('student_id')->count('student_id');
            $presentCount = (clone $detailsQuery)->where('status', 'present')->count();
            $absentCount = (clone $detailsQuery)->where('status', 'absent')->count();
            $sickCount = (clone $detailsQuery)->where('status', 'sick')->count();
            $permissionCount = (clone $detailsQuery)->where('status', 'permission')->count();

            $totalDetails = $presentCount + $absentCount + $sickCount + $permissionCount;

            $summary = [
                'total_records' => $totalRecords,
                'total_students' => $totalStudents,
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'sick_count' => $sickCount,
                'permission_count' => $permissionCount,
                'attendance_percentage' => $totalDetails > 0 ? round(($presentCount / $totalDetails) * 100, 2) : 0,
            ];

            return apiResponse('Summary laporan absensi siswa', ['summary' => $summary]);
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
            $query = StudentAttendance::with(['teacher', 'classroom', 'academicYear', 'details.student']);
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
                    'class_name' => $request->class_id ? \App\Models\Classroom::find($request->class_id)?->name : 'Semua Kelas',
                ],
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ];

            $pdf = Pdf::loadView('reports.student-attendance-pdf', $data);
            $pdf->setPaper('a4', 'landscape');

            $filename = 'laporan-absensi-siswa-' . now()->format('YmdHis') . '.pdf';
            
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
            $query = StudentAttendance::with(['teacher', 'classroom', 'academicYear', 'details.student']);
            $this->applyFilters($query, $request);

            $filename = 'laporan-absensi-siswa-' . now()->format('YmdHis') . '.xlsx';
            
            return Excel::download(
                new StudentAttendanceExport($query, $request->all()), 
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
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
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
        
        // Get all details from filtered attendances
        $allDetails = StudentAttendanceDetail::whereIn('student_attendance_id', 
            (clone $query)->pluck('id')
        );

        $totalStudents = (clone $allDetails)->distinct('student_id')->count('student_id');
        $presentCount = (clone $allDetails)->where('status', 'present')->count();
        $absentCount = (clone $allDetails)->where('status', 'absent')->count();
        $sickCount = (clone $allDetails)->where('status', 'sick')->count();
        $permissionCount = (clone $allDetails)->where('status', 'permission')->count();

        return [
            'total_records' => $totalRecords,
            'total_students' => $totalStudents,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'sick_count' => $sickCount,
            'permission_count' => $permissionCount,
        ];
    }
}
