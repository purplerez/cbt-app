<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan setiap 5 menit: set is_active = 0 untuk siswa yang token-nya
// tidak aktif lebih dari 10 menit (tidak ada request/heartbeat masuk)
Schedule::command('students:deactivate-inactive --minutes=5')->everyFiveMinutes();
