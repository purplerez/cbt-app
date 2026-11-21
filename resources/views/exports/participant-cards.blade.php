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
            color: #000;
            padding: 4mm;
        }

        @page {
            size: A4 portrait;
            margin: 4mm;
        }

        table.cards-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 3mm 4mm;
        }

        td.card-cell {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .card {
            width: 100%;
            border: 1.5px solid #000;
            padding: 6px 8px;
            background: #fff;
            page-break-inside: avoid;
        }

        .card-header {
            width: 100%;
            border-bottom: 1.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 6px;
        }

        .card-header td {
            vertical-align: middle;
        }

        .title-kop {
            font-size: 9.5pt;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .school-kop {
            font-size: 8.5pt;
            font-weight: bold;
            text-align: center;
            margin-top: 1px;
            text-transform: uppercase;
        }

        .table-info {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
        }

        .table-info td {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .label-col {
            width: 32%;
            font-weight: bold;
        }

        .colon-col {
            width: 3%;
            text-align: center;
        }

        .val-col {
            width: 65%;
        }

        .cred-box {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            background-color: #f3f4f6;
            padding: 1px 4px;
            border: 1px solid #d1d5db;
            border-radius: 2px;
        }

        .password-val {
            color: #b91c1c;
        }

        .table-footer {
            width: 100%;
            margin-top: 4px;
            font-size: 8pt;
        }

        .ttd-box {
            text-align: right;
            vertical-align: top;
            padding-right: 4px;
        }
    </style>
</head>

<body>

<table class="cards-table">
    <tr>
    @foreach ($students as $index => $student)
        <td class="card-cell">
            <div class="card">
                <!-- KOP KARTU -->
                <table class="card-header">
                    <tr>
                        @if ($logo)
                            <td width="18%" align="center">
                                <img
                                    src="data:image/png;base64,{{ $logo }}"
                                    alt="Logo"
                                    style="max-width: 42px; max-height: 42px; width: auto; height: auto;"
                                >
                            </td>
                        @endif
                        <td width="{{ $logo ? '82%' : '100%' }}">
                            <div class="title-kop">
                                KARTU PESERTA UJIAN<br/>
                                {{ $examType->title ?? 'UJIAN BERSAMA' }}
                            </div>
                            <div class="school-kop">
                                {{ $school->name ?? 'MADRASAH' }}
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- DATA PESERTA -->
                <table class="table-info">
                    <tr>
                        <td class="label-col">No. Peserta (NIS)</td>
                        <td class="colon-col">:</td>
                        <td class="val-col"><strong>{{ $student['nis'] }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label-col">Nama Peserta</td>
                        <td class="colon-col">:</td>
                        <td class="val-col"><strong>{{ strtoupper($student['name']) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="label-col">Kelas</td>
                        <td class="colon-col">:</td>
                        <td class="val-col">{{ $student['class'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Tempat, Tgl Lahir</td>
                        <td class="colon-col">:</td>
                        <td class="val-col">{{ $student['ttl'] }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">Username</td>
                        <td class="colon-col">:</td>
                        <td class="val-col"><span class="cred-box">{{ $student['email'] }}</span></td>
                    </tr>
                    <tr>
                        <td class="label-col">Password</td>
                        <td class="colon-col">:</td>
                        <td class="val-col"><span class="cred-box password-val">{{ $student['password'] }}</span></td>
                    </tr>
                </table>

                <!-- TANDA TANGAN -->
                <table class="table-footer">
                    <tr>
                        <td width="45%" style="vertical-align: bottom; font-size: 7pt; color: #555;">
                            <em>*Simpan kartu ini & bawa saat ujian</em>
                        </td>
                        <td width="55%" class="ttd-box">
                            <div>{{ now()->translatedFormat('d F Y') }}</div>
                            <div style="font-weight: bold; margin-bottom: 22px;">Kepala Madrasah,</div>
                            <div style="font-weight: bold; text-decoration: underline;">
                                {{ $headmaster?->name ?? '....................................' }}
                            </div>
                            @if (!empty($headmaster?->nip))
                                <div style="font-size: 7.5pt;">NIP. {{ $headmaster->nip }}</div>
                            @endif
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
