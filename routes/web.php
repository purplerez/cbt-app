<?php

use App\Http\Controllers\Admin\DashboardController as AdminController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\Guru\DashboardController as GuruController;
use App\Http\Controllers\Kepala\DashboardController as KepalaController;
use App\Http\Controllers\Siswa\DashboardController as SiswaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SubjectController;
use App\Models\School;
// use App\Http\Controllers\siswa\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// all users page
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// super admin page
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function() {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Routing for schools management
    Route::get('schools', [SchoolController::class, 'index'])->name('schools');
    Route::get('inputsekolah', function () {
        return view('admin.inputsekolah');
    })->name('inputsekolah');
    Route::post('inputsekolah', [SchoolController::class, 'store'])->name('sekolahstore');
    Route::get('schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
    Route::put('schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
    Route::delete('schools/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
    Route::post('schools/{school}/manage', [SchoolController::class, 'manage'])->name('schools.manage');
    Route::get('schools/{school}/manage', [SchoolController::class, 'manageView'])->name('schools.manage.view');
    Route::post('schools/nonaktif', [SchoolController::class, 'inactive'])->name('school.inactive');
    Route::get('schools/{school}/active', [SchoolController::class, 'active'])->name('school.active');


    // Routing for student management
    Route::post('students', [StudentController::class, 'store'])->name('students.store');
    Route::post('students/update', [StudentController::class, 'update'])->name('students.update');
    Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('siswa.destroy');

    // Routing for teacher management
    Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::post('teachers/update', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teacher.destroy');

    // Routing for headmaster management
    Route::post('headmasters', [TeacherController::class, 'storeHeadmaster'])->name('head.store');
    Route::get('headmasters/{headmaster}/edit', [TeacherController::class, 'editHeadmaster'])->name('head.edit');
    Route::post('headmasters/update', [TeacherController::class, 'updateHeadmaster'])->name('head.update');


    // Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('siswa.destroy');
    // Route::post('students/update', [StudentController::class, 'update'])->name('students.update');


    // Routing for grade management
    Route::get('grades', [GradeController::class, 'index'])->name('grades');
    Route::get('grades/create', function ()
    {
        return view('admin.inputgrades');
    })->name('input.grades');
    Route::post('grades/create', [GradeController::class, 'store'])->name('grades.store');
    Route::get('grades/{grade}/edit', [GradeController::class, 'edit'])->name('grades.edit');
    Route::put('grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    // routing for subjects management
    // Route::get('subjects', [SubjectController::class, 'index'])->name('subjects');
    // Route::get('subjects/create', [SubjectController::class, 'create'])->name('subjects.create');
    Route::post('subjects/create', [SubjectController::class, 'store'])->name('subjects.store');
    Route::delete('subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    // Route::get('subjects/{subject}/edit', [SubjectController::class, 'edit'])->name('subjects.edit');
    Route::post('subjects/update', [SubjectController::class, 'update'])->name('subjects.update');

    // Routing for question types management
    Route::get('question/type', [QuestionController::class, 'typeindex'])->name('questions.types');
    Route::get('question/type/create', function() {
        return view('admin.inputquestiontype');
    })->name('question.type.create');
    Route::post('question/type/create', [QuestionController::class, 'typestore'])->name('question.type.store');
    Route::get('question/type/{type}/edit', [QuestionController::class, 'typeedit'])->name('question.type.edit');
    Route::put('question/type/{type}/update', [QuestionController::class, 'typeupdate'])->name('question.type.update');
    Route::delete('question/type/{type}/destroy', [QuestionController::class, 'typedestroy'])->name('question.type.destroy');

    Route::get('exams', [ExamController::class, 'index'])->name('exams');
    Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');





    // Route::get('students', [SchoolController::class, 'students'])->name('students');

    Route::get('questions', [SchoolController::class, 'questions'])->name('questions');
    Route::get('results', [SchoolController::class, 'results'])->name('results');
    Route::get('settings', [SchoolController::class, 'settings'])->name('settings');
});

Route::middleware(['auth', 'role:kepala'])->prefix('kepala')->name('kepala.')->group(function(){
    Route::get('/dashboard', [KepalaController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function() {
    Route::get('/dashboard', [GuruController::class, 'index'])->name('dashboard');
    // Route::get('/guru/dashboard', [GuruController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function() {
    Route::get('/dashboard', [SiswaController::class, 'index'])->name('dashboard');
});
require __DIR__.'/auth.php';
