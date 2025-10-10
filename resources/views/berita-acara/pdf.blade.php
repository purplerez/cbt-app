<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara - {{ $beritaAcara->nomor_ba }}</title>
    <style>
        @page {
            margin: 2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .header h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 5px 0;
        }
        .header p {
            margin: 3px 0;
            font-size: 11pt;
        }
        .nomor-ba {
            text-align: center;
            font-weight: bold;
            margin: 20px 0;
            font-size: 13pt;
        }
        .content {
            margin: 20px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.info-table td {
            padding: 8px;
            vertical-align: top;
        }
        table.info-table td:first-child {
            width: 35%;
            font-weight: bold;
        }
        table.data-table {
            border: 1px solid #000;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .signature-box {
            display: inline-block;
            width: 45%;
            text-align: center;
            vertical-align: top;
        }
        .signature-box.left {
            margin-right: 10%;
        }
        .signature-space {
            height: 80px;
            margin: 20px 0;
        }
        .signature-name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            padding: 5px 10px;
        }
        .footer {
            margin-top: 30px;
            font-size: 10pt;
            text-align: center;
            color: #666;
        }
        .section-title {
            font-weight: bold;
            font-size: 13pt;
            margin-top: 20px;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        p {
            text-align: justify;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $beritaAcara->school->name ?? 'NAMA SEKOLAH' }}</h1>
        <p>{{ $beritaAcara->school->address ?? '' }}</p>
        <p>Telp: {{ $beritaAcara->school->contact ?? '-' }} | Email: {{ $beritaAcara->school->email ?? '-' }}</p>
    </div>

    <!-- Nomor BA -->
    <div class="nomor-ba">
        BERITA ACARA PELAKSANAAN UJIAN<br>
        Nomor: {{ $beritaAcara->nomor_ba }}
    </div>

    <!-- Opening Statement -->
    <div class="content">
        <p>
            Pada hari ini, {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('l') }}, 
            tanggal {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d F Y') }}, 
            telah dilaksanakan ujian dengan keterangan sebagai berikut:
        </p>
    </div>

    <!-- Exam Info -->
    <div class="section-title">I. INFORMASI UJIAN</div>
    <table class="info-table">
        <tr>
            <td>Jenis Ujian</td>
            <td>: {{ $beritaAcara->examType->title ?? '-' }}</td>
        </tr>
        @if($beritaAcara->exam)
        <tr>
            <td>Mata Pelajaran</td>
            <td>: {{ $beritaAcara->exam->title }}</td>
        </tr>
        @endif
        <tr>
            <td>Tanggal Pelaksanaan</td>
            <td>: {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Waktu Pelaksanaan</td>
            <td>: {{ $beritaAcara->waktu_mulai }} - {{ $beritaAcara->waktu_selesai }} WIB</td>
        </tr>
        <tr>
            <td>Tempat/Ruangan</td>
            <td>: {{ $beritaAcara->room->nama_ruangan ?? 'Berbagai Ruangan' }}</td>
        </tr>
    </table>

    <!-- Attendance Data -->
    <div class="section-title">II. DATA PESERTA UJIAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th style="text-align: center; width: 20%;">Jumlah</th>
                <th style="text-align: center; width: 20%;">Persentase</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Jumlah Peserta Terdaftar</td>
                <td style="text-align: center;">{{ $beritaAcara->jumlah_peserta_terdaftar }}</td>
                <td style="text-align: center;">100%</td>
            </tr>
            <tr>
                <td>Jumlah Peserta Hadir</td>
                <td style="text-align: center;">{{ $beritaAcara->jumlah_peserta_hadir }}</td>
                <td style="text-align: center;">{{ $beritaAcara->persentase_kehadiran }}%</td>
            </tr>
            <tr>
                <td>Jumlah Peserta Tidak Hadir</td>
                <td style="text-align: center;">{{ $beritaAcara->jumlah_peserta_tidak_hadir }}</td>
                <td style="text-align: center;">{{ $beritaAcara->jumlah_peserta_terdaftar > 0 ? round(($beritaAcara->jumlah_peserta_tidak_hadir / $beritaAcara->jumlah_peserta_terdaftar) * 100, 2) : 0 }}%</td>
            </tr>
        </tbody>
    </table>

    <!-- Proctors -->
    @if($beritaAcara->pengawas_users->isNotEmpty())
    <div class="section-title">III. PENGAWAS UJIAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">No</th>
                <th>Nama Pengawas</th>
                <th style="width: 30%;">Email/Kontak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($beritaAcara->pengawas_users as $index => $pengawas)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $pengawas->name }}</td>
                <td>{{ $pengawas->email }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- Conditions -->
    <div class="section-title">{{ $beritaAcara->pengawas_users->isNotEmpty() ? 'IV' : 'III' }}. KONDISI PELAKSANAAN</div>
    <table class="info-table">
        <tr>
            <td>Kondisi Pelaksanaan Ujian</td>
            <td>: <strong style="text-transform: capitalize;">{{ str_replace('_', ' ', $beritaAcara->kondisi_pelaksanaan) }}</strong></td>
        </tr>
        <tr>
            <td>Kondisi Ruangan</td>
            <td>: <strong style="text-transform: capitalize;">{{ $beritaAcara->kondisi_ruangan }}</strong></td>
        </tr>
        <tr>
            <td>Kondisi Peralatan</td>
            <td>: <strong style="text-transform: capitalize;">{{ $beritaAcara->kondisi_peralatan }}</strong></td>
        </tr>
    </table>

    @if($beritaAcara->kendala)
    <div style="margin-top: 15px;">
        <strong>Kendala/Masalah yang Terjadi:</strong>
        <p style="margin-left: 20px;">{{ $beritaAcara->kendala }}</p>
    </div>
    @endif

    @if($beritaAcara->catatan_khusus)
    <div style="margin-top: 15px;">
        <strong>Catatan Khusus:</strong>
        <p style="margin-left: 20px;">{{ $beritaAcara->catatan_khusus }}</p>
    </div>
    @endif

    <!-- Closing Statement -->
    <div class="content" style="margin-top: 30px;">
        <p>
            Demikian berita acara ini dibuat dengan sebenar-benarnya untuk dapat digunakan sebagaimana mestinya.
        </p>
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                    <div style="margin-bottom: 10px;">Pengawas 1</div>
                    <div class="signature-space"></div>
                    <div>
                        <span class="signature-name">
                            @if($beritaAcara->pengawas_users->count() > 0)
                                {{ $beritaAcara->pengawas_users->first()->name }}
                            @else
                                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                            @endif
                        </span>
                    </div>
                </td>
                <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                    <div style="margin-bottom: 10px;">
                        {{ $beritaAcara->school->city ?? 'Kota' }}, 
                        {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d F Y') }}
                    </div>
                    <div style="margin-bottom: 10px;">Kepala Sekolah</div>
                    <div class="signature-space"></div>
                    <div>
                        <span class="signature-name">
                            @if($beritaAcara->approver)
                                {{ $beritaAcara->approver->name }}
                            @else
                                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                            @endif
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB<br>
            Dokumen ini adalah hasil cetak resmi dari sistem {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
