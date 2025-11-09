<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
     /**
      * Run the database seeds.
      */
     public function run(): void
     {
        $schools = School::all();

          $grades = [
               'Kelas 1',
               'Kelas 2',
               'Kelas 3',
               'Kelas 4',
               'Kelas 5',
               'Kelas 6',
               'Kelas 7',
               'Kelas 8',
               'Kelas 9',
               'Kelas 10',
               'Kelas 11',
               'Kelas 12'
          ];

          foreach ($grades as $grade) {
               Grade::firstOrCreate([
                'name' => $grade,
                'school_id' => $schools->random()->id
            ]);
          }
     }
}
