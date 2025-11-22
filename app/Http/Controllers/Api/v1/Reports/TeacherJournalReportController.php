<?php

namespace App\Http\Controllers\Api\v1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TeacherJournalExport;

class TeacherJournalReportController extends Controller
{
    /**
     * Get teacher journal report with filtering
     */
    public function index(Request $request)
    {
        try {
            $query = Journal::with(['teacher', 'classroom', 'subjects.subject', 'academicYear']);

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

            // Filter by teacher
            if ($request->has('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // Filter by subject
            if ($request->has('subject_id')) {
                $query->whereHas('subjects', function($q) use ($request) {
                    $q->where('subject_id', $request->subject_id);
                });
            }

            // Filter by classroom
            if ($request->has('class_id')) {
                $query->where('class_id', $request->class_id);
            }

            // Order by date descending
            $query->orderBy('date', 'desc');

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $journals = $query->paginate($perPage);

            return apiResponse('Data laporan jurnal guru', [
                'journals' => $journals
            ]);
        } catch (\Throwable $th) {
            return apiResponse($th->getMessage(), null, 500);
        }
    }

    /**
     * Get summary statistics for teacher journals
     */
    public function summary(Request $request)
    {
        try {
            $query = Journal::query();

            // Apply same filters as index
            $this->applyFilters($query, $request);

            // Calculate statistics
            $totalJournals = $query->count();
            $uniqueTeachers = $query->distinct('teacher_id')->count('teacher_id');
            $uniqueClasses = $query->distinct('class_id')->count('class_id');
            
            // Get subject statistics
            $subjectStats = $query->with('subjects.subject')
                ->get()
                ->flatMap(function($journal) {
                    return $journal->subjects;
                })
                ->groupBy('subject_id')
                ->map(function($group) {
                    return [
                        'subject_name' => $group->first()->subject->name ?? 'Unknown',
                        'count' => $group->count()
                    ];
                })
                ->values();

            $summary = [
                'total_journals' => $totalJournals,
                'unique_teachers' => $uniqueTeachers,
                'unique_classes' => $uniqueClasses,
                'total_subjects' => $subjectStats->count(),
                'subject_breakdown' => $subjectStats->take(5), // Top 5 subjects
            ];

            return apiResponse('Summary laporan jurnal guru', ['summary' => $summary]);
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
            $query = Journal::with(['teacher', 'classroom', 'subjects.subject', 'academicYear']);
            $this->applyFilters($query, $request);

            $journals = $query->orderBy('date', 'desc')->get();
            $summary = $this->calculateSummary($query);

            $data = [
                'journals' => $journals,
                'summary' => $summary,
                'filters' => [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'month' => $request->month,
                    'teacher_name' => $request->teacher_id ? User::find($request->teacher_id)?->name : 'Semua Guru',
                ],
                'generated_at' => now()->format('d M Y H:i:s'),
            ];

            $pdf = Pdf::loadView('reports.teacher-journal-pdf', $data);
            $pdf->setPaper('a4', 'landscape');

            $filename = 'laporan-jurnal-guru-' . now()->format('YmdHis') . '.pdf';
            
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
            $query = Journal::with(['teacher', 'classroom', 'subjects.subject', 'academicYear']);
            $this->applyFilters($query, $request);

            $filename = 'laporan-jurnal-guru-' . now()->format('YmdHis') . '.xlsx';
            
            return Excel::download(
                new TeacherJournalExport($query, $request->all()), 
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
    if ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('date', [$request->start_date, $request->end_date]);
    }

    if ($request->filled('month')) {
        $month = $request->month;
        $query->whereYear('date', substr($month, 0, 4))
              ->whereMonth('date', substr($month, 5, 2));
    }

    if ($request->filled('academic_year_id')) {
        $query->where('academic_year_id', $request->academic_year_id);
    }

    if ($request->filled('teacher_id')) {
        $query->where('teacher_id', $request->teacher_id);
    }

    if ($request->filled('subject_id')) {
        $query->whereHas('subjects', function($q) use ($request) {
            $q->where('subject_id', $request->subject_id);
        });
    }

    if ($request->filled('class_id')) {
        $query->where('class_id', $request->class_id);
    }
}

    /**
     * Calculate summary statistics
     */
    private function calculateSummary($query)
    {
        $clonedQuery = clone $query;
        
        $totalJournals = $clonedQuery->count();
        $uniqueTeachers = (clone $query)->distinct('teacher_id')->count('teacher_id');
        $uniqueClasses = (clone $query)->distinct('class_id')->count('class_id');

        return [
            'total_journals' => $totalJournals,
            'unique_teachers' => $uniqueTeachers,
            'unique_classes' => $uniqueClasses,
        ];
    }
}
