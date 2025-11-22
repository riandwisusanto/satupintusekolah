<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TeacherJournalExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $query;
    protected $filters;
    protected $rowNumber = 0;

    public function __construct($query, $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    /**
     * Get collection of data
     */
    public function collection()
    {
        return $this->query->orderBy('date', 'desc')->get();
    }

    /**
     * Define headings
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Nama Guru',
            'Kelas',
            'Mata Pelajaran',
            'Tema',
            'Kegiatan',
            'Catatan',
        ];
    }

    /**
     * Map data to rows
     */
    public function map($journal): array
    {
        $this->rowNumber++;
        
        // Get subjects as comma-separated string
        $subjects = $journal->subjects->map(function($js) {
            return $js->subject->name ?? '-';
        })->join(', ');

        return [
            $this->rowNumber,
            $journal->date ? \Carbon\Carbon::parse($journal->date)->format('d/m/Y') : '-',
            $journal->teacher->name ?? '-',
            $journal->classroom->name ?? '-',
            $subjects ?: '-',
            $journal->theme ?? '-',
            $journal->activity ?? '-',
            $journal->notes ?? '-',
        ];
    }

    /**
     * Apply styles to worksheet
     */
    public function styles(Worksheet $sheet)
    {
        // Style for header row
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001f3f'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add borders to all cells with data
        $lastRow = $this->rowNumber + 1;
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Center align for specific columns
        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Wrap text for long content columns
        $sheet->getStyle("F2:H{$lastRow}")->getAlignment()->setWrapText(true);

        return [];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'Laporan Jurnal Guru';
    }
}
