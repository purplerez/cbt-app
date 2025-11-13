<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    //
    public function index()
    {

        return view('guru.dashboard');
    }

    public function studentAll(Request $request){
        $query = Student::where('school_id', session('school_id'));

        $selectedGrade = null;
        if ($request->has('grade_id') && $request->grade_id) {
            $selectedGrade = $request->grade_id;
            $query->where('grade_id', $request->grade_id);
        }

        $students = $query->get();
        $grade = Grade::all();
        return view('kepala.view_datasiswa', compact('students', 'grade', 'selectedGrade'));
    }

    public function createStudent(){
        $grade = Grade::all();
        return view('kepala.input_siswa', compact('grade'));
    }

    public function storeStudent(Request $request){
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

        DB::beginTransaction();
        try {
            // Check if the student already exists
            $existingStudent = Student::where('nis', $request->nis)->first();
            if ($existingStudent) {
                throw new \Exception('NIS already exists');
            }

            // Check if email already exists
            $email = $request->nis . '@student.test';
            $existingUser = User::where('email', $email)->first();
            if ($existingUser) {
                throw new \Exception('A user with this NIS already exists in the system');
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
                'email_verified_at' => now(),
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

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Menambahkan Data Siswa : '.$request->name);

            DB::commit();

            return redirect()->route('kepala.students')->with('success', 'Data Siswa Berhasil Ditambahkan');
        }
        catch(\Exception $e){
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editStudent($id){
        try{
            $student = Student::findOrFail($id);
            $grade = Grade::all();
            return view('kepala.edit_siswa', compact('student', 'grade'));
        }
        catch(\Exception $e){
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function updateStudent(Request $request, $id){
        try{
            $validatedData = $request->validate([
                'nis' => 'required',
                'name' => 'required|string|max:255',
                'grade_id' => 'required|exists:grades,id',
                'gender' => 'required|in:L,P',
                'p_birth' => 'required|string|max:255', // Place of birth
                'd_birth' => 'required|date', // Date of birth
                'address' => 'required|string',
                'photo' => 'nullable|image|max:2048|mimes:jpeg,jpg,gif', // max 2MB and only JPEG, JPG, and GIF files
            ]);
            // dd($validatedData);
            $student = Student::findOrFail($id);

            $student->update([
                'nis' => $validatedData['nis'],
                'name' => $validatedData['name'],
                'grade_id' => $validatedData['grade_id'],
                'gender' => $validatedData['gender'],
                'p_birth' => $validatedData['p_birth'],
                'd_birth' => $validatedData['d_birth'],
                'address' => $validatedData['address'],
            ]);

            if ($request->hasFile('photo')) {
                $oldPhoto = $request->input('old_photo');
                // Delete old photo if it exists and is not the default photo
                if ($oldPhoto && $oldPhoto !== "assets/images/students/default.jpg") {
                    Storage::disk('public')->delete($oldPhoto);
                }
                // Store the new photo
                $imageName = $request->nis.'.'.$request->file('photo')->extension();
                $photoPath = $request->file('photo')->storeAs('assets/images/students', $imageName, 'public');
                $student->photo = $photoPath;
                $student->save();
            }

            $updateUser = User::where('id', $student->user_id)->first();
            $updateUser->name = $validatedData['name'];
            $updateUser->email = $validatedData['nis'] . '@student.test';
            $updateUser->password = Hash::make($validatedData['nis']);
            $updateUser->save();

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Merubah Data Siswa: ID-'.$id);

            return redirect()->route('kepala.students')->with('success', 'Data siswa berhasil diperbarui');
        }
        catch (\Exception $e) {

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Gagal Merubah Data Siswa'.$request->name);

            return redirect()->back()->withInput()->withErrors(['error' => 'Update Failed : ' . $e->getMessage()]);
        }
    }

    public function destroyStudent($id){
        try{
            $student = Student::findOrFail($id);

            // Delete the associated user
            if ($student->user) {
                $student->user->delete();
            }
            // Delete the photo if it exists and is not the default photo
            if ($student->photo && $student->photo !== "assets/images/students/default.jpg") {
                Storage::disk('public')->delete($student->photo);
            }

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Menghapus Data '.$student->name);

            $student->delete();

            return redirect()->route('kepala.students')->with('success', 'Data siswa berhasil dihapus');
        }
        catch(\Exception $e){
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Gagal Menghapus Data Siswa'.$student->name);
        }
    }
}
