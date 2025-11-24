<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use App\Models\School;
use App\Models\Grade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
          // Get users with siswa role
          $siswaUsers = User::role('siswa')->get();

          // Get available schools and grades
          $schools = School::all();
          $grades = Grade::all();

          if ($schools->isEmpty()) {
               $this->command->error('No schools found. Please run SchoolSeeder first.');
               return;
          }

          if ($grades->isEmpty()) {
               $this->command->error('No grades found. Please run GradeSeeder first.');
               return;
          }

          foreach ($siswaUsers as $index => $user) {
               // Check if student already exists for this user
               if (!$user->student) {
                    Student::create([
                         'user_id' => $user->id,
                         'school_id' => $schools->random()->id,
                         'nis' => str_pad($index + 1, 6, '0', STR_PAD_LEFT), // Generate NIS: 000001, 000002, etc
                         'name' => $user->name,
                         'grade_id' => $grades->random()->id,
                         'gender' => collect(['L', 'P'])->random(),
                         'p_birth' => collect(['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Semarang'])->random(),
                         'd_birth' => \Illuminate\Support\Facades\Date::now()->subYears(rand(15, 18))->format('Y-m-d'),
                         'address' => 'Jl. Contoh No. ' . rand(1, 100),
                         'photo' => 'assets/images/students/default.jpg',
                    ]);
               }
          }
     }
}
