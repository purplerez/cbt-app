<?php

namespace Database\Seeders;

use App\Models\Examtype;
use App\Models\School;
use App\Models\Grade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExamTypeSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
          $schools = School::all();
          $grades = Grade::all();

          if ($schools->isEmpty() || $grades->isEmpty()) {
               $this->command->error('Schools or Grades not found. Please run SchoolSeeder and GradeSeeder first.');
               return;
          }

          $examTypes = [
               'Ujian Tengah Semester',
               'Ujian Akhir Semester',
               'Ujian Nasional',
               'Ujian Sekolah',
               'Latihan Soal'
          ];

          foreach ($examTypes as $type) {
               Examtype::firstOrCreate([
                    'title' => $type,
                    'school_id' => $schools->random()->id,
                    'grade_id' => $grades->random()->id,
                    'start_time' => now()->addDays(rand(1, 30)),
                    'end_time' => now()->addDays(rand(31, 60)),
                    'is_active' => '1',
                    'is_global' => '0'
               ]);
          }
     }
}
