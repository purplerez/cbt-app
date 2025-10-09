<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class InstructionSheet implements FromArray, WithTitle, WithStyles, ShouldAutoSize
{
    protected $grades;

    public function __construct($grades)
    {
        $this->grades = $grades;
    }

    public function array(): array
    {
        $instructions = [
            ['PETUNJUK PENGISIAN TEMPLATE IMPORT SISWA'],
            [''],
            ['1. Informasi Umum:'],
            ['   - Semua kolom yang bertanda * wajib diisi'],
            ['   - Jangan mengubah format atau urutan kolom'],
            ['   - Pastikan data yang dimasukkan sesuai dengan format yang diminta'],
            [''],
            ['2. Penjelasan Kolom:'],
            ['   NIS:', '- Nomor Induk Siswa (angka)'],
            ['   Nama Lengkap:', '- Nama lengkap siswa'],
            ['   Kelas ID:', '- ID kelas yang tersedia:'],
        ];

        // Add available classes
        foreach ($this->grades as $id => $name) {
            $instructions[] = ['', "   {$id} = {$name}"];
        }

        $instructions = array_merge($instructions, [
            [''],
            ['   Jenis Kelamin:', '- Pilih L (Laki-laki) atau P (Perempuan)'],
            ['   Tempat Lahir:', '- Nama kota tempat lahir'],
            ['   Tanggal Lahir:', '- Format: YYYY-MM-DD (contoh: 2000-01-31)'],
            ['   Alamat:', '- Alamat lengkap siswa'],
            [''],
            ['3. Contoh Pengisian:'],
            ['   NIS: 12345'],
            ['   Nama: Budi Santoso'],
            ['   Kelas ID: 1'],
            ['   Jenis Kelamin: L'],
            ['   Tempat Lahir: Jakarta'],
            ['   Tanggal Lahir: 2000-01-01'],
            ['   Alamat: Jl. Contoh No. 123, Jakarta'],
            [''],
            ['4. Catatan Penting:'],
            ['   - File yang diupload harus dalam format .xlsx'],
            ['   - Maksimal 100 data siswa per file'],
            ['   - Pastikan tidak ada baris kosong di antara data'],
            [''],
            ['5. Jika terjadi error:'],
            ['   - Periksa kembali format data yang dimasukkan'],
            ['   - Pastikan semua kolom wajib telah diisi'],
            ['   - Perhatikan format tanggal dan jenis kelamin']
        ]);

        return $instructions;
    }

    public function title(): string
    {
        return 'Petunjuk Pengisian';
    }

    public function styles(Worksheet $sheet)
    {
        // Style for title
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ]
        ]);

        // Style for section headers
        $sheet->getStyle('A3:A8')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '4472C4']
            ]
        ]);

        // Add some color to the example section
        $sheet->getStyle('A19:A25')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA']
            ]
        ]);

        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
        ];
    }
}
