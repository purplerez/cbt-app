<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $grades = Grade::all();

        return view('admin.view_grades', compact('grades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required'
        ]);
        try {
            $roleRoute = [
                'admin' => 'admin.grades',
                'super' => 'super.grades'
            ];

            $role = auth()->user()->getRoleNames()->first();
            if(!isset($roleRoute[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah tingkat');
            }

            $existingGrade = Grade::where('name', $request->name)->first();
            if ($existingGrade) {
                throw new \Exception('Tingkat sudah ada');
                // return redirect()->back()->withErrors(['error' => 'Tingkat sudah ada']);
            }
            if ($request->name == null) {
                throw new \Exception('Tingkat tidak boleh kosong');
                // return redirect()->back()->withErrors(['error' => 'Tingkat tidak boleh kosong']);
            }

            Grade::create([
                'name' => $request->name
            ]);

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Membuat Tingkat  : '.$request->name);

            return redirect()->route($roleRoute[$role])->with('success', 'Tingkat berhasil ditambahkan ');

        } catch (\Exception $e) {
            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Gagal Membuat Tingkat  : '.$request->name);

            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $grade = Grade::findOrFail($id);

        return view('admin.editgrade', compact('grade'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        try{
            $grade = Grade::findOrFail($id);

            $roleRoute = [
                'admin' => 'admin.grades',
                'super' => 'super.grades'
            ];

            $role = auth()->user()->getRoleNames()->first();
            if(!isset($roleRoute[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah tingkat');
            }

            // Check if the grade name already exists
            $existingGrade = Grade::where('name', $request->name)->where('id', '!=', $id)->first();
            if ($existingGrade) {
                throw new \Exception('Tingkat sudah ada');
                // return redirect()->back()->withErrors(['error' => 'Tingkat sudah ada']);
            }

            $grade->update($validatedData);

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Merubah data Tingkat  : '.$request->name);

            return redirect()->route($roleRoute[$role])->with('success', 'Tingkat berhasil diperbarui');
        }
        catch (\Exception $e) {

            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            //grade related to schoolId,
            //create delete grade without deleting the school. because grade is the child.

            $grade = Grade::findOrFail($id);

            $roleRoute = [
                'admin' => 'admin.grades',
                'super' => 'super.grades'
            ];

            $role = auth()->user()->getRoleNames()->first();
            if(!isset($roleRoute[$role])) {
                throw new \Exception ('Anda tidak memiliki akses untuk menambah tingkat');
            }
            //delete grade without deleting the school, because grade is the child.
            $grade->school_id = null;
            $grade->save();

            $grade->delete();


            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Menghapus Tingkat  : '.$grade->name);

            return redirect()->route($roleRoute[$role])->with('success', 'Tingkat berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
