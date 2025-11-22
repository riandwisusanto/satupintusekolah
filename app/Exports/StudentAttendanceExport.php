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

class StudentAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $query;
    protected $filters;
    protected $rowNumber = 0;

    public function __construct($query, $filters = [])
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kelas',
            'Guru',
            'Nama Siswa',
            'Status',
            'Catatan',
        ];
    }

    public function map($attendance): array
    {
        $rows = [];
        
        foreach ($attendance->details as $detail) {
            $this->rowNumber++;
            
            $statusMap = [
                'present' => 'Hadir',
                'absent' => 'Tidak Hadir',
                'sick' => 'Sakit',
                'permission' => 'Izin',
            ];

            $rows[] = [
                $this->rowNumber,
                $attendance->date ? \Carbon\Carbon::parse($attendance->date)->format('d/m/Y') : '-',
                $attendance->classroom->name ?? '-',
                $attendance->teacher->name ?? '-',
                $detail->student->name ?? '-',
                $statusMap[$detail->status] ?? $detail->status,
                $detail->note ?? '-',
            ];
        }

        return count($rows) > 0 ? $rows[0] : [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '001f3f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $lastRow = $this->rowNumber + 1;
        $sheet->getStyle("A1:G{$lastRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("F2:F{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }

    public function title(): string
    {
        return 'Laporan Absensi Siswa';
    }
}
