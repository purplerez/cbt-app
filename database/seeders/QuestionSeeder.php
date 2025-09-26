<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Exam;
use App\Models\QuestionTypes;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
     public function run(): void
     {
          // Get available exams
          $exams = Exam::all();

          if ($exams->isEmpty()) {
               $this->command->warn('No exams found. Please run ExamSeeder first.');
               return;
          }

          foreach ($exams as $exam) {
               // Create sample questions for each exam
               $this->createQuestionsForExam($exam);
          }
     }

     private function createQuestionsForExam($exam)
     {
          $questionsCount = 3; // Batasi hanya 3 soal untuk uji coba

          for ($i = 1; $i <= $questionsCount; $i++) {
               // Create different types of questions
               $questionType = $i % 4; // 0=Essay, 1=Multiple Choice, 2=True/False, 3=Fill in the Blank

               switch ($questionType) {
                    case 0: // Essay
                         $this->createEssayQuestion($exam, $i);
                         break;
                    case 1: // Multiple Choice
                         $this->createMultipleChoiceQuestion($exam, $i);
                         break;
                    case 2: // True/False
                         $this->createTrueFalseQuestion($exam, $i);
                         break;
                    case 3: // Complex Multiple Choice
                         $this->createComplexMultipleChoiceQuestion($exam, $i);
                         break;
               }
          }
     }

     private function createMultipleChoiceQuestion($exam, $number)
     {
          $choices = [
               'A' => $this->getRandomChoice($number, 'A'),
               'B' => $this->getRandomChoice($number, 'B'),
               'C' => $this->getRandomChoice($number, 'C'),
               'D' => $this->getRandomChoice($number, 'D'),
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '1', // Use string value for ENUM
               'question_text' => "Soal pilihan ganda nomor {$number} untuk ujian {$exam->title}. Manakah dari pernyataan berikut yang paling tepat?",
               'choices' => json_encode($choices),
               'answer_key' => 'A', // Store as simple string
               'points' => 10,
               'created_by' => 1, // Assuming admin user ID is 1
          ]);
     }

     private function createComplexMultipleChoiceQuestion($exam, $number)
     {
          $choices = [
               'A' => "Pernyataan A untuk soal {$number}",
               'B' => "Pernyataan B untuk soal {$number}",
               'C' => "Pernyataan C untuk soal {$number}",
               'D' => "Pernyataan D untuk soal {$number}",
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '1', // Use string value for ENUM
               'question_text' => "Soal pilihan ganda kompleks nomor {$number} untuk ujian {$exam->title}. Pilih semua jawaban yang benar:",
               'choices' => json_encode($choices),
               'answer_key' => 'A,C', // Store as comma-separated string
               'points' => 15,
               'created_by' => 1,
          ]);
     }

     private function createTrueFalseQuestion($exam, $number)
     {
          $choices = [
               'T' => 'Benar',
               'F' => 'Salah',
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '2', // Use string value for ENUM
               'question_text' => "Soal benar/salah nomor {$number} untuk ujian {$exam->title}. Pernyataan berikut ini adalah benar.",
               'choices' => json_encode($choices),
               'answer_key' => 'T', // Store as simple string
               'points' => 5,
               'created_by' => 1,
          ]);
     }

     private function createEssayQuestion($exam, $number)
     {
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '0', // Essay type according to migration
               'question_text' => "Soal essay nomor {$number} untuk ujian {$exam->title}. Jelaskan secara detail mengenai topik yang telah dipelajari dan berikan contoh yang relevan.",
               'choices' => null,
               'answer_key' => 'Jawaban essay yang menjelaskan topik secara detail dengan contoh yang relevan.',
               'points' => 25,
               'created_by' => 1,
          ]);
     }

     private function getRandomChoice($number, $option)
     {
          $choices = [
               'A' => [
                    "Pilihan A yang merupakan jawaban pertama untuk soal {$number}",
                    "Opsi A dengan penjelasan detail untuk nomor {$number}",
                    "Alternatif jawaban A untuk pertanyaan {$number}",
               ],
               'B' => [
                    "Pilihan B yang merupakan jawaban kedua untuk soal {$number}",
                    "Opsi B dengan penjelasan detail untuk nomor {$number}",
                    "Alternatif jawaban B untuk pertanyaan {$number}",
               ],
               'C' => [
                    "Pilihan C yang merupakan jawaban ketiga untuk soal {$number}",
                    "Opsi C dengan penjelasan detail untuk nomor {$number}",
                    "Alternatif jawaban C untuk pertanyaan {$number}",
               ],
               'D' => [
                    "Pilihan D yang merupakan jawaban keempat untuk soal {$number}",
                    "Opsi D dengan penjelasan detail untuk nomor {$number}",
                    "Alternatif jawaban D untuk pertanyaan {$number}",
               ],
          ];

          return $choices[$option][array_rand($choices[$option])];
     }
}
