<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamlogController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'role:admin|super'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/exam/{examId}/participants', [StudentController::class, 'getExamParticipants']);
    Route::get('/exam/{examId}/stats', [StudentController::class, 'getExamStats']);

    Route::apiResource('exams', ExamController::class);

    Route::get('/schools/{schoolId}/students', [StudentController::class, 'getStudentsBySchool']);
    Route::post('/exams/add-student', [StudentController::class, 'addStudentToExam']);


    // logs
    Route::get('/admin/exam/{examId}/participant/{userId}/logs', [ExamController::class, 'getParticipantLogs']);
});


// peserta routes
Route::middleware(['auth:sanctum', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [ParticipantController::class, 'index'])->name('dashboard');
    Route::post('/exams/{examId}/start', [ParticipantController::class, 'start'])->name('start');
    // Route::post('/exams/{examId}/submit', [ParticipantController::class, 'submit'])->name('submit');
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
