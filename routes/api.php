<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamlogController;
use App\Http\Controllers\Api\KepalaExamApiController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentAnswerBackupController;
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
    Route::post('/exams/{examId}/submit', [ParticipantController::class, 'submit'])->name('submit');
    Route::post('/exams/{examId}/status', [ParticipantController::class, 'getSessionStatus'])->name('session-status');

    // Student Answer Backup & Restore Routes
    Route::prefix('exam-session')->group(function () {
        Route::post('/save-answers', [StudentAnswerBackupController::class, 'saveAnswers'])
            ->name('save-answers');

        // Update single answer for real-time auto-save
        Route::post('/update-answer', [StudentAnswerBackupController::class, 'updateSingleAnswer'])
            ->name('update-single-answer');

        // Get saved answers for resume functionality
        Route::get('/{sessionId}/answers', [StudentAnswerBackupController::class, 'getAnswers'])
            ->name('get-answers');

        Route::get('/{sessionId}/compact-answers', [StudentAnswerBackupController::class, 'getCompactAnswers'])
            ->name('get-compact-answers');

        // Restore answers from backup (resume exam) - JSON FORMAT
        Route::post('/restore-answers', [StudentAnswerBackupController::class, 'restoreAnswers'])
            ->name('restore-answers');

        // Get session progress/stats
        Route::get('/{sessionId}/progress', [StudentAnswerBackupController::class, 'getSessionProgress'])
            ->name('session-progress');
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Kepala API: exam participants
Route::middleware(['auth:sanctum', 'role:kepala'])->prefix('kepala')->group(function () {
    Route::get('/exams/{examId}/participants', [KepalaExamApiController::class, 'participants']);
});
