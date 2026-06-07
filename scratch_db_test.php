<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\DB;

// We will test updateSingleAnswer
try {
    DB::beginTransaction();
    
    // Create dummy user and exam
    $user = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
    $exam = Exam::factory()->create();
    
    $session = ExamSession::create([
        'user_id' => $user->id,
        'exam_id' => $exam->id,
        'session_token' => 'test-token',
        'started_at' => now(),
        'submited_at' => now()->addHour(),
        'time_remaining' => 3600,
        'total_score' => 0,
        'status' => 'progress',
        'attempt_number' => 1,
        'ip_address' => '127.0.0.1'
    ]);
    
    echo "Session created ID: " . $session->id . "\n";
    
    // Test 1: update first answer
    $res1 = StudentAnswer::updateSingleAnswer($session->id, 1, 'A');
    echo "Update 1 success: " . ($res1 ? 'yes' : 'no') . "\n";
    
    $sa = StudentAnswer::where('session_id', $session->id)->first();
    echo "Answers after 1: " . json_encode($sa->answers) . "\n";
    
    // Test 2: update second answer
    $res2 = StudentAnswer::updateSingleAnswer($session->id, 2, 'B');
    echo "Update 2 success: " . ($res2 ? 'yes' : 'no') . "\n";
    
    $sa2 = StudentAnswer::where('session_id', $session->id)->first();
    echo "Answers after 2: " . json_encode($sa2->answers) . "\n";

    DB::rollBack();
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    DB::rollBack();
}
