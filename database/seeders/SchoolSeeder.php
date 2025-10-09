<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        School::firstOrCreate(['name' => 'Sekolah Dasar 1', 'address' => 'Jl. Contoh No. 1', 'code' => 'SD001', 'logo' => 'assets/images/school/default.png']);
        School::firstOrCreate(['name' => 'Sekolah Menengah Pertama 1', 'address' => 'Jl. Contoh No. 2', 'code' => 'SMP001', 'logo' => 'assets/images/school/default.png']);
        School::firstOrCreate(['name' => 'Sekolah Menengah At   as 1', 'address' => 'Jl. Contoh No. 3', 'code' => 'SMA001', 'logo' => 'assets/images/school/default.png']);
    }
}
