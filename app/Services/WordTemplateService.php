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

            $section->addText('1. Soal dimulai dengan nomor: 1., 2., 3., dst (diikuti spasi)', ['size' => 11]);
            $section->addText('2. Pilihan jawaban dimulai dengan: A., B., C., D.', ['size' => 11]);
            $section->addText('3. Soal tanpa pilihan dianggap Essay (tidak perlu kunci jawaban)', ['size' => 11]);
            $section->addText('4. Soal dengan 2 pilihan (A, B) dianggap Benar/Salah', ['size' => 11]);
            $section->addText('5. Soal dengan 3-4 pilihan dianggap Pilihan Ganda', ['size' => 11]);
            $section->addText('6. JANGAN ada baris kosong antara pertanyaan dan pilihan', ['size' => 11]);
            $section->addText('7. Tulis ANSWER KEY di bagian bawah dengan format: JAWAB:[nomor soal]=[huruf jawaban]', ['size' => 11]);
            $section->addText('8. Untuk Pilihan Ganda Kompleks, pisahkan dengan koma: JAWAB:2=A,B,C', ['size' => 11]);

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

            $section->addText('');
            $section->addText('Kunci Jawaban: A, B, C', [
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

            // ===== CONTOH 4: ESSAY =====
            $section->addText('CONTOH 4: ESSAY/ISIAN SINGKAT', [
                'size' => 12,
                'bold' => true,
                'color' => '0070C0',
            ]);

            $section->addText('Soal tanpa pilihan jawaban (A, B, C, D) dianggap Essay:', [
                'size' => 10,
                'italic' => true,
            ]);

            $section->addText('');

            // Format soal 4
            $section->addText('4. Jelaskan proses fotosintesis!', ['size' => 11]);

            $section->addText('');
            $section->addText('(Essay tidak memiliki kunci jawaban otomatis, dinilai manual)', [
                'size' => 10,
                'italic' => true,
            ]);

            $section->addText('');
            $section->addText('');

            // ===== BAGIAN KUNCI JAWABAN =====
            $section->addText('');
            $section->addText(str_repeat('=', 80), ['size' => 10]);
            $section->addText('');

            $section->addText('KUNCI JAWABAN - COPY FORMAT INI DI BAWAH SEMUA SOAL', [
                'size' => 12,
                'bold' => true,
                'color' => 'C00000',
            ]);

            $section->addText('');
            $section->addText('Format untuk Pilihan Ganda Single Answer:', [
                'size' => 11,
                'bold' => true,
            ]);
            $section->addText('JAWAB:1=A', ['size' => 11]);

            $section->addText('');
            $section->addText('Format untuk Pilihan Ganda Kompleks (multiple answers):', [
                'size' => 11,
                'bold' => true,
            ]);
            $section->addText('JAWAB:2=A,B,C', ['size' => 11]);

            $section->addText('');
            $section->addText('Format untuk Benar/Salah:', [
                'size' => 11,
                'bold' => true,
            ]);
            $section->addText('JAWAB:3=A', ['size' => 11]);

            $section->addText('');
            $section->addText('Format untuk Essay (kosongkan atau tidak perlu):', [
                'size' => 11,
                'bold' => true,
            ]);
            $section->addText('(Tidak perlu kunci jawaban untuk Essay)', ['size' => 11]);

            $section->addText('');
            $section->addText('');

            // ===== INSTRUKSI UNTUK PENGGUNA =====
            $section->addText('CARA MENGGUNAKAN TEMPLATE INI:', [
                'size' => 12,
                'bold' => true,
                'color' => 'C00000',
            ]);

            $instructions = [
                'Hapus semua contoh soal di atas (1, 2, 3, 4)',
                'Tulis soal Anda mulai dari nomor 1 dengan format yang sama',
                'Pastikan TIDAK ADA BARIS KOSONG antara soal dan pilihan jawaban',
                'Gunakan format: A., B., C., D. untuk pilihan (jangan yang lain)',
                'Setelah semua soal, buat section baru dengan judul "KUNCI JAWABAN"',
                'Tulis kunci jawaban dengan format: JAWAB:[nomor]=[jawaban]',
                'Untuk multiple answers, gunakan koma: JAWAB:2=A,B,C',
                'Simpan file ini sebagai .docx',
                'Upload ke sistem CBT',
                'Sistem akan otomatis mendeteksi jawaban dari format JAWAB:...',
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
