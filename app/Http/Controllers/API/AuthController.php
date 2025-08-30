<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;



class AuthController extends Controller
{
    //
    public function login(Request $request){
        $request->validate([
            'nis' => 'required',
            'password' => 'required',
        ]);

    }
}
