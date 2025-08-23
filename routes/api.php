<?php

use App\Http\Controllers\Api\ExamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('exams', ExamController::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
