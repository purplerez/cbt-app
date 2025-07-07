<?php

use App\Http\Controllers\Admin\DashboardController as AdminController;
use App\Http\Controllers\Guru\DashboardController as GuruController;
use App\Http\Controllers\Kepala\DashboardController as KepalaController;
use App\Http\Controllers\Siswa\DashboardController as SiswaController;
use App\Http\Controllers\ProfileController;
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
