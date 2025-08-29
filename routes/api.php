<?php

use App\Http\Controllers\API\ExamController;
use App\Http\Controllers\api\ExamlogController;
use App\Http\Controllers\API\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
