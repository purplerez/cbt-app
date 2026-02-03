<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daftar Hadir Siswa - {{ $beritaAcara->nomor_ba }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.1;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        /* .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        } */
        /* .header h2 {
            font-size: 14pt;
            font-weight: bold;
            margin: 5px 0;
        } */
        .header p {
            /* margin: 3px 0; */
            font-size: 10pt;
        }
        .info-section {
            margin: 15px 0;
        }
        .info-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-section table td {
            padding: 4px;
            vertical-align: top;
        }
        .info-section table td:first-child {
            width: 30%;
            font-weight: bold;
        }
        .grade-section {
          /*  page-break-inside: avoid; */
            margin-top: 25px;
        }
        .grade-section:first-child {
            margin-top: 10px;
        }
        .grade-title {
            background-color: #f0f0f0;
            padding: 8px;
            font-weight: bold;
            font-size: 12pt;
            border: 1px solid #000;
            margin-bottom: 5px;
        }
        table.student-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.student-table th,
        table.student-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table.student-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        table.student-table td.no {
            width: 5%;
            text-align: center;
        }
        table.student-table td.nis {
            width: 15%;
            text-align: center;
        }
        table.student-table td.name {
            width: 40%;
        }
        table.student-table td.signature {
            width: 40%;
            min-height: 40px;
        }
        table.student-table tbody tr {
            page-break-inside: avoid;
        }
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signature-box {
            display: inline-block;
            width: 48%;
            text-align: center;
            vertical-align: top;
        }
        .signature-box.right {
            float: right;
        }
        .signature-space {
            height: 30px;
            margin: 10px 0;
        }
        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            padding: 5px 10px;
        }
        .summary {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #000;
            background-color: #f9f9f9;
        }
        .summary table {
            width: 100%;
        }
        .summary table td {
            padding: 5px;
        }
        .summary table td:first-child {
            width: 60%;
            font-weight: bold;
        }
        .summary table td:last-child {
            width: 40%;
            text-align: left;
            /* border-bottom: 1px solid #000; */
            padding-left: 10px;
        }
        .notes-section {
            margin-top: 20px;
            border: 1px solid #000;
            padding: 10px;
            min-height: 80px;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>


    <!-- Exam Info -->
    @if($studentsByGrade->isEmpty())

    <!-- Header -->
    <div class="header">
        {{-- <h1>{{ $beritaAcara->school->name ?? 'NAMA SEKOLAH' }}</h1>
        <p>{{ $beritaAcara->school->address ?? '' }}</p> --}}
        <h2 style="margin : 0">DAFTAR HADIR PESERTA </h2>
        <h2 style="margin : 0"> {{ strtoupper($beritaAcara->examType->title ?? '-') }}</h2>
        <h3 style="margin : 0">TAHUN {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('Y') }}</h3>
    </div>

        <div class="grade-section">
            <p style="text-align: center; padding: 20px; border: 1px solid #ccc;">
                Tidak ada data siswa untuk ruangan ini.
            </p>
        </div>
    @else
    @foreach($studentsByGrade as $gradeName => $students)
    <!-- Header -->
    <div class="header">
        {{-- <h1>{{ $beritaAcara->school->name ?? 'NAMA SEKOLAH' }}</h1>
        <p>{{ $beritaAcara->school->address ?? '' }}</p> --}}
        <h2 style="margin : 0">DAFTAR HADIR PESERTA </h2>
        <h2 style="margin : 0"> {{ strtoupper($beritaAcara->examType->title ?? '-') }}</h2>
        <h3 style="margin : 0">TAHUN {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('Y') }}</h3>
    </div>

    <div class="info-section">
        <table>
            {{-- <tr>
                <td>Jenis Ujian</td>
                <td>: {{ $beritaAcara->examType->title ?? '-' }}</td>
            </tr> --}}
            @if($beritaAcara->exam)
            <tr>
                <td>Mata Pelajaran</td>
                <td>: {{ $beritaAcara->exam->title }}</td>
            </tr>
            @endif
            <tr>
                <td>Hari/Tanggal</td>
                <td>: {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>: {{ $beritaAcara->waktu_mulai }} - {{ $beritaAcara->waktu_selesai }} WIB</td>
            </tr>
            <tr>
                <td>Ruangan</td>
                <td>: {{ $beritaAcara->room->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Kelas</td>
                <td>: {{ strtoupper($gradeName) }}</td>
            </tr>
            {{-- <tr>
                <td>Nomor Berita Acara</td>
                <td>: {{ $beritaAcara->nomor_ba }}</td>
            </tr> --}}
        </table>
    </div>

    <!-- Student Lists by Grade -->


            <div class="grade-section">
                {{-- <div class="grade-title">
                    KELAS:  ({{ $students->count() }} Siswa)
                </div> --}}

                <table class="student-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIS</th>
                            <th>Nama Lengkap</th>
                            <th colspan="2">Tanda Tangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $no = 1;
                         @endphp
                        @foreach($students as $index => $student)
                            <tr>
                                <td class="no">{{ $index + 1 }}</td>
                                <td class="nis">{{ $student->nis }}</td>
                                <td class="name">{{ $student->name }}</td>
                                <td class="signature">

                                    <!-- Space for manual signature -->
                                    @if($no % 2 == 1) {{ $no }} @endif &nbsp;
                                </td>
                                <td class="signature">
                                     @if($no % 2 == 0) {{ $no }} @endif &nbsp;
                                    <!-- Space for manual signature -->
                                </td>
                            </tr>
                            @php
                                $no++;
                            @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>


    <!-- Summary -->
    <div class="summary">
        <strong>REKAPITULASI KEHADIRAN:</strong>
        <table style="margin-top: 10px;">
            <tr>
                <td>Jumlah Siswa Terdaftar</td>
                <td>: ____________ siswa</td>
            </tr>
            <tr>
                <td>Jumlah Siswa Hadir</td>
                <td>: ____________ siswa</td>
            </tr>
            <tr>
                <td>Jumlah Siswa Tidak Hadir</td>
                <td>: ____________ siswa</td>
            </tr>
            {{-- <tr>
                <td>Persentase Kehadiran</td>
                <td>: ____________ %</td>
            </tr> --}}
        </table>
    </div>

    <!-- Notes Section -->
    <div class="notes-section">
        <strong>CATATAN:</strong>
        <div style="margin-top: 10px;">
            _____________________________________________________________________________
        </div>
        <div style="margin-top: 5px;">
            _____________________________________________________________________________
        </div>
        <div style="margin-top: 5px;">
            _____________________________________________________________________________
        </div>
    </div>

    {{--  signature  --}}

    <div class="section-title" style="margin-top : 30px; margin-bottom: 10px;">Yang membuat berita acara : </div>

    <table class="data-table" style="border : none; border-collapse: collapse; width: 100%;">
        {{-- <thead>
            <tr>
                <th style="width: 10%; text-align: center;">No</th>
                <th>Nama Pengawas</th>
                <th style="width: 30%;">Email/Kontak</th>
            </tr>
        </thead> --}}
        <tbody>
            @foreach($beritaAcara->pengawas as $index => $namaPengawas)
                @if(!empty(trim($namaPengawas)))
                <tr>
                    <td style="border: none; padding: 10px 6px;" width="17%">
                        @if ( $index + 1 == 1)
                            Pengawas
                        @else
                            Proktor
                        @endif
                    </td>
                    <td style="border: none; padding: 10px 6px; width : 55%;">
                        {{ strtoupper($namaPengawas) }}
                    </td>
                    <td style="border: none; padding: 10px 6px;" width="28%">
                       ( <!-- Kolom untuk tanda tangan --> .......................... )
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{--  end of signature --}}

    <!-- Print Info -->
    <div style="margin-top: 20px; padding-top: 10px; border-top: 1px solid #ccc; text-align: center; font-size: 9pt; color: #666; ">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB | Dokumen resmi dari {{ config('app.name') }}
    </div>
    <div style="page-break-before: always;"></div>
          @endforeach
    @endif
</body>
</html>
