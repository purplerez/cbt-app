<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class QuestionTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        return [
            [
                'Ibukota Indonesia adalah...',
                'Jakarta',
                'Surabaya',
                'Bandung',
                'Medan',
                'A',
                '1',
                '// Contoh soal Pilihan Ganda'
            ],
            [
                'Jakarta adalah ibukota Indonesia',
                'Benar',
                'Salah',
                '',
                '',
                'A',
                '1',
                '// Contoh soal True/False'
            ],
            [
                'Jelaskan 3 alasan mengapa Jakarta dipilih sebagai ibukota Indonesia!',
                '',
                '',
                '',
                '',
                'Alasan: 1) Lokasi strategis 2) Pusat ekonomi 3) Sejarah',
                '2',
                '// Contoh soal Essay'
            ],
            [
                '// PETUNJUK PENGISIAN:',
                '1. Hapus contoh soal di atas',
                '2. Isi soal mulai dari sini',
                '3. Pilihan Ganda: isi semua pilihan',
                '4. True/False: isi A dan B saja',
                '5. Essay: kosongkan semua pilihan',
                '',
                '// Kunci jawaban: A,B,C,D atau A,B untuk multi jawaban'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'SOAL',
            'PILIHAN A',
            'PILIHAN B',
            'PILIHAN C',
            'PILIHAN D',
            'KUNCI JAWABAN',
            'POIN',
            'PETUNJUK'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 45,  // Soal
            'B' => 20,  // Pilihan A
            'C' => 20,  // Pilihan B
            'D' => 20,  // Pilihan C
            'E' => 20,  // Pilihan D
            'F' => 15,  // Kunci Jawaban
            'G' => 8,   // Poin
            'H' => 30,  // Petunjuk
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
        ]);

        // Style example rows
        $sheet->getStyle('A2:H4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // Style instruction row
        $sheet->getStyle('A5:H5')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'],
            ],
        ]);

        return [];
    }
}
