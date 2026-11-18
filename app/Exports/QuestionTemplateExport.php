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
                'Ibukota negara Indonesia adalah...',
                'Jakarta',
                'Surabaya',
                'Bandung',
                'Medan',
                'A',
                '1',
                '// Contoh Pilihan Ganda (1 jawaban)'
            ],
            [
                'Kota yang terletak di Pulau Jawa adalah...',
                'Jakarta',
                'Bandung',
                'Medan',
                'Makassar',
                'A,B',
                '1',
                '// Contoh PG Kompleks (jawaban dipisah koma)'
            ],
            [
                'Matahari terbit dari sebelah barat.',
                'Benar',
                'Salah',
                '',
                '',
                'B',
                '1',
                '// Contoh Benar/Salah (cukup isi pilihan A dan B)'
            ],
            [
                '// === PETUNJUK PENGISIAN ===',
                '// 1. Hapus baris contoh atau ganti dengan soal Anda',
                '// 2. Pilihan jawaban hanya A s/d D',
                '// 3. True/False cukup isi pilihan A dan B',
                '',
                '',
                '',
                '// Baris diawali // otomatis dilewati sistem'
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
