<?php

namespace App\Http\Controllers;

use App\Models\QuestionTypes;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function typeindex()
    {
        //
        $types = QuestionTypes::all();

        return view('admin.view_questiontypes', compact('types'));
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
    public function typestore(Request $request)
    {
        //
        try{
            $validates = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            QuestionTypes::create($validates);

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil ditambahkan.');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal menambahkan jenis soal :', $e->getMessage()]);
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
    public function typeedit(int $type)
    {
        //
        try{
            $type = QuestionTypes::findOrFail($type);

            return view('admin.editquestiontype', compact('type'));
        }
        catch(\Exception $e){

            return redirect()->route('admin.questions.types')->withErrors(['error' => 'Jenis Soal tidak ditemukan : '.$e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function typeupdate(Request $request, string $id)
    {
        //
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $type = QuestionTypes::findOrFail($id);
            $type->update($validated);

            return redirect()->route('admin.questions.types')->with('success', 'Jenis Soal berhasil dirubah');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withInput()->withErrors((['error' => 'Gagal Merubah Jenis Soal : '.$e->getMessage()]));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
