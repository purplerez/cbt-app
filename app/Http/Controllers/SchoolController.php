<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        $schools = School::all();
        return view('admin.csekolah', compact('schools'));
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
 public function store(StoreSchoolRequest $request)
    {
        // dd($request);
        try {
            $validatedData = $request->validated();

            if($request->hasFile('logo')) {
                $logo = $request->file('logo');

                $imageName = time().'.'.$logo->extension();
                $destinationPath = $logo->storeAs('assets/images/school', $imageName, 'public');
                $validatedData['logo'] = $destinationPath;

            }
            else {
                throw new \Exception('Logo is required and must be a valid image file');
            }

            School::create($validatedData);


            return redirect()->route('admin.schools')->with('success', 'Data sekolah berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Validation failed: ' . $e->getMessage()]);
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
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
