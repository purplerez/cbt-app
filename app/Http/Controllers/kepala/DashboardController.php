<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRequest;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use App\Models\Rooms;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DashboardController extends Controller
{
    /**
     * Download Excel template for student import
     */
    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport, 'template_import_siswa.xlsx');
    }

    /**
     * Import students from Excel file
     */
    public function importStudents(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('excel_file'));


            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Mengimport Data Siswa');

            return redirect()->back()->with('success', 'Data siswa berhasil diimport');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(function ($failure) {
                return "Baris {$failure->row()}: {$failure->errors()[0]}";
            })->join(', ');

            return redirect()->back()->withErrors(['error' => 'Import gagal: ' . $errors]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Import gagal: ' . $e->getMessage()]);
        }
    }
    //
    // private $school;

    // public function __construct(School $school)
    // {
    //     $this->school = session('school_id');
    // }

    public function index()
    {
        // dd(session()->all());

        return view('kepala.dashboard');
    }



    public function school()
    {
        try {
            $school_id = $this->getSchoolId();
            $school = School::findOrFail($school_id);

            return view($this->getRoutePrefix() . '.view_school', compact('school'));
        } catch (\Exception $e) {
            Log::error('Kepala School Method Error: ' . $e->getMessage());
            return redirect()->route('kepala.dashboard')->with('error', $e->getMessage());
        }
    }

    /**
     * Get school ID from session or database
     */
    private function getSchoolId()
    {
        $school_id = session('school_id');

        if (!$school_id) {
            $user = Auth::user();
            $headmaster = $user->head;

            if (!$headmaster) {
                throw new \Exception('Data kepala sekolah tidak ditemukan. Silakan hubungi administrator.');
            }

            $school_id = $headmaster->school_id;

            // Set session untuk penggunaan selanjutnya
            session([
                'school_id' => $school_id,
                'school_name' => $headmaster->school->name,
                'kepala_id' => $headmaster->id
            ]);
        }

        return $school_id;
    }

    public function editSchool($id)
    {
        try {
            $school = School::findOrFail($id);

            return view('kepala.edit_school', compact('school'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function updateSchool(Request $request)
    {
        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'npsn' => 'required|string|max:255',
                'address' => 'required|string',
                'phone' => 'required|string|max:20',
                'email' => 'required|string|email|max:255',
                'code' => 'required|string|max:50',
                'logo' => 'nullable|image|max:2048' // max 2MB
            ]);


            $school = School::findOrFail($request->id);

            $school->name = $validated['name'];
            $school->npsn = $validated['npsn'];
            $school->address = $validated['address'];
            $school->phone = $validated['phone'];
            $school->email = $validated['email'];
            $school->code = $validated['code'];

            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');

                // delete old logo if exists
                if ($school->logo && $school->logo !== "assets/images/school/default.png") {
                    Storage::disk('public')->delete($school->logo);
                }

                $imageName = time() . '.' . $logo->extension();
                $destinationPath = $logo->storeAs('assets/images/school', $imageName, 'public');
                $school->logo = $destinationPath;
            }

            $school->save();

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Merubah Data Sekolah : ' . $validated['name']);


            return redirect()->route('kepala.school')->with('success', 'Data sekolah berhasil diperbarui');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function studentAll(Request $request)
    {
        $school_id = $this->getSchoolId();
        $query = Student::where('school_id', $school_id);

        $selectedGrade = null;
        if ($request->has('grade_id') && $request->grade_id) {
            $selectedGrade = $request->grade_id;
            $query->where('grade_id', $request->grade_id);
        }

        $students = $query->get();
        $grade = Grade::all();
        return view(
            $this->getRoutePrefix() . '.view_datasiswa',
            [
                'students' => $students,
                'grade' => $grade,
                'selectedGrade' => $selectedGrade
            ],
            compact('students', 'grade', 'selectedGrade')
        );
    }

    public function createStudent()
    {
        $grade = Grade::all();
        return view($this->getRoutePrefix() . '.input_siswa', compact('grade'));
    }

    public function storeStudent(Request $request)
    {
        //dd(auth()->user()->getRoleNames()->first());

        $validated = $request->validate([
            'nis' => 'required|unique:students',
            'name' => 'required|string|max:255',
            'grade_id' => 'required|exists:grades,id',
            'gender' => 'required|in:L,P',
            'p_birth' => 'required|string|max:255',
            'd_birth' => 'required|date',
            'address' => 'required|string',
            'photo' => 'nullable',
            'image',
            'max:2048',
            'mimes:jpeg,jpg,gif', // max 2MB
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
                $imageName = $request->nis . '.' . $request->file('photo')->extension();
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
                'school_id' => $this->getSchoolId(),
                'photo' => $validated['photo'],
            ]);

            // dd($this->getRoutePrefix());

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Menambahkan Data Siswa : ' . $request->name);

            DB::commit();

            return redirect()->route($this->getRoutePrefix() . '.students')->with('success', 'Data Siswa Berhasil Ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function editStudent($id)
    {
        try {
            $student = Student::findOrFail($id);
            $grade = Grade::all();
            return view($this->getRoutePrefix() . '.edit_siswa', compact('student', 'grade'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function updateStudent(Request $request, $id)
    {
        try {
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
                $imageName = $request->nis . '.' . $request->file('photo')->extension();
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
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Merubah Data Siswa: ID-' . $id);

            return redirect()->route($this->getRoutePrefix() . '.students')->with('success', 'Data siswa berhasil diperbarui');
        } catch (\Exception $e) {

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Gagal Merubah Data Siswa' . $request->name);

            return redirect()->back()->withInput()->withErrors(['error' => 'Update Failed : ' . $e->getMessage()]);
        }
    }

    public function destroyStudent($id)
    {
        try {
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
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Menghapus Data ' . $student->name);

            $student->delete();

            return redirect()->route('kepala.students')->with('success', 'Data siswa berhasil dihapus');
        } catch (\Exception $e) {
            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Gagal Menghapus Data Siswa' . $student->name);
        }
    }

    public function teacherAll()
    {
        try {
            $school_id = $this->getSchoolId();
            $teachers = Teacher::where('school_id', $school_id)->get();
            return view('kepala.view_dataguru', compact('teachers'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function createTeacher()
    {
        try {
            return view('kepala.input_guru');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function storeTeacher(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            // Validate and store teacher data
            $validated = $request->validate([
                'nip' => 'required|unique:teachers',
                'name' => 'required|string|max:255',
                'gender' => 'required|in:L,P',
                'address' => 'required|string',
                'photo' => 'nullable|image|max:2048|mimes:jpeg,jpg,gif', // max 2MB
            ]);
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $imageName = $request->nip . '.' . $request->file('photo')->extension();
                $photoPath = $request->file('photo')->storeAs('assets/images/teachers', $imageName, 'public');
                $validated['photo'] = $photoPath;
            } else {
                $validated['photo'] = "assets/images/teachers/default.jpg"; // or set a default photo path
            }
            // add as user

            $user = User::create([
                'name' => $request->name,
                'email' => $request->nip . '@teacher.test',
                'password' => Hash::make($request->nip),
                'role' => 'guru', // Let the seeder explicitly set this to match Spatie role
                'email_verified_at' => now(),
            ]);

            // Assign teacher role using Spatie
            $user->assignRole('guru');

            // Create teacher record with all required fields
            $teacherData = array_merge($validated, [
                'user_id' => $user->id,
                'school_id' => $this->getSchoolId()
            ]);

            Teacher::create($teacherData);

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Menambahkan Data Guru' . $request->name . ' Sekolah ' . session('school_name'));

            DB::commit();
            return redirect()->route('kepala.teachers')
                ->with('success', 'Data guru berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            // delete the uploaded photo if it exist and the transaction fails
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Input Failed : ' . $e->getMessage()]);
        }
    }

    public function editTeacher($id)
    {
        try {
            $teacher = Teacher::findOrFail($id);

            return view('kepala.edit_guru', compact('teacher'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateTeacher(Request $request)
    {
        // dd($request->all());
        DB::beginTransaction();
        try {
            // Validate and store teacher data
            $validated = $request->validate([
                'nip' => 'required|unique:teachers,nip,' . $request->id . ',id',
                'name' => 'required|string|max:255',
                'gender' => 'required|in:L,P',
                'address' => 'required|string',
                'photo' => 'nullable|image|max:2048|mimes:jpeg,jpg,gif', // max 2MB
            ]);

            $teacher = Teacher::findOrFail($request->id);
            $teacher->update([
                'nip' => $validated['nip'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'address' => $validated['address'],
            ]);

            if ($request->hasFile('photo')) {
                $oldPhoto = $request->input('old_photo');
                // Delete old photo if it exists and is not the default photo
                if ($oldPhoto && $oldPhoto !== "assets/images/students/default.jpg") {
                    Storage::disk('public')->delete($oldPhoto);
                }
                // Store the new photo
                $imageName = $request->nip . '.' . $request->file('photo')->extension();
                $photoPath = $request->file('photo')->storeAs('assets/images/students', $imageName, 'public');
                $teacher->photo = $photoPath;
                $teacher->save();
            }

            $user = User::findOrFail($teacher->user_id);
            $user->name = $validated['name'];
            $user->email = $validated['nip'] . '@teacher.test';
            $user->password = Hash::make($validated['nip']);
            $user->save();

            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Berhasil Merubah Data Guru ' . $request->name . ' Sekolah ' . session('school_name'));

            DB::commit();

            return redirect()->route('kepala.teachers')->with('success', 'Data guru berhasil diubah');
        } catch (\Exception $e) {
            DB::rollBack();
            $user = auth()->user();
            logActivity($user->name . ' (ID: ' . $user->id . ') Gagal Merubah Data Guru ' . $request->name . ' Sekolah ' . session('school_name'));

            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroyTeacher($id)
    {
        DB::beginTransaction();
        try {
            $teacher = Teacher::findOrFail($id);
            $user = User::findOrFail($teacher->user_id);

            // delete existing photo
            if ($teacher->photo && file_exists(public_path($teacher->photo) && $teacher->photo !== "assets/images/students/default.jpg")) {
                unlink(public_path($teacher->photo));
            }

            $userlog = auth()->user();
            logActivity($userlog->name . ' (ID: ' . $userlog->id . ') Berhasil Menghapus Data Guru' . $teacher->name . ' Sekolah ' . session('school_name'));

            // delete user associated with teacher
            $user->delete();

            // delete teacher record
            $teacher->delete();

            DB::commit();
            return redirect()
                ->route('kepala.teachers')
                ->with('success', 'Data guru berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Delete Failed : ' . $e->getMessage()]);
        }
    }
    //->route($this->getRoutePrefix() . '.berita-acara.index')

    private function getRoutePrefix()
    {
        $user = auth()->user();

        if ($user->hasRole('kepala')) {
            return 'kepala';
        }
        elseif ($user->hasRole('guru')) {
            return 'guru';
        }

        return 'admin';
    }
}
