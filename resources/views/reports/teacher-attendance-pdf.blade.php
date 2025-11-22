<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi Guru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            font-size: 10px;
        }
        table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 10px;
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
        .status-badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-present { background-color: #d4edda; color: #155724; }
        .status-sick { background-color: #fff3cd; color: #856404; }
        .status-permission { background-color: #d1ecf1; color: #0c5460; }
        .status-leave { background-color: #e2e3e5; color: #383d41; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ABSENSI GURU</h2>
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
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 25%;">Nama Guru</th>
                <th style="width: 15%;">NIP</th>
                <th style="width: 10%;">Waktu Masuk</th>
                <th style="width: 10%;">Waktu Keluar</th>
                <th style="width: 13%;">Status</th>
                <th style="width: 10%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $attendance->date->format('d/m/Y') }}</td>
                    <td>{{ $attendance->teacher->name ?? '-' }}</td>
                    <td>{{ $attendance->teacher->nip ?? '-' }}</td>
                    <td style="text-align: center;">
                        {{ $attendance->time_in ? \Carbon\Carbon::parse($attendance->time_in)->format('H:i') : '-' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $attendance->time_out ? \Carbon\Carbon::parse($attendance->time_out)->format('H:i') : '-' }}
                    </td>
                    <td style="text-align: center;">
                        @php
                            $statusMap = [
                                'check_in' => ['label' => 'Masuk', 'class' => 'status-present'],
                                'check_out' => ['label' => 'Pulang', 'class' => 'status-present'],
                                'sick' => ['label' => 'Sakit', 'class' => 'status-sick'],
                                'permission' => ['label' => 'Izin', 'class' => 'status-permission'],
                                'on_leave' => ['label' => 'Cuti', 'class' => 'status-leave'],
                            ];
                            $status = $statusMap[$attendance->status] ?? ['label' => $attendance->status, 'class' => ''];
                        @endphp
                        <span class="status-badge {{ $status['class'] }}">{{ $status['label'] }}</span>
                    </td>
                    <td>{{ $attendance->note ?? '-' }}</td>
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
                <strong>Total Record</strong>
                <span>{{ $summary['total_records'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Hadir</strong>
                <span>{{ $summary['present_days'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Tidak Hadir</strong>
                <span>{{ $summary['absent_days'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Sakit</strong>
                <span>{{ $summary['sick_days'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Izin</strong>
                <span>{{ $summary['permission_days'] }}</span>
            </div>
            <div class="summary-item">
                <strong>Cuti</strong>
                <span>{{ $summary['leave_days'] }}</span>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>
