<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        try {
        $subjects = Subject::join('schools', 'subjects.school_id', '=', 'schools.id')
            ->select('subjects.*', 'schools.name as school_name')
            ->get();

        return view('admin.view_subjects', compact('subjects'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $schools = School::all();

        return view('admin.inputsubjects', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required',
            'school_id' => 'required|exists:schools,id',
            'code' => 'required|unique:subjects,code',
        ]);
        try {
            $existingSubject = Subject::where('name', $request->name)->where('school_id', $request->school_id)->first();
            if ($existingSubject) {
                throw new \Exception('Mata pelajaran sudah ada di sekolah ini');
            }
            Subject::create([
                'name' => $request->name,
                'school_id' => $request->school_id,
                'code' => $request->code,
            ]);
            return redirect()->route('admin.subjects')->with('success', 'Mata pelajaran berhasil ditambahkan');
        } catch (\Exception $e) {
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
        try{
        $subject = Subject::findOrFail($id);
        $schools = School::all();
        return view('admin.editsubject', compact('subject', 'schools'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'name' => 'required',
            'school_id' => 'required|exists:schools,id',
            'code' => 'required|unique:subjects,code,',
        ]);

        try {
            $subject = Subject::findOrFail($id);
            $subject->update([
                'name' => $request->name,
                'school_id' => $request->school_id,
                'code' => $request->code,
            ]);
            return redirect()->route('admin.subjects')->with('success', 'Mata pelajaran berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();
            return redirect()->route('admin.subjects')->with('success', 'Mata pelajaran berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
