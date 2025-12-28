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
}
