<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TextAlignment;

class WordTemplateService
{
    /**
     * Generate Word template with sample questions
     * Template format HARUS sesuai dengan yang bisa di-parse oleh WordQuestionImportService
     */
    public function generateTemplate()
    {
        try {
            $phpWord = new PhpWord();

            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setTitle('Template Soal Ujian');
            $properties->setCreator('CBT Application');

            // Add section
            $section = $phpWord->addSection();

            if (!$section) {
                throw new \Exception('Failed to create section');
            }

            // ===== HEADER =====
            $section->addText('TEMPLATE SOAL UJIAN', [
                'size' => 18,
                'bold' => true,
            ]);

            $section->addText('Format Penulisan Soal untuk Import ke Sistem', [
                'size' => 11,
                'italic' => true,
            ]);

            $section->addText('');
            $section->addText('PANDUAN PENTING:', [
                'size' => 12,
                'bold' => true,
                'color' => 'C00000',
            ]);

            $section->addText('• Soal dimulai dengan nomor: 1., 2., 3., dst (diikuti spasi)', ['size' => 11]);
            $section->addText('• Pilihan jawaban dimulai dengan: A., B., C., D., E.', ['size' => 11]);
            $section->addText('• Soal dengan 2 pilihan (A, B) dianggap Benar/Salah', ['size' => 11]);
            $section->addText('• Soal dengan 3-5 pilihan (A s/d E) dianggap Pilihan Ganda', ['size' => 11]);
            $section->addText('• Kunci jawaban ditulis di bawah soal: Kunci Jawaban: A (atau Kunci Jawaban: A,B,C untuk multi jawaban)', ['size' => 11]);
            $section->addText('• JANGAN ada baris kosong antara pertanyaan dan pilihan', ['size' => 11]);

            $section->addText('');
            $section->addText('');

            // ===== CONTOH 1: PILIHAN GANDA =====
            $section->addText('CONTOH 1: PILIHAN GANDA', [
                'size' => 12,
                'bold' => true,
                'color' => '0070C0',
            ]);

            $section->addText('Tuliskan soal Anda di bawah ini (COPY format ini):', [
                'size' => 10,
                'italic' => true,
            ]);

            $section->addText('');

            // Format soal 1 - EXACT format untuk parser
            $section->addText('1. Apa itu fotosintesis?', ['size' => 11]);
            $section->addText('A. Proses pembentukan makanan dari cahaya matahari', ['size' => 11]);
            $section->addText('B. Proses pernapasan sel', ['size' => 11]);
            $section->addText('C. Proses pencernaan makanan', ['size' => 11]);
            $section->addText('D. Proses pembuangan limbah', ['size' => 11]);
            $section->addText('E. Proses pembentukan energi seluler', ['size' => 11]);

            $section->addText('');
            $section->addText('Kunci Jawaban: A', [
                'size' => 10,
                'italic' => true,
                'color' => '70AD47',
            ]);

            $section->addText('');
            $section->addText('');

            // ===== CONTOH 2: PILIHAN GANDA KOMPLEKS =====
            $section->addText('CONTOH 2: PILIHAN GANDA KOMPLEKS (Multiple Answers)', [
                'size' => 12,
                'bold' => true,
                'color' => '0070C0',
            ]);

            $section->addText('Tuliskan soal Anda di bawah ini (bisa ada lebih dari 1 jawaban benar):', [
                'size' => 10,
                'italic' => true,
            ]);

            $section->addText('');

            // Format soal 2
            $section->addText('2. Pernyataan mana yang BENAR tentang fotosintesis?', ['size' => 11]);
            $section->addText('A. Terjadi di kloroplas tumbuhan', ['size' => 11]);
            $section->addText('B. Memerlukan cahaya matahari', ['size' => 11]);
            $section->addText('C. Menghasilkan oksigen dan glukosa', ['size' => 11]);
            $section->addText('D. Merupakan proses katabolisme', ['size' => 11]);
            $section->addText('E. Berlangsung dengan bantuan klorofil', ['size' => 11]);

            $section->addText('');
            $section->addText('Kunci Jawaban: A, B, C, E', [
                'size' => 10,
                'italic' => true,
                'color' => '70AD47',
            ]);

            $section->addText('');
            $section->addText('');

            // ===== CONTOH 3: BENAR/SALAH =====
            $section->addText('CONTOH 3: BENAR/SALAH', [
                'size' => 12,
                'bold' => true,
                'color' => '0070C0',
            ]);

            $section->addText('Soal dengan hanya 2 pilihan (A, B) otomatis jadi Benar/Salah:', [
                'size' => 10,
                'italic' => true,
            ]);

            $section->addText('');

            // Format soal 3
            $section->addText('3. Fotosintesis menghasilkan oksigen. Benar atau Salah?', ['size' => 11]);
            $section->addText('A. Benar', ['size' => 11]);
            $section->addText('B. Salah', ['size' => 11]);

            $section->addText('');
            $section->addText('Kunci Jawaban: A', [
                'size' => 10,
                'italic' => true,
                'color' => '70AD47',
            ]);

            $section->addText('');
            $section->addText('');

            // ===== INSTRUKSI UNTUK PENGGUNA =====
            $section->addText('');
            $section->addText(str_repeat('=', 80), ['size' => 10]);
            $section->addText('');
            $section->addText('CARA MENGGUNAKAN TEMPLATE INI:', [
                'size' => 12,
                'bold' => true,
                'color' => 'C00000',
            ]);

            $instructions = [
                'Hapus atau ganti contoh soal di atas (1, 2, 3) dengan soal Anda',
                'Tulis soal Anda mulai dari nomor 1 dengan format yang sama',
                'Pastikan TIDAK ADA BARIS KOSONG antara soal dan pilihan jawaban',
                'Gunakan format: A., B., C., D., E. untuk pilihan jawaban (maksimal 5 pilihan)',
                'Tulis kunci jawaban tepat di bawah pilihan, contoh: Kunci Jawaban: A',
                'Untuk Pilihan Ganda Kompleks (multi jawaban), pisahkan dengan koma: Kunci Jawaban: A, B, E',
                'Simpan file ini sebagai .docx lalu upload ke sistem CBT',
            ];

            foreach ($instructions as $instruction) {
                $section->addText($instruction, [
                    'size' => 11,
                    'indentation' => ['left' => 360],
                ]);
            }

            return $phpWord;

        } catch (\Exception $e) {
            Log::error('WordTemplateService Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
