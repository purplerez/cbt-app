<?php

use App\Http\Controllers\Api\Admin\DashboardStatisticsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\ExamlogController;
use App\Http\Controllers\Api\KepalaExamApiController;
use App\Http\Controllers\Api\ParticipantController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ExamScoreController;
use App\Http\Controllers\Api\Kepala\KepalaApiDashboardController;
// use App\Http\Controllers\Api\KepalaApiDashboardController as KepalaDashboard1;
use App\Http\Controllers\Api\StudentAnswerBackupController;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum', 'role:admin|super'])->prefix('admin')->name('api.admin.')->group(function () {
    Route::get('/schools/{schoolId}/students', [StudentController::class, 'getStudentsBySchool'])
        ->name('school.students');
    Route::get('/exam/{examId}/participants', [StudentController::class, 'getExamParticipants'])
        ->name('exam.participants');
    Route::get('/exam/{examId}/stats', [StudentController::class, 'getExamStats'])
        ->name('exam.stats');

    // Dashboard Statistics Routes
    Route::get('/dashboard/stats', [DashboardStatisticsController::class, 'getOverviewStats'])
        ->name('dashboard.stats');
    Route::get('/dashboard/active-exams', [DashboardStatisticsController::class, 'getActiveExams'])
        ->name('dashboard.active-exams');
    // ->name('exam.stats');

    Route::apiResource('exams', ExamController::class);

    Route::get('/schools/{schoolId}/students', [StudentController::class, 'getStudentsBySchool'])
        ->name('school.students');
    Route::post('/exams/add-student', [StudentController::class, 'addStudentToExam'])
        ->name('exam.add-student');

    // logs
    Route::get('/exam/{examId}/participant/{userId}/logs', [ExamController::class, 'getParticipantLogs'])
        ->name('exam.participant.logs');

    Route::get('/exam/{exam}/scores', [ExamScoreController::class, 'getScores']);

    Route::get('/schools/{schoolId}/grades', [StudentController::class, 'getGradesBySchool'])
    ->name('school.grades');

});


// peserta routes - API endpoints
Route::middleware(['auth:sanctum', 'role:siswa'])->prefix('siswa')->name('api.siswa.')->group(function () {
    Route::get('/dashboard', [ParticipantController::class, 'index'])->name('dashboard');
    Route::post('/exams/{examId}/start', [ParticipantController::class, 'start'])->name('exam.start');
    Route::post('/exams/{examId}/submit', [ParticipantController::class, 'submit'])->name('exam.submit');
    Route::post('/exams/{examId}/status', [ParticipantController::class, 'getSessionStatus'])->name('exam.status');

    // Student Answer Backup & Restore Routes
    Route::prefix('exam-session')->group(function () {
        Route::post('/save-answers', [StudentAnswerBackupController::class, 'saveAnswers'])->name('answers.save');

        // Update single answer for real-time auto-save
        Route::post('/update-answer', [StudentAnswerBackupController::class, 'updateSingleAnswer'])
            ->name('answer.update');

        // Get saved answers for resume functionality
        Route::get('/{sessionId}/answers', [StudentAnswerBackupController::class, 'getAnswers'])
            ->name('answers.get');

        Route::get('/{sessionId}/compact-answers', [StudentAnswerBackupController::class, 'getCompactAnswers'])
            ->name('answers.compact');

        // Restore answers from backup (resume exam) - JSON FORMAT
        Route::post('/restore-answers', [StudentAnswerBackupController::class, 'restoreAnswers'])
            ->name('answers.restore');


        // Get session progress/stats
        Route::get('/{sessionId}/progress', [StudentAnswerBackupController::class, 'getSessionProgress'])
            ->name('session.progress');
    });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Kepala API: exam participants
Route::middleware(['auth:sanctum', 'role:kepala|guru'])->prefix('kepala')->name('api.kepala.')->group(function () {
    Route::get('/exams/{examId}/participants', [KepalaExamApiController::class, 'participants'])
        ->name('exam.participants');

    // Route::get('/dashboard/stats', [App\Http\Controllers\Api\Kepala\DashboardStatisticsController::class, 'getOverviewStats'])
    //     ->name('dashboard.stats');

    //dashboard api starts
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Get overview statistics
        Route::get('/stats',[KepalaApiDashboardController::class, 'getStats'])
            ->name('stats');

        // Get statistics grouped by grade
        Route::get('/grade-stats', [KepalaApiDashboardController::class, 'getGradeStats'])
            ->name('grade_stats');

        // Get active exams (in progress)
        Route::get('/active-exams', [KepalaApiDashboardController::class, 'getActiveExams'])
            ->name('active_exams');

        // Get recent exam scores (completed)
        Route::get('/recent-scores', [KepalaApiDashboardController::class, 'getRecentScores'])
            ->name('recent_scores');
    });

    Route::prefix('exams')->name('exams.')->group(function () {
        // Get scores for specific exam (with optional grade filter)
        Route::get('{exam}/scores', [KepalaApiDashboardController::class, 'getExamScores'])
            ->name('scores');

        // Get session details
        Route::get('sessions/{sessionId}', [KepalaApiDashboardController::class, 'getSessionDetails'])
            ->name('session_details');
    });

    //dashboard api ends

});
