<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

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

            return redirect()->route('admin.users')->with('success', 'Status user berhasil diubah.');
        } catch (\Exception $e) {
            return redirect()->route('admin.users')->with('error', 'Terjadi kesalahan saat mengubah status user.');
        }
    }
}
