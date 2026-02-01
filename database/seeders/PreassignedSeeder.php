<?php

namespace Database\Seeders;

use App\Models\Preassigned;
use App\Models\User;
use App\Models\Exam;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PreassignedSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
          $siswaUsers = User::query()->role('siswa')->get();
          $exams = Exam::all();

          if ($siswaUsers->isEmpty()) {
               $this->command->error('No siswa users found. Please run UserSeeder first.');
               return;
          }

          if ($exams->isEmpty()) {
               $this->command->error('No exams found. Please run ExamSeeder first.');
               return;
          }

          // Assign random exams to each siswa
          foreach ($siswaUsers as $user) {
               // Assign 2-4 random exams to each student
               $randomExams = $exams->random(rand(2, 4));

               foreach ($randomExams as $exam) {
                    Preassigned::firstOrCreate([
                         'user_id' => $user->id,
                         'exam_id' => $exam->id
                    ]);
               }
          }
     }
}
