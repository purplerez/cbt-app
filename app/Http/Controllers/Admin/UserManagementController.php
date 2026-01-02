<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Headmaster;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;



class UserManagementController extends Controller
{
    //
    public function index()
    {
        try{
            $users = User::with('roles')->orderBy('role', 'asc')->get();
            return view('admin.view_users', compact('users'));
        }catch(\Exception $e){
            return view('admin.view_users');
        }
        // return view('admin.view_users');
    }

    public function toggleActive(Request $request, User $user)
    {
        // dd($user->email);?\
        try {

            $user->is_active = !$user->is_active;
            $user->save();

            $usrAktif = auth()->user();
            logActivity($usrAktif->name.' (ID: '.$usrAktif->id.') Tonggle = '.$user->is_active.' untuk user '. $user->email);


            return redirect()->route('admin.users')->with('success', 'Status user berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Terjadi kesalahan saat mengubah status user.');
        }
    }

    public function setPassword(Request $request, User $user)
    {
        try {
            $role = $user->role;
            //dd($role);
            if ($role == 'super') {
                //  dd("super here");
                $user->password = Hash::make('superpass125');
            }elseif ($role == 'kepala') {
                // dd($user->id." kepala here");
                $head = Headmaster::where('user_id', $user->id)->first();
                // dd($head);
                if ($head) {
                    $user->password = Hash::make($head->nip);
                } else {
                    throw new \Exception('User Kepala Sekolah tidak ditemukan');
                }
            }
            elseif ($role == 'guru') {
                //  dd("guru here");
                $guru = Teacher::where('user_id', $user->id)->first();
                if ($guru) {
                    $user->password = Hash::make($guru->nip);
                } else {
                    throw new \Exception('User Guru tidak ditemukan');
                }
            }
            elseif ($role == 'siswa') {
                //  dd("siswa here");
               $siswa = Student::where('user_id', $user->id)->first();
                if ($siswa) {
                    $user->password = Hash::make($siswa->nis);
                } else {
                    throw new \Exception('User Siswa tidak ditemukan');
                }
            }
            else {
                // dd('role else here');
                throw new \Exception('Role user tidak dikenali');
            }

            $user->save();

            $usrAktif = auth()->user();
            logActivity($usrAktif->name.' (ID: '.$usrAktif->id.') Berhasil Merubah password '. $user->email);


            return redirect()->route('admin.users')->with('success', 'Password berhasil diubah.');
        } catch (\Exception $e) {
            Log::error('SetPassword Error', [
            'user_id' => $user->id,
            'role' => $user->role,
            'error_message' => $e->getMessage(),
            'timestamp' => now()
        ]);

        return redirect()->route('admin.users')->with('error', $e->getMessage());
        }
    }
}
