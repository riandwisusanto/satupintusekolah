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

class TeacherAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
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
            'NIP',
            'Waktu Masuk',
            'Waktu Keluar',
            'Status',
            'Catatan',
        ];
    }

    /**
     * Map data to rows
     */
    public function map($attendance): array
    {
        $this->rowNumber++;
        
        $statusMap = [
            'check_in' => 'Masuk',
            'check_out' => 'Pulang',
            'sick' => 'Sakit',
            'permission' => 'Izin',
            'on_leave' => 'Cuti',
        ];

        return [
            $this->rowNumber,
            $attendance->date ? $attendance->date->format('d/m/Y') : '-',
            $attendance->teacher->name ?? '-',
            $attendance->teacher->nip ?? '-',
            $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-',
            $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-',
            $statusMap[$attendance->status] ?? $attendance->status,
            $attendance->notes ?? '-',
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
        $sheet->getStyle("E2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    /**
     * Set worksheet title
     */
    public function title(): string
    {
        return 'Laporan Absensi Guru';
    }
}
