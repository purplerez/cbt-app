<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\Exam;
use App\Models\QuestionTypes;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class QuestionSeeder extends Seeder
{
     public function run(): void
     {
          $this->command->info('📝 Creating storage directories for images...');
          $this->createStorageDirectories();

          $this->command->info('🎨 Creating sample images...');
          $this->createSampleImages();

          // Get available exams
          $exams = Exam::all();

          if ($exams->isEmpty()) {
               $this->command->warn('No exams found. Please run ExamSeeder first.');
               return;
          }

          foreach ($exams as $exam) {
               $this->command->info("Creating questions for exam: {$exam->title}");
               // Create sample questions for each exam
               $this->createQuestionsForExam($exam);
          }

          $this->command->info('✅ Questions seeded successfully!');
     }

     private function createQuestionsForExam($exam)
     {
          $questionsCount = 5; // Update: 5 soal untuk testing yang lebih komprehensif

          for ($i = 1; $i <= $questionsCount; $i++) {
               // Create different types of questions
               // 0=Multiple Choice, 1=MC with Images, 2=True/False, 3=Complex MC, 4=Essay
               $questionType = $i % 5;

               switch ($questionType) {
                    case 0: // Multiple Choice (no image)
                         $this->createMultipleChoiceQuestion($exam, $i);
                         break;
                    case 1: // Multiple Choice with Question Image
                         $this->createMultipleChoiceWithQuestionImage($exam, $i);
                         break;
                    case 2: // Multiple Choice with Choice Images
                         $this->createMultipleChoiceWithChoiceImages($exam, $i);
                         break;
                    case 3: // Complex Multiple Choice
                         $this->createComplexMultipleChoiceQuestion($exam, $i);
                         break;
                    case 4: // Essay
                         $this->createEssayQuestion($exam, $i);
                         break;
               }
          }
     }

     /**
      * Create storage directories for images
      */
     private function createStorageDirectories(): void
     {
          Storage::disk('public')->makeDirectory('question_images');
          Storage::disk('public')->makeDirectory('choice_images');
     }

     /**
      * Create sample images for testing
      */
     private function createSampleImages(): void
     {
          // Create sample question images
          for ($i = 1; $i <= 5; $i++) {
               $this->createDummyImage("question_images/question_{$i}.png", "Soal {$i}", 800, 400);
          }

          // Create sample choice images
          for ($i = 1; $i <= 10; $i++) {
               $this->createDummyImage("choice_images/choice_{$i}.png", "Pilihan {$i}", 400, 300);
          }
     }

     /**
      * Create a dummy image with text
      */
     private function createDummyImage(string $path, string $text, int $width = 400, int $height = 300): void
     {
          $fullPath = storage_path('app/public/' . $path);

          // Create directory if it doesn't exist
          $directory = dirname($fullPath);
          if (!File::exists($directory)) {
               File::makeDirectory($directory, 0755, true);
          }

          // Check if GD extension is available
          if (!extension_loaded('gd')) {
               $this->command->warn("⚠️  GD extension not available. Skipping image: {$path}");
               return;
          }

          // Create image
          $image = imagecreatetruecolor($width, $height);

          // Allocate colors
          $bgColor = imagecolorallocate($image, 245, 245, 245);
          $textColor = imagecolorallocate($image, 50, 50, 50);
          $borderColor = imagecolorallocate($image, 150, 150, 150);

          // Fill background
          imagefill($image, 0, 0, $bgColor);

          // Draw border
          imagerectangle($image, 0, 0, $width - 1, $height - 1, $borderColor);

          // Add text
          $fontSize = 5;
          $textWidth = imagefontwidth($fontSize) * strlen($text);
          $textHeight = imagefontheight($fontSize);
          $x = ($width - $textWidth) / 2;
          $y = ($height - $textHeight) / 2;

          imagestring($image, $fontSize, $x, $y, $text, $textColor);

          // Add watermark
          $watermark = "Sample Image";
          $wmWidth = imagefontwidth(2) * strlen($watermark);
          $wmX = ($width - $wmWidth) / 2;
          $wmY = $height - 30;
          imagestring($image, 2, $wmX, $wmY, $watermark, $textColor);

          // Save image
          imagepng($image, $fullPath);
          imagedestroy($image);
     }

     /**
      * Multiple Choice with Question Image (using same pattern as original)
      */
     private function createMultipleChoiceWithQuestionImage($exam, $number)
     {
          $choices = [
               '0' => $this->getRandomChoice($number, 'A'),
               '1' => $this->getRandomChoice($number, 'B'),
               '2' => $this->getRandomChoice($number, 'C'),
               '3' => $this->getRandomChoice($number, 'D'),
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '0', // Single answer multiple choice
               'question_text' => "Perhatikan gambar! Soal pilihan ganda nomor {$number} untuk ujian {$exam->title}. Manakah dari pernyataan berikut yang paling tepat?",
               'question_image' => 'question_images/question_' . (($number % 5) + 1) . '.png',
               'choices' => json_encode($choices),
               'choices_images' => null,
               'answer_key' => '[0]', // Store as JSON array format
               'points' => 10,
               'created_by' => 1,
          ]);
     }

     /**
      * Multiple Choice with Choice Images (using same pattern as original)
      */
     private function createMultipleChoiceWithChoiceImages($exam, $number)
     {
          $choices = [
               '0' => $this->getRandomChoice($number, 'A'),
               '1' => $this->getRandomChoice($number, 'B'),
               '2' => $this->getRandomChoice($number, 'C'),
               '3' => $this->getRandomChoice($number, 'D'),
          ];

          $baseIndex = (($number - 1) * 4) % 10;

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '0',
               'question_text' => "Soal nomor {$number} untuk ujian {$exam->title}. Pilih gambar yang menunjukkan jawaban yang benar!",
               'question_image' => null,
               'choices' => json_encode($choices),
               'choices_images' => json_encode([
                    '0' => 'choice_images/choice_' . (($baseIndex % 10) + 1) . '.png',
                    '1' => 'choice_images/choice_' . ((($baseIndex + 1) % 10) + 1) . '.png',
                    '2' => 'choice_images/choice_' . ((($baseIndex + 2) % 10) + 1) . '.png',
                    '3' => 'choice_images/choice_' . ((($baseIndex + 3) % 10) + 1) . '.png',
               ]),
               'answer_key' => '[2]',
               'points' => 15,
               'created_by' => 1,
          ]);
     }

     private function createMultipleChoiceQuestion($exam, $number)
     {
          $choices = [
               '0' => $this->getRandomChoice($number, 'A'),
               '1' => $this->getRandomChoice($number, 'B'),
               '2' => $this->getRandomChoice($number, 'C'),
               '3' => $this->getRandomChoice($number, 'D'),
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '0', // Single answer multiple choice
               'question_text' => "Soal pilihan ganda nomor {$number} untuk ujian {$exam->title}. Manakah dari pernyataan berikut yang paling tepat?",
               'question_image' => null,
               'choices' => json_encode($choices),
               'choices_images' => null,
               'answer_key' => '[0]', // Store as JSON array format
               'points' => 10,
               'created_by' => 1, // Assuming admin user ID is 1
          ]);
     }

     private function createComplexMultipleChoiceQuestion($exam, $number)
     {
          $choices = [
               '0' => "Pernyataan A untuk soal {$number}",
               '1' => "Pernyataan B untuk soal {$number}",
               '2' => "Pernyataan C untuk soal {$number}",
               '3' => "Pernyataan D untuk soal {$number}",
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '1', // Complex multiple choice (multiple answers)
               'question_text' => "Soal pilihan ganda kompleks nomor {$number} untuk ujian {$exam->title}. Pilih semua jawaban yang benar:",
               'question_image' => null,
               'choices' => json_encode($choices),
               'choices_images' => null,
               'answer_key' => '[0,2]', // Store as JSON array format for multiple answers
               'points' => 15,
               'created_by' => 1,
          ]);
     }

     private function createTrueFalseQuestion($exam, $number)
     {
          $choices = [
               '0' => 'Benar',
               '1' => 'Salah',
          ];

          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '2', // True/False
               'question_text' => "Soal benar/salah nomor {$number} untuk ujian {$exam->title}. Pernyataan berikut ini adalah benar.",
               'question_image' => null,
               'choices' => json_encode($choices),
               'choices_images' => null,
               'answer_key' => '[0]', // Store as JSON array format
               'points' => 5,
               'created_by' => 1,
          ]);
     }

     private function createEssayQuestion($exam, $number)
     {
          Question::create([
               'exam_id' => $exam->id,
               'question_type_id' => '3', // Essay type
               'question_text' => "Soal essay nomor {$number} untuk ujian {$exam->title}. Jelaskan secara detail mengenai topik yang telah dipelajari dan berikan contoh yang relevan.",
               'question_image' => null,
               'choices' => null,
               'choices_images' => null,
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
