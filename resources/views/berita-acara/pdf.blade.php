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
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
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
            font-size: 14pt;
        }
        .content {
            margin: 20px 0;
        }
        table {
            font-size : 12pt;
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.info-table td {
            padding: 2px;
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
            height: 50px;
            margin: 10px 0;
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

        .title {
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
        }

    </style>
</head>
<body>
    <!-- Header -->
    <!--div class="header">
        <h1>{{ $beritaAcara->school->name ?? 'NAMA SEKOLAH' }}</h1>
        <p>{{ $beritaAcara->school->address ?? '' }}</p>
        <p>Telp: {{ $beritaAcara->school->contact ?? '-' }} | Email: {{ $beritaAcara->school->email ?? '-' }}</p>
    </!div-->

    <!-- Nomor BA -->
    <table>
        <tr>
            <td style="width : 20%;"><img src="data:image/png;base64,{{ $logo }}"  alt="Logo Sekolah" style="width: 100px; height: auto;"></td>
            <td style="text-align: center;">
                <div class="title">
                    BERITA ACARA PELAKSANAAN <br>
                    {{ strtoupper($beritaAcara->examType->title) ?? '-' }}<br/>
                    TAHUN {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('Y') }}
                    {{-- Nomor: {{ $beritaAcara->nomor_ba }} --}}
                </div>
            </td>
        </tr>
    </table>


    <!-- Opening Statement -->
    <div class="content">
        <p>
            Pada hari ini, {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('l') }},
            tanggal {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d F Y') }}, di {{ $beritaAcara->school->name ?? 'NAMA SEKOLAH' }} Kab. Banyuwangi
            telah dilaksanakan {{ $beritaAcara->examType->title ?? '-' }}, dari pukul {{ $beritaAcara->waktu_mulai }} sampai dengan {{ $beritaAcara->waktu_selesai }}.
        </p>
    </div>

    <!-- Exam Info -->
    <div class="section-title"></div>
    <table class="info-table">
        <tr>
            <td>Madrasah </td>
            <td>: {{ $beritaAcara->school->name ?? 'NAMA SEKOLAH' }}</td>
        </tr>
        @if($beritaAcara->exam)
        <tr>
            <td>Mata Pelajaran</td>
            <td>: {{ $beritaAcara->exam->title }}</td>
        </tr>
        @endif
        <tr>
            <td>Ruang</td>
            <td>: {{ $beritaAcara->room->name }}</td>
        </tr>
        <tr>
            <td>Jumlah Peserta Seharusnya</td>
            <td>: {{ $beritaAcara->jumlah_peserta_terdaftar }}</td>
        </tr>
        <tr>
            <td>Jumlah Hadir (Ikut Ujian)</td>
            <td>: {{ $beritaAcara->jumlah_peserta_hadir }}</td>
        </tr>
        <tr>
            <td>Jumlah Tidak Hadir</td>
            <td>: {{ $beritaAcara->jumlah_peserta_tidak_hadir }}</td>
        </tr>
    </table>

    <!-- Attendance Data -->
    {{-- <div class="section-title">II. DATA PESERTA UJIAN</div>
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
                <td style="text-align: center;"></td>
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
    </table> --}}

    <!-- Conditions -->
    <div class="section-title">Catatan Selama Pelaksanaan : </div>
    <div class="content" style="padding : 1em; border : solid 1px black;">
        {{ str_replace('_', ' ', $beritaAcara->kondisi_pelaksanaan) }}
    </div>
    {{-- <table class="info-table">
        <tr>
            <td>Kondisi Pelaksanaan Ujian</td>
            <td>: <strong style="text-transform: capitalize;">{{ str_replace('_', ' ', $beritaAcara->kondisi_pelaksanaan) }}</strong></td>
        </tr> --}}
        {{-- <tr>
            <td>Kondisi Ruangan</td>
            <td>: <strong style="text-transform: capitalize;">{{ $beritaAcara->kondisi_ruangan }}</strong></td>
        </tr>
        <tr>
            <td>Kondisi Peralatan</td>
            <td>: <strong style="text-transform: capitalize;">{{ $beritaAcara->kondisi_peralatan }}</strong></td>
        </tr> --}}
    {{-- </table> --}}

    {{-- @if($beritaAcara->kendala)
    <div style="margin-top: 15px;">
        <strong>Kendala/Masalah yang Terjadi:</strong>
        <p style="margin-left: 20px;">{{ $beritaAcara->kendala }}</p>
    </div>
    @endif --}}

    {{-- @if($beritaAcara->catatan_khusus)
    <div style="margin-top: 15px;">
        <strong>Catatan Khusus:</strong>
        <p style="margin-left: 20px;">{{ $beritaAcara->catatan_khusus }}</p>
    </div>
    @endif --}}

    {{--  signature start --}}

    <!-- Signatures -->
    <div class="footer">
        <table style="width: 100%; border: none;">
            <tr>
                @foreach($beritaAcara->pengawas as $index => $namaPengawas)
                @if(!empty(trim($namaPengawas)))
                <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                    <div style="margin-bottom: 5px;">Pengawas {{ $index + 1 }}</div>
                    <div class="signature-space"></div>
                    <div>
                        <span class="signature-name">
                            {{ $namaPengawas }}

                        </span>
                    </div>
                </td>
                {{-- <td style="width: 50%; text-align: center; vertical-align: top; border: none;">
                    <div style="margin-bottom: 10px;">Pengawas II</div>
                    <div class="signature-space"></div>
                    <div>
                        <span class="signature-name">
                            @if($beritaAcara->pengawas_users->count() > 1)
                                {{ $beritaAcara->pengawas_users->get(1)->name }}
                            @else
                                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                            @endif
                        </span>
                    </div>
                </td> --}}
                @endif
                @endforeach

            </tr>
            <tr>
                <td colspan=2 style="text-align: center">
                    <div style="text-align: center; margin-top : 3rem;">
                        <div style="display: inline-block; text-align: center;">
                            <div> Banyuwangi , {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d F Y') }}</div>
                            <div style="margin-top: 5px;">Kepala Sekolah,</div>
                            <div class="signature-space"></div>
                        <div>
                    <span class="signature-name">
                        {{ $head ? '(' . $head->name . ')' : '' }}

                    </span>
                </div>
            </div>
        </div>
                </td>
            </tr>
        </table>


    </div>

{{-- signature end --}}


    <!-- Proctors -->
    {{-- @if($beritaAcara->pengawas_users->isNotEmpty()) --}}

    {{-- @endif --}}


    <!-- Closing Statement -->
    {{-- <div class="content" style="margin-top: 30px;">
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
                        {{ 'Banyuwangi' }},
                        {{ $beritaAcara->tanggal_pelaksanaan->translatedFormat('d F Y') }}
                    </div>
                    <div style="margin-bottom: 10px;">Kepala Sekolah</div>
                    <div class="signature-space"></div>
                    <div>
                        <span class="signature-name">

                            {{ $head ? '(' . $head->name . ')' : '' }}
                            {{-- @if($head)
                                {{ $head->name }}
                            @else
                                (&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;)
                            @endif --}}
                      {{-- </span>
                    </div>
                </td>
            </tr>
        </table>
    </div> --}}

    <!-- Footer -->
    <div class="footer">
        <p>
            Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB<br>
            Dokumen ini adalah hasil cetak resmi dari sistem {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
