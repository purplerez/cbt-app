<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            DB::transaction(function() use ($row) {
                // Create user account
                $user = User::create([
                    'name' => $row['nama'],
                    'email' => $row['nis'] . '@student.test',
                    'password' => Hash::make($row['nis']),
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('siswa');

                // Create student record
                Student::create([
                    'user_id' => $user->id,
                    'school_id' => session('school_id'),
                    'nis' => $row['nis'],
                    'name' => $row['nama'],
                    'grade_id' => $row['kelas_id'],
                    'gender' => $row['jenis_kelamin'],
                    'p_birth' => $row['tempat_lahir'],
                    'd_birth' => $row['tanggal_lahir'],
                    'address' => $row['alamat'],
                    'photo' => 'assets/images/students/default.jpg'
                ]);
            });
        }
    }

    public function rules(): array
    {
        // return [
        //     'nis' => [
        //         'required',
        //         'string',
        //         'min:4',
        //         'max:20',
        //         'unique:students,nis'
        //     ],
        //     'nama' => 'required|string|min:3|max:255',
        //     'kelas_id' => [
        //         'required',
        //         'numeric',
        //         'exists:grades,id'
        //     ],
        //     'jenis_kelamin' => [
        //         'required',
        //         'string',
        //         'in:L,P'
        //     ],
        //     'tempat_lahir' => 'required|string|min:3|max:100',
        //     'tanggal_lahir' => [
        //         'required',
        //         'date',
        //         'date_format:Y-m-d',
        //         'after:1990-01-01',
        //         'before:2020-12-31'
        //     ],
        //     'alamat' => 'required|string|min:5|max:500'
        // ];

        return [
            '*.nis' => ['required', 'unique:students,nis'],
            '*.nama' => ['required', 'string'],
            '*.kelas_id' => ['required', 'exists:grades,id'],
            '*.jenis_kelamin' => ['required', 'in:L,P'],
            '*.tempat_lahir' => ['required', 'string'],
            '*.tanggal_lahir' => ['required', 'date'],
            '*.alamat' => ['required', 'string'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nis.required' => 'NIS wajib diisi',
            '*.nis.unique' => 'NIS sudah terdaftar',
            '*.nama.required' => 'Nama wajib diisi',
            '*.kelas_id.required' => 'Kelas wajib diisi',
            '*.kelas_id.exists' => 'Kelas tidak valid',
            '*.jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
            '*.jenis_kelamin.in' => 'Jenis kelamin harus L atau P',
            '*.tempat_lahir.required' => 'Tempat lahir wajib diisi',
            '*.tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            '*.tanggal_lahir.date' => 'Format tanggal lahir tidak valid',
            '*.alamat.required' => 'Alamat wajib diisi',
        ];
    }
}
