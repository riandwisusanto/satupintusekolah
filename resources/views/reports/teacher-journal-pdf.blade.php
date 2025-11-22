<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Jurnal Guru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #001f3f;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            color: #001f3f;
        }
        .filter-info {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border-left: 4px solid #001f3f;
        }
        .filter-info p {
            margin: 3px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background-color: #001f3f;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f8ff;
            border: 1px solid #001f3f;
        }
        .summary h3 {
            margin-top: 0;
            color: #001f3f;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        .summary-item {
            padding: 8px;
            background-color: white;
            border-left: 3px solid #001f3f;
        }
        .summary-item strong {
            display: block;
            color: #666;
            font-size: 9px;
        }
        .summary-item span {
            display: block;
            font-size: 16px;
            font-weight: bold;
            color: #001f3f;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN JURNAL GURU</h2>
        <p>{{ config('app.name') }}</p>
    </div>

    <div class="filter-info">
        <p><strong>Filter Laporan:</strong></p>
        @if($filters['start_date'] && $filters['end_date'])
            <p>Periode: {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}</p>
        @elseif($filters['month'])
            <p>Bulan: {{ \Carbon\Carbon::parse($filters['month'] . '-01')->format('F Y') }}</p>
        @endif
        <p>Guru: {{ $filters['teacher_name'] }}</p>
        <p>Dicetak pada: {{ $generated_at }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 15%;">Nama Guru</th>
                <th style="width: 10%;">Kelas</th>
                <th style="width: 12%;">Mata Pelajaran</th>
                <th style="width: 18%;">Tema</th>
                <th style="width: 21%;">Kegiatan</th>
                <th style="width: 10%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journals as $index => $journal)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($journal->date)->format('d/m/Y') }}</td>
                    <td>{{ $journal->teacher->name ?? '-' }}</td>
                    <td>{{ $journal->classroom->name ?? '-' }}</td>
                    <td>
                        @if($journal->subjects->count() > 0)
                            {{ $journal->subjects->map(fn($js) => $js->subject->name ?? '-')->join(', ') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $journal->theme ?? '-' }}</td>
                    <td>{{ $journal->activity ?? '-' }}</td>
                    <td>{{ $journal->notes ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px;">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h3>Ringkasan</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <strong>Total Jurnal</strong>
                <span>{{ $summary['total_journals'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Jumlah Guru</strong>
                <span>{{ $summary['unique_teachers'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Jumlah Kelas</strong>
                <span>{{ $summary['unique_classes'] }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>
