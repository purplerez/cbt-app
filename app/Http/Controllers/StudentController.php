<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class StudentController extends Controller
{
    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'nis' => 'required|unique:students',
                'name' => 'required|string|max:255',
                'grade_id' => 'required|exists:grades,id',
                'gender' => 'required|in:L,P',
                'p_birth' => 'required|string|max:255',
                'd_birth' => 'required|date',
                'address' => 'required|string',
                'photo' => 'nullable', 'image', 'max:2048', 'mimes:jpeg,jpg,gif', // max 2MB
            ]);

            // $validated['photo'] = $request->file('photo')->store('assets/images/students', 'public');

            // Check if the student already exists
            $existingStudent = Student::where('nis', $request->nis)->first();
            if ($existingStudent) {
                throw new \Exception('NIS already exists');
            }

            // upload the photo
            if ($request->hasFile('photo')) {
                $imageName = $request->nis.'.'.$request->file('photo')->extension();
                $photoPath = $request->file('photo')->storeAs('assets/images/students', $imageName, 'public');
                $validated['photo'] = $photoPath;
            } else {
                $validated['photo'] = "assets/images/students/default.jpg"; // or set a default photo path
            }

            // Create user account for student
            $user = User::create([
                'name' => $request->name,
                'email' => $request->nis . '@student.test',
                'password' => Hash::make($request->nis),
                'role' => 'siswa', // Let the seeder explicitly set this to match Spatie role
                // 'school_id' => $request->school_id
            ]);

            // Assign student role using Spatie
            $user->assignRole('siswa');

            // Create student record
            Student::create([
                'user_id' => $user->id,
                'nis' => $request->nis,
                'name' => $request->name,
                'grade_id' => $request->grade_id,
                'gender' => $request->gender,
                'p_birth' => $request->p_birth,
                'd_birth' => $request->d_birth,
                'address' => $request->address,
                'school_id' => session()->get('school_id'),
                'photo' => $validated['photo'],
            ]);

            DB::commit();
            return redirect()->route('admin.schools.manage.view', session()->get('school_id'))
                ->with('success', 'Data siswa berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            // Delete the created user if it exists
            if (isset($user)) {
                $user->delete();
            }

            return redirect()->back()->withInput()->withErrors(['error' => 'Input Failed : ' . $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        try {
            $student = Student::findOrFail($id);

            // Delete the associated user
            if ($student->user) {
                $student->user->delete();
            }

            $student->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data siswa berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 422);
        }
    }
}
