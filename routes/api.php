<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\api\ExamlogController;
use App\Http\Controllers\api\ParticipantController;
use App\Http\Controllers\API\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);


Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::apiResource('exams', ExamController::class);
    Route::get('/schools/{schoolId}/students', [StudentController::class, 'getStudentsBySchool']);
    Route::post('/exams/add-student', [StudentController::class, 'addStudentToExam']);
    Route::get('/exams/{examId}/participants', [StudentController::class, 'getExamParticipants']);
    Route::get('/exams/{examId}/stats', [StudentController::class, 'getExamStats']);

    // Student routes
    Route::get('/schools/{schoolId}/students', [StudentController::class, 'getStudentsBySchool']);
    Route::post('/exams/add-student', [StudentController::class, 'addStudentToExam']);

    // logs
    Route::get('/exams/{userId}/Logs', [ExamlogController::class, 'getAllLogs']);
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
