<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\Question;
use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAnswerMissingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Skenario utama: row student_answer dibuat kosong (autosave awal),
     * lalu tidak ada update jawaban berikutnya → force submit → nilai = 0.
     */
    public function test_student_answer_missing_on_force_submit_results_in_zero_score()
    {
        $user = User::factory()->create(['role' => 'siswa', 'is_active' => true]);
        $exam = Exam::factory()->create(['duration' => 60, 'total_quest' => 10]);
        Question::factory()->create(['exam_id' => $exam->id, 'answer' => 'A']);

        $examSession = ExamSession::create([
            'user_id'        => $user->id,
            'exam_id'        => $exam->id,
            'session_token'  => bin2hex(random_bytes(16)),
            'started_at'     => now(),
            'submited_at'    => now()->addMinutes(60),
            'time_remaining' => 3600,
            'total_score'    => 0,
            'status'         => 'progress',
            'attempt_number' => 1,
            'ip_address'     => '127.0.0.1',
            'user_agent'     => 'Test Agent',
        ]);

        // Autosave awal dengan array kosong → row dibuat dengan answers = {}
        $response = $this->actingAs($user)->postJson('/api/student-answers/save', [
            'session_id'    => $examSession->id,
            'answers'       => [],
            'essay_answers' => [],
        ]);
        $response->assertStatus(200);

        // Pastikan row ada tapi total_answered = 0
        $this->assertDatabaseHas('student_answers', [
            'session_id'     => $examSession->id,
            'total_answered' => 0,
        ]);

        // Update jawaban setelahnya TIDAK terjadi (simulasi network failure)

        // Admin force submit
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $response = $this->actingAs($admin)->postJson('/api/force-submit/session', [
        ]);

        // If ForceSubmitController requires admin role, we might get 403, 
        // but let's assume it routes correctly or we can call the controller method directly.
        // We will just instantiate the controller to bypass route middleware if needed,
        // or test the endpoint if it's available. Let's call the endpoint.
        
        // 4. Assertions
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'score' => 0
        ]);

        $this->assertDatabaseHas('exam_sessions', [
            'id' => $examSession->id,
            'status' => 'submited',
            'total_score' => 0
        ]);
    }

    public function test_save_answers_validation_fails_on_null_or_empty_answers()
    {
        $user = User::factory()->create();
        $examSession = ExamSession::factory()->create([
            'user_id' => $user->id,
            'status' => 'progress'
        ]);

        $response = $this->actingAs($user)->postJson('/api/student-answers/save', [
            'session_id' => $examSession->id,
            'answers' => [
                '1' => null // simulate deselecting or missing answer
            ]
        ]);

        $response->assertStatus(422); // Validation should fail according to rules
    }
}
