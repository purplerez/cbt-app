<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use App\Models\Grade;

class StudentsTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithMultipleSheets
{
    protected $grades;

    public function __construct()
    {
        $this->grades = Grade::pluck('name', 'id')->toArray();
    }

    public function sheets(): array
    {
        return [
            'Data Siswa' => $this,
            new InstructionSheet($this->grades)
        ];
    }

    public function array(): array
    {
        return [
            [
                '12345',                // NIS
                'Nama Siswa',           // Nama
                '1',                    // Kelas ID
                'L',                    // Jenis Kelamin
                'Jakarta',              // Tempat Lahir
                '2000-01-01',          // Tanggal Lahir
                'Jl. Contoh No. 123'    // Alamat
            ],
            [
                '12346',
                'Siswa Contoh 2',
                '1',
                'P',
                'Bandung',
                '2000-02-15',
                'Jl. Contoh No. 456'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'NIS *',
            'Nama Lengkap *',
            'Kelas ID *',
            'Jenis Kelamin (L/P) *',
            'Tempat Lahir *',
            'Tanggal Lahir (YYYY-MM-DD) *',
            'Alamat *'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15); // NIS
        $sheet->getColumnDimension('B')->setWidth(30); // Nama
        $sheet->getColumnDimension('C')->setWidth(12); // Kelas
        $sheet->getColumnDimension('D')->setWidth(20); // Gender
        $sheet->getColumnDimension('E')->setWidth(20); // Tempat Lahir
        $sheet->getColumnDimension('F')->setWidth(25); // Tanggal Lahir
        $sheet->getColumnDimension('G')->setWidth(40); // Alamat

        // Style for all cells
        $sheet->getStyle('A1:G100')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Header style
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '000000']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'font' => [
                'color' => ['rgb' => 'FFFFFF']
            ]
        ]);

        // Add validation for Gender column
        $validation = $sheet->getCell('D2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"L,P"');
        $validation->setPromptTitle('Pilih Jenis Kelamin');
        $validation->setPrompt('L = Laki-laki, P = Perempuan');
        $validation->setErrorTitle('Input Salah');
        $validation->setError('Pilih L atau P saja');

        // Add validation for Date column
        $dateValidation = $sheet->getCell('F2')->getDataValidation();
        $dateValidation->setType(DataValidation::TYPE_DATE);
        $dateValidation->setAllowBlank(false);
        $dateValidation->setShowInputMessage(true);
        $dateValidation->setShowErrorMessage(true);
        $dateValidation->setFormula1('DATE(1990,1,1)');
        $dateValidation->setFormula2('DATE(2020,12,31)');
        $dateValidation->setPromptTitle('Format Tanggal');
        $dateValidation->setPrompt('Gunakan format: YYYY-MM-DD');
        $dateValidation->setErrorTitle('Tanggal Tidak Valid');
        $dateValidation->setError('Masukkan tanggal dengan format yang benar (YYYY-MM-DD)');

        // Add validation for Class ID
        $classValidation = $sheet->getCell('C2')->getDataValidation();
        $classValidation->setType(DataValidation::TYPE_LIST);
        $classValidation->setAllowBlank(false);
        $classValidation->setShowInputMessage(true);
        $classValidation->setShowErrorMessage(true);
        $classValidation->setShowDropDown(true);
        $classValidation->setFormula1('"' . implode(',', array_keys($this->grades)) . '"');
        $classValidation->setPromptTitle('Pilih Kelas');
        $classValidation->setPrompt('Pilih ID Kelas yang tersedia');
        $classValidation->setErrorTitle('Kelas Tidak Valid');
        $classValidation->setError('Pilih ID Kelas yang tersedia dalam daftar');

        // Copy validations to the rest of the rows
        for ($i = 3; $i <= 100; $i++) {
            $sheet->getCell("D{$i}")->setDataValidation(clone $validation);
            $sheet->getCell("F{$i}")->setDataValidation(clone $dateValidation);
            $sheet->getCell("C{$i}")->setDataValidation(clone $classValidation);
        }

        // Add alternate row colors
        for ($i = 2; $i <= 100; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle("A{$i}:G{$i}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F2F2F2']
                    ]
                ]);
            }
        }

        // Freeze the header row
        $sheet->freezePane('A2');
    }
}
