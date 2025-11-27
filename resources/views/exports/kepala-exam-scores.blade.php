<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daftar Nilai Ujian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .school-info {
            font-weight: bold;
        }
        .exam-info {
            font-weight: bold;
        }
        .grade-info {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table thead {
            background-color: #f0f0f0;
        }
        table th {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        table td {
            border: 1px solid #333;
            padding: 8px;
            font-size: 11px;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR NILAI UJIAN</h1>
        @if($school)
            <p class="school-info">{{ $school->name }}</p>
        @endif
        @if($exam)
            <p class="exam-info">Mata Pelajaran: {{ $exam->title }}</p>
        @endif
        @if($grade)
            <p class="grade-info">Kelas: {{ $grade->name }}</p>
        @endif
        <p>Total Skor Maksimal: {{ $totalPossibleScore }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 12%;">NIS</th>
                <th style="width: 30%;">Nama Siswa</th>
                <th style="width: 15%;">Kelas</th>
                <th class="text-center" style="width: 15%;">Nilai</th>
                <th class="text-right" style="width: 15%;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            @forelse($scores as $score)
                <tr>
                    <td class="text-center">{{ $score['no'] }}</td>
                    <td>{{ $score['nis'] }}</td>
                    <td>{{ $score['student_name'] }}</td>
                    <td>{{ $score['grade_name'] }}</td>
                    <td class="text-center">{{ $score['total_score'] }}/{{ $score['total_possible'] }}</td>
                    <td class="text-right">{{ $score['percentage'] }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data nilai</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Tanggal Cetak: {{ now()->format('d-m-Y H:i:s') }}</p>
    </div>
</body>
</html>
