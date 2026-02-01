<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\Examtype;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
          $examTypes = Examtype::all();
          $adminUsers = User::query()->role('admin')->get();

          if ($examTypes->isEmpty()) {
               $this->command->error('No exam types found. Please run ExamTypeSeeder first.');
               return;
          }

          if ($adminUsers->isEmpty()) {
               $this->command->error('No admin users found. Please run UserSeeder first.');
               return;
          }

          $examTitles = [
               'Matematika Dasar',
               'Bahasa Indonesia',
               'Bahasa Inggris',
               'Ilmu Pengetahuan Alam',
               'Ilmu Pengetahuan Sosial',
               'Sejarah',
               'Geografi',
               'Fisika',
               'Kimia',
               'Biologi'
          ];

          foreach ($examTitles as $title) {
               Exam::firstOrCreate([
                    'title' => $title,
                    'exam_type_id' => $examTypes->random()->id,
                    'description' => "Ujian $title untuk siswa",
                    'start_date' => now()->addMinute(rand(1, 30)),
                    'end_date' => now()->addDays(rand(1, 10)),
                    'duration' => rand(45, 50), // minutes
                    'total_quest' => rand(20, 50),
                    'score_minimal' => rand(60, 80),
                    'created_by' => $adminUsers->random()->id,
                    'is_active' => true
               ]);
          }
     }
}
