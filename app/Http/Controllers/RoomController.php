<?php

namespace App\Http\Controllers;

use App\Models\Rooms;
use App\Models\User;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;


class RoomController extends Controller
{
    //
    public function index($id){

        session(['exam_type_id' => $id]);

        $rooms = Rooms::where('school_id', session('school_id'))
                        ->where('exam_type_id', $id)
                        ->get();

        return view('kepala.view_rooms', compact('rooms'));
    }

    public function roomCreate(){

        return view('kepala.input_rooms');
    }

    public function roomStore(Request $request){
        try{
            $validated = $request->validate([
                'name' => 'required',
                'capacity' => 'required', 'integer',
            ]);

            $rooms = Rooms::create([
                'name' => $validated['name'],
                'capacity' => $validated['capacity'],
                'school_id' => session('school_id'),
                'exam_type_id' => session('exam_type_id'),
            ]);

            $rooms->save();

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Menambahkan data ruang : '.$validated['name']);

            return redirect()->route($this->getRoutePrefix() . '.room.create')->with('success', 'Data ruang berhasil ditambahkan');
        }
        catch(\Exception $e)
        {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function roomParticipants(Request $request, $id){
        $room = Rooms::where('id', $id)->first();
        $participants = Student::where('room_id', $id)->get();
        return view('kepala.room_participants', compact('room', 'participants'));
    }

    public function roomDestroy(Rooms $room)
    {
        try {
            $roomName = $room->name;
            $room->delete();

            $user = auth()->user();
            logActivity($user->name.' (ID: '.$user->id.') Berhasil Menghapus data ruang : '.$roomName);


            return redirect()->route($this->getRoutePrefix() . '.rooms', session('exam_type_id'))->with('success', 'Data ruang berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus data ruang: ' . $e->getMessage()]);
        }
    }

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
