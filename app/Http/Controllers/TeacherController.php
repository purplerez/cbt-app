<?php

namespace App\Http\Controllers;

use App\Models\Headmaster;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;


class TeacherController extends Controller
{
    //
    public function store(Request $request)
    {
        DB::beginTransaction();
        try{
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
                'school_id' => session()->get('school_id')
            ]);

            Teacher::create($teacherData);

            DB::commit();
            return redirect()->route('admin.schools.manage', session()->get('school_id'))
                   ->with('success', 'Data guru berhasil ditambahkan <script>setTimeout(function(){ showTab(\'guru\'); }, 100);</script>');
        }
        catch (\Exception $e) {
            DB::rollBack();
            // delete the uploaded photo if it exist and the transaction fails
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Input Failed : ' . $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try{
            // Validate and update teacher data
            $validated = $request->validate([
                'teacher_id' => 'required|exists:teachers,id',
                'nip' => 'required|unique:teachers,nip,' . $request->teacher_id,
                't_name' => 'required|string|max:255',
                't_gender' => 'required|in:L,P',
                't_address' => 'required|string',
                't_photo' => 'nullable|image|max:2048|mimes:jpeg,jpg,gif', // max 2MB
            ]);

            // dd($validated);

            $teacher = Teacher::findOrFail($request->teacher_id);
            $teacher->update([
                'nip' => $validated['nip'],
                'name' => $validated['t_name'],
                'gender' => $validated['t_gender'],
                'address' => $validated['t_address'],
            ]);
            // Handle photo upload
            if ($request->hasFile('t_photo')) {
                $imageName = $request->nip . '.' . $request->file('t_photo')->extension();
                $photoPath = $request->file('t_photo')->storeAs('assets/images/teachers', $imageName, 'public');
                $validated['t_photo'] = $photoPath;
                // delete old photo if exists
                if ($teacher->photo && file_exists(public_path($teacher->photo))) {
                    unlink(public_path($teacher->photo));
                }
                $teacher->photo = $photoPath;
                // dd($teacher->photo, $request->file('t_photo'));
            }

            $teacher->save();
            // Update user data
            $user = User::findOrFail($teacher->user_id);
            $user->name = $validated['t_name'];
            $user->email = $validated['nip'] . '@teacher.test';
            $user->password = Hash::make($validated['nip']);
            $user->save();

            DB::commit();
            return redirect()->route('admin.schools.manage', session()->get('school_id'))
                   ->with('success', 'Data guru berhasil diubah <script>setTimeout(function(){ showTab(\'guru\'); }, 100);</script>');
        }
        catch (\Exception $e) {
            DB::rollBack();
            // delete the uploaded photo if it exists and the transaction fails
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Update Failed : ' . $e->getMessage()]);
        }
    }

    public function destroy($id){
        DB::beginTransaction();
        try{
            $teacher = Teacher::findOrFail($id);
            $user = User::findOrFail($teacher->user_id);

            // delete existing photo
            if ($teacher->photo && file_exists(public_path($teacher->photo))) {
                unlink(public_path($teacher->photo));
            }

            // delete user associated with teacher
            $user->delete();

            // delete teacher record
            $teacher->delete();

            DB::commit();
            return redirect()->route('admin.schools.manage', session()->get('school_id'))
                   ->with('success', 'Data guru berhasil dihapus <script>setTimeout(function(){ showTab(\'guru\'); }, 100);</script>');
        }
        catch(\Exception $e){
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Delete Failed : ' . $e->getMessage()]);
        }
    }

    public function storeHeadmaster(Request $request){
        DB::beginTransaction();
        try{
            // Validate and store headmaster data
            $validated = $request->validate([
                'h_nip' => 'required|unique:headmasters,nip',
                'h_name' => 'required|string|max:255',
                'h_gender' => 'required|in:L,P',
                'h_address' => 'required|string',
                'h_photo' => 'nullable|image|max:2048|mimes:jpeg,jpg,gif', // max 2MB
            ]);

           // dd($validated);

            // check existing nip in the same school
            if (Headmaster::where('nip', $request->h_nip)->where('school_id', session()->get('school_id'))->exists()) {
                return redirect()->back()->withInput()->withErrors(['error' => 'NIP sudah terdaftar di sekolah ini']);
            }

            // Handle photo upload
            if ($request->hasFile('h_photo')) {
                $imageName = $request->h_nip . '.' . $request->file('h_photo')->extension();
                $photoPath = $request->file('h_photo')->storeAs('assets/images/head', $imageName, 'public');
                $validated['h_photo'] = $photoPath;
            } else {
                $validated['h_photo'] = "assets/images/head/default.jpg"; // or set a default photo path
            }
            // add as user

            $user = User::create([
                'name' => $request->h_name,
                'email' => $request->h_nip . '@headmaster.test',
                'password' => Hash::make($request->h_nip),
                'role' => 'kepala', // Let the seeder explicitly set this to match Spatie role
                'email_verified_at' => now(),
            ]);

            // Assign teacher role using Spatie
            $user->assignRole('kepala');

            // Create teacher record with all required fields
            $teacherData = array_merge($validated, [
                'user_id' => $user->id,
                'school_id' => session()->get('school_id')
            ]);

            // dd($teacherData);

            $head = Headmaster::create(
                [
                    'nip' => $teacherData['h_nip'],
                    'name' => $teacherData['h_name'],
                    'gender' => $teacherData['h_gender'],
                    'address' => $teacherData['h_address'],
                    'photo' => $teacherData['h_photo'],
                    'user_id' => $teacherData['user_id'],
                    'school_id' => $teacherData['school_id'],
                ]
            );
            // dd($head);
            DB::commit();
            return redirect()->route('admin.schools.manage', session()->get('school_id'))
                   ->with('success', 'Data kepala sekolah berhasil ditambahkan <script>setTimeout(function(){ showTab(\'kepala\'); }, 100);</script>');
        }
        catch (\Exception $e) {
            DB::rollBack();
            // delete the uploaded photo if it exist and the transaction fails
            if (isset($photoPath) && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            return redirect()->back()->withInput()->withErrors(['error' => 'Input Failed : ' . $e->getMessage()]);
        }
    }
}
