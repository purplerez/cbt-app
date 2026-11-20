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
                'Semarang',
                'A',
                '1',
                '// Contoh Pilihan Ganda (1 jawaban)'
            ],
            [
                'Kota yang terletak di Pulau Jawa adalah...',
                'Jakarta',
                'Bandung',
                'Surabaya',
                'Medan',
                'Makassar',
                'A,B,C',
                '1',
                '// Contoh PG Kompleks (jawaban dipisah koma)'
            ],
            [
                'Matahari terbit dari sebelah barat.',
                'Benar',
                'Salah',
                '',
                '',
                '',
                'B',
                '1',
                '// Contoh Benar/Salah (cukup isi pilihan A dan B)'
            ],
            [
                '// === PETUNJUK PENGISIAN ===',
                '// 1. Hapus baris contoh atau ganti dengan soal Anda',
                '// 2. Pilihan jawaban A s/d E (opsional jika hanya A-D)',
                '// 3. True/False cukup isi pilihan A dan B',
                '',
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
            'PILIHAN E',
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
            'F' => 20,  // Pilihan E
            'G' => 15,  // Kunci Jawaban
            'H' => 8,   // Poin
            'I' => 30,  // Petunjuk
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header
        $sheet->getStyle('A1:I1')->applyFromArray([
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
        $sheet->getStyle('A2:I4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);

        // Style instruction row
        $sheet->getStyle('A5:I5')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF3C7'],
            ],
        ]);

        return [];
    }
}
