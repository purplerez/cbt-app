<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\School;
use App\Models\Grade;
use App\Models\Exam;
use App\Models\Preassigned;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class BenchmarkStudentSeeder extends Seeder
{
    public function run(): void
    {
        $totalStudents = 1500;
        $examId = 2;
        $password = Hash::make('12345678');

        $siswaRole = Role::where('name', 'siswa')->first();
        if (!$siswaRole) {
            $this->command->error('Role siswa not found!');
            return;
        }

        $schools = School::all();
        if ($schools->isEmpty()) {
            $this->command->error('No schools found!');
            return;
        }

        $grades = Grade::all();
        if ($grades->isEmpty()) {
            $this->command->error('No grades found!');
            return;
        }

        $exam = Exam::find($examId);
        if (!$exam) {
            $this->command->error("Exam ID {$examId} not found!");
            return;
        }

        $exam->update([
            'start_date' => Carbon::now()->subDay(),
            'end_date' => Carbon::now()->addDays(7),
        ]);

        $this->command->info("Generating {$totalStudents} benchmark students...");

        $existingCount = User::where('email', 'like', 'bench_%')->count();
        $this->command->info("Existing benchmark users: {$existingCount}");

        $batchSize = 100;
        $created = 0;

        for ($i = 1; $i <= $totalStudents; $i += $batchSize) {
            $end = min($i + $batchSize - 1, $totalStudents);

            $users = [];
            $students = [];
            $preassigned = [];

            for ($j = $i; $j <= $end; $j++) {
                $email = 'bench_' . str_pad($j, 4, '0', STR_PAD_LEFT);

                $user = [
                    'name' => "Benchmark Student {$j}",
                    'email' => $email,
                    'password' => $password,
                    'is_active' => true,
                    'is_logout' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $users[] = $user;
            }

            $insertedUsers = [];
            foreach ($users as $userData) {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    $userData
                );
                $user->assignRole($siswaRole);
                $insertedUsers[] = $user;

                $school = $schools->random();
                $grade = $grades->random();

                $student = Student::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nis' => 'BENCH' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
                        'name' => $user->name,
                        'school_id' => $school->id,
                        'grade_id' => $grade->id,
                        'gender' => ['L', 'P'][array_rand(['L', 'P'])],
                        'p_birth' => 'Jakarta',
                        'd_birth' => '2005-01-01',
                        'address' => 'Jl. Benchmark No. ' . $j,
                        'photo' => 'assets/images/students/default.jpg',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                Preassigned::updateOrCreate(
                    ['user_id' => $user->id, 'exam_id' => $examId],
                    [
                        'exam_id' => $examId,
                        'user_id' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $created++;
            }

            $this->command->info("Created {$created}/{$totalStudents} students");
        }

        $this->command->info("Done! {$created} benchmark students created and assigned to exam ID {$examId}");
    }
}
