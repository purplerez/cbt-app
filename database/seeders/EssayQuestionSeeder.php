<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Exam;
use Illuminate\Database\Seeder;

class EssayQuestionSeeder extends Seeder
{
     /**
      * Run the database seeds.
      * Seeder ini dibuat untuk testing case sensitivity pada jawaban essay
      */
     public function run(): void
     {
          $this->command->info('📝 Creating Essay Questions for Testing...');

          // Get first exam for testing
          $exam = Exam::first();

          if (!$exam) {
               $this->command->warn('No exams found. Please run ExamSeeder first.');
               return;
          }

          $this->command->info("Creating essay questions for exam: {$exam->title}");

          // Test Case 1: Simple one-word answer (UPPERCASE key)
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3', // Essay type
               'question_text' => 'Apa ibukota Indonesia?',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'JAKARTA', // Kunci jawaban UPPERCASE
               'points' => 10,
               'created_by' => 1,
          ]);

          // Test Case 2: Multiple words answer (UPPERCASE key)
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Siapa presiden pertama Indonesia?',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'SOEKARNO', // Kunci jawaban UPPERCASE
               'points' => 10,
               'created_by' => 1,
          ]);

          // Test Case 3: Phrase answer (Mixed Case key)
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Apa nama bendera Indonesia?',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'Merah Putih', // Kunci jawaban Mixed Case
               'points' => 10,
               'created_by' => 1,
          ]);

          // Test Case 4: Short sentence answer (lowercase key)
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Apa bahasa resmi Indonesia?',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'bahasa indonesia', // Kunci jawaban lowercase
               'points' => 10,
               'created_by' => 1,
          ]);

          // Test Case 5: Answer with special characters
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Apa motto Indonesia?',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'BHINNEKA TUNGGAL IKA', // Kunci jawaban dengan spasi
               'points' => 15,
               'created_by' => 1,
          ]);

          // Test Case 6: Scientific term (UPPERCASE key)
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Apa simbol kimia untuk air?',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'H2O', // Kunci jawaban dengan angka
               'points' => 10,
               'created_by' => 1,
          ]);

          // Test Case 7: Date/Number answer
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Kapan Indonesia merdeka? (format: DD-MM-YYYY)',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => '17-08-1945', // Kunci jawaban tanggal
               'points' => 10,
               'created_by' => 1,
          ]);

          // Test Case 8: Answer with punctuation
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3',
               'question_text' => 'Sebutkan julukan kota Bandung!',
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
               'answer_key' => 'Paris Van Java', // Kunci jawaban dengan spasi
               'points' => 10,
               'created_by' => 1,
          ]);

          $this->command->info('✅ Essay questions created successfully!');
          $this->command->info('');
          $this->command->info('📋 Test Cases Created:');
          $this->command->info('1. JAKARTA (UPPERCASE key) - Test dengan lowercase: jakarta');
          $this->command->info('2. SOEKARNO (UPPERCASE key) - Test dengan lowercase: soekarno');
          $this->command->info('3. Merah Putih (Mixed Case key) - Test dengan berbagai case');
          $this->command->info('4. bahasa indonesia (lowercase key) - Test dengan uppercase: BAHASA INDONESIA');
          $this->command->info('5. BHINNEKA TUNGGAL IKA (dengan spasi)');
          $this->command->info('6. H2O (dengan angka)');
          $this->command->info('7. 17-08-1945 (tanggal)');
          $this->command->info('8. Paris Van Java (dengan spasi)');
          $this->command->info('');
          $this->command->info('💡 Sistem sekarang sudah case-insensitive dan ignore punctuation!');
     }
}
