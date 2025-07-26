<?php

namespace App\Http\Controllers;

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
}
