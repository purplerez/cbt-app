<?php

namespace App\Http\Controllers\Kepala;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // dd(session()->all());

        return view('kepala.dashboard');
    }
}
