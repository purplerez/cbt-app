<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kartu Peserta Ujian</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            color: black;
            padding: 3mm;
        }

        /* ===== PAGE ===== */
        @page {
            size: A4 portrait;
            margin: 3mm;
        }

        /* ===== TABLE LAYOUT ===== */
        table.cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3mm 3mm; /* horizontal | vertical */
        }

        td.card-cell {
            width: 50%;
            vertical-align: top;
            padding : 0;
        }

        /* ===== CARD ===== */
        .card {
            width: 100%;
            height: 60mm;
            border: 3px solid black;
            display: table;
            padding : 0px;
            /* table-layout: fixed; */
        }

        .card-row {
            display: table-row;
        }

        .card-left,
        .card-right {
            display: table-cell;
            vertical-align: middle;
        }

        /* ===== LEFT ===== */
        .card-left {
            width: 35%;
            /* background: #667eea; */
            color: black;
            text-align: center;
            padding: 3mm;
            /* border-right: 2px solid #5567d8; */
        }

        .school-name {
            /* font-size: 8pt; */
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.3;
            white-space: normal;
        }

        .exam-title {
            /* font-size: 6pt;
            margin-top: 2mm; */
        }

        /* ===== RIGHT ===== */
        .card-right {
            width: 65%;
            padding: 4mm 5mm;
        }

        .student-name {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 2mm;
            white-space: normal;
        }

        .info-row {
            /* font-size: 7pt; */
            /* margin-bottom: 1.5mm; */
        }

        .info-label {
            font-weight: bold;
            color: black;
            display: inline-block;
            width: 30%;
            vertical-align: top;
            /* border : 1px solid black; */
        }

        .email {
            color: black;
            display: inline-block;
            width: 60%;
            vertical-align: top;
            /* border : 1px solid black; */
            /* word-break: break-all; */
        }

        .password {
            font-family: "Courier New", monospace;
            font-weight: bold;
            color: #d32f2f;
            letter-spacing: 0.5px;
        }
        div {
            /* border : 1px solid black; */
        }

        .card-full {
            display: table-cell;
            width: 100%;
            vertical-align: top;
        }

    </style>
</head>

<body>

<table class="cards-table">
    <tr>
    @foreach ($students as $index => $student)
        <td class="card-cell">
            <div class="card" style="padding: 0px; ">
                <table width="100%" style="margin: 0;">
                    <tr >
                        <td width="20%" style=" padding-bottom : 10px;">
                            <img
                                src="data:image/png;base64,{{ $logo }}"
                                alt="Logo Sekolah"
                                style="width: 90%; height: auto;"
                            >
                        </td>
                        <td style="font-family: Times New Roman; font-size: 10pt; text-align: center; font-weight: bold;  padding-bottom : 10px;">
                            KARTU PESERTA <br/>
                            {{ strtoupper($examType->title ?? 'UJIAN') }} <br/>
                            TAHUN PELAJARAN 2025/2026
                        </td>
                    </tr>
                    <tr>
                        <td colspan=2 style="font-family: Times New Roman; font-size: 9pt; padding-top : 10px; border-top : solid 1px black;">
                            <div class="info-row">
                                <span class="info-label">NISN</span>
                                <span class="email">: {{ $student['email'] }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Nama</span>
                                <span class="email">:  {{ $student['name'] }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Tempat&Tgl Lahir</span>
                                <span class="email">:  {{ $student['name'] }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Sekolah</span>
                                <span class="email">:  {{ strtoupper($school->name ?? 'SEKOLAH') }}</span>
                            </div>

                            <div class="info-row">
                                <span class="info-label">Kelas</span>
                                : {{ $student['class'] }}
                            </div>

                        </td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td style="font-family: Times New Roman; font-size: 10pt; text-align: left;">
                            <div class="email" style="padding-left:40%;">Banyuwangi, {{ date('d F Y') }}</div>
                            <div class="email" style="padding-left:40%;">Kepala Sekolah </div>
                            <div class="email" style="padding-left:40%; margin-top : 10%;">{{ $headmaster->name }} </div>
                        </td>
                    </tr>
                </table>

            </div>

        </td>

        @if (($index + 1) % 2 == 0 && !$loop->last)
            </tr><tr>
        @endif
    @endforeach
    </tr>
</table>

</body>
</html>
