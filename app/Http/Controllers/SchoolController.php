<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSchoolRequest;
use App\Models\Grade;
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
            return redirect()->back()->withInput()->withErrors(['error' => 'Input Failed : ' . $e->getMessage()]);
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
    public function edit(School $school)
    {
        //
        try{
            $dataSchool = School::findOrFail($school->id);

            if (!$dataSchool) {
                return redirect()->route('admin.schools')->withErrors(['error' => 'School not found']);
            }

            return view('admin.editsekolah', compact('dataSchool'));
        }
        catch(\Exception $e){

            return redirect()->route('admin.schools')->withErrors(['error' =>'Searching data failed : ', $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'npsn' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'code' => ['required', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'max:2048'], // max 2MB
        ]);
        try {
            $school = School::findOrFail($id);

            $school->name = $request->input('name');
            $school->npsn = $request->input('npsn');
            $school->address = $request->input('address');
            $school->phone = $request->input('phone');
            $school->email = $request->input('email');
            $school->code = $request->input('code');


            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');

                // delete old logo if exists
                if ($school->logo && file_exists(public_path($school->logo))) {
                    unlink(public_path($school->logo));
                }

                // save new logo
                $imageName = time() . '.' . $logo->extension();
                $destinationPath = $logo->storeAs('assets/images/school', $imageName, 'public');
                $school->logo = $destinationPath;
            }
            $school->save();

            return redirect()->route('admin.schools')->with('success', 'Data sekolah berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Update Failed : ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        //
        try{
        // delete logo file if exists
        if ($school->logo && file_exists(public_path($school->logo))) {
            unlink(public_path($school->logo));
        }
        $school->delete();

        return redirect()->route('admin.schools')->with('success', 'Data sekolah berhasil dihapus');
        }
        catch(\Exception $e){
            return redirect()->route('admin.schools')->withErrors(['error' =>'Delete Failed ', $e->getMessage()]);
        }


    }

    public function manage(School $school)
    {
        try {
            // Set session data
            session([
                'school_id' => $school->id,
                'school_name' => $school->name,
                'school_code' => $school->code,
            ]);


            // Redirect ke halaman manage dengan GET request
            return redirect()->route('admin.schools.manage.view', $school);
        }
        catch(\Exception $e) {
            return redirect()->route('admin.schools')
                ->withErrors(['error' => 'Manage School Failed : ' . $e->getMessage()]);
        }
    }

    public function manageView(School $school)
    {
        try {
            // Pastikan session sekolah ada
            if (!session('school_id')) {
                return redirect()->route('admin.schools')
                    ->withErrors(['error' => 'School session not found']);
            }

            $students = $school->students()->get();
            $teachers = $school->teachers()->get();
            $head = $school->headmasters()->get();
            $subjects = $school->subjects()->get();
            $grade = Grade::all();
            // $school = School::findOrFail($school->id);

            return view('admin.managesekolah', compact('school', 'students', 'teachers', 'head', 'grade', 'subjects'));
        }
        catch(\Exception $e) {
            return redirect()->route('admin.schools')
                ->withErrors(['error' => 'Manage School Failed : ' . $e->getMessage()]);
        }
    }

    public function inactive(Request $request)
    {
        // Nonaktifkan sekolah
        // Set status to 0
        // Destroy session school
        try {
            $school = School::findOrFail(session('school_id'));

            if (!session('school_id')) {
                throw new \Exception('School session not found');
            }

            if ($school->id != session('school_id')) {
                throw new \Exception('School ID mismatch');
            }

            $school = School::findOrFail(session('school_id'));
            // if (!$school) {
            //     return redirect()->route('admin.schools')
            //         ->withErrors(['error' => 'School not found']);
            // }
            $school->status = '0';
            $school->save();

            // destroy session for school
            session()->forget(['school_id', 'school_name', 'school_code']);

            // set status inactive


            // Redirect ke halaman nonaktif dengan GET request
            return redirect()->route('admin.schools')
                ->with('success', 'Sekolah berhasil dinonaktifkan <script>setTimeout(function(){ showTab(\'manage\'); }, 100);</script>');
                // ->with('success', 'Sekolah berhasil dinonaktifkan');
        } catch (\Exception $e) {
            return redirect()->route('admin.schools')
                ->withErrors(['error' => 'Nonaktifkan Sekolah Failed : ' . $e->getMessage()]);
        }
    }
}
