<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class LogsController extends Controller
{
    public function index()
    {
        return view('admin.logs');
    }
}
