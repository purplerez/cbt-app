<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SchoolSeeder::class,
            GradeSeeder::class,
            UserSeeder::class,
            StudentSeeder::class,
            ExamTypeSeeder::class,
            ExamSeeder::class,
            QuestionSeeder::class, // Added QuestionSeeder
            PreassignedSeeder::class,
        ]);
    }
}
