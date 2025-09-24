<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Question;
use App\Models\Student;
use App\Models\School;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ExamSubmitTest extends TestCase
{
     use RefreshDatabase;

     protected $user;
     protected $exam;
     protected $examSession;
     protected $questions;

     protected function setUp(): void
     {
          parent::setUp();

          // Create test data
          $this->user = User::factory()->create();
          $this->user->assignRole('siswa');

          $school = School::factory()->create();
          $grade = Grade::factory()->create();

          Student::factory()->create([
               'user_id' => $this->user->id,
               'school_id' => $school->id,
               'grade_id' => $grade->id
          ]);

          $this->exam = Exam::factory()->create([
               'duration' => 120, // 2 hours
               'total_quest' => 5
          ]);

          $this->examSession = ExamSession::factory()->create([
               'user_id' => $this->user->id,
               'exam_id' => $this->exam->id,
               'session_token' => 'test-session-token',
               'started_at' => Carbon::now(),
               'submited_at' => Carbon::now()->addMinutes(120),
               'status' => 'progress'
          ]);

          // Create test questions
          $this->questions = collect([
               Question::factory()->create([
                    'exam_id' => $this->exam->id,
                    'question_type_id' => 0, // Multiple choice
                    'answer_key' => 'A',
                    'points' => 20
               ]),
               Question::factory()->create([
                    'exam_id' => $this->exam->id,
                    'question_type_id' => 0, // Multiple choice
                    'answer_key' => 'B',
                    'points' => 20
               ]),
               Question::factory()->create([
                    'exam_id' => $this->exam->id,
                    'question_type_id' => 2, // True/False
                    'answer_key' => 'TRUE',
                    'points' => 20
               ]),
               Question::factory()->create([
                    'exam_id' => $this->exam->id,
                    'question_type_id' => 3, // Essay
                    'answer_key' => '',
                    'points' => 20
               ]),
               Question::factory()->create([
                    'exam_id' => $this->exam->id,
                    'question_type_id' => 0, // Multiple choice
                    'answer_key' => 'C',
                    'points' => 20
               ])
          ]);
     }

     /** @test */
     public function user_can_submit_exam_successfully()
     {
          Sanctum::actingAs($this->user, ['*']);

          $answers = [
               (string)$this->questions[0]->id => 'A', // Correct
               (string)$this->questions[1]->id => 'C', // Wrong
               (string)$this->questions[2]->id => 'TRUE', // Correct
               (string)$this->questions[4]->id => 'C', // Correct
          ];

          $essayAnswers = [
               (string)$this->questions[3]->id => 'This is my essay answer'
          ];

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'test-session-token',
               'answers' => $answers,
               'essay_answers' => $essayAnswers,
               'final_submit' => true
          ]);

          $response->assertStatus(200)
               ->assertJson([
                    'success' => true,
                    'message' => 'Ujian berhasil diselesaikan'
               ])
               ->assertJsonStructure([
                    'data' => [
                         'session_id',
                         'exam_title',
                         'total_score',
                         'max_score',
                         'percentage',
                         'grade',
                         'total_questions',
                         'answered_questions',
                         'unanswered_questions',
                         'submission_time',
                         'score_breakdown',
                         'detailed_results'
                    ]
               ]);

          // Verify session is updated
          $this->examSession->refresh();
          $this->assertEquals('completed', $this->examSession->status);
          $this->assertEquals(60, $this->examSession->total_score); // 3 correct out of 5
     }

     /** @test */
     public function user_cannot_submit_with_invalid_session_token()
     {
          Sanctum::actingAs($this->user, ['*']);

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'invalid-token',
               'answers' => [],
               'essay_answers' => []
          ]);

          $response->assertStatus(404)
               ->assertJson([
                    'success' => false,
                    'message' => 'Session ujian tidak ditemukan atau sudah berakhir'
               ]);
     }

     /** @test */
     public function user_cannot_submit_expired_exam()
     {
          Sanctum::actingAs($this->user, ['*']);

          // Make exam session expired
          $this->examSession->update([
               'submited_at' => Carbon::now()->subMinutes(10)
          ]);

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'test-session-token',
               'answers' => [],
               'essay_answers' => []
          ]);

          $response->assertStatus(422)
               ->assertJson([
                    'success' => false,
                    'message' => 'Waktu ujian telah berakhir'
               ]);
     }

     /** @test */
     public function user_can_force_submit_expired_exam()
     {
          Sanctum::actingAs($this->user, ['*']);

          // Make exam session expired
          $this->examSession->update([
               'submited_at' => Carbon::now()->subMinutes(10)
          ]);

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'test-session-token',
               'answers' => [],
               'essay_answers' => [],
               'force_submit' => true
          ]);

          $response->assertStatus(200)
               ->assertJson([
                    'success' => true,
                    'message' => 'Ujian berhasil diselesaikan'
               ]);
     }

     /** @test */
     public function user_cannot_submit_already_completed_exam()
     {
          Sanctum::actingAs($this->user, ['*']);

          // Mark session as completed
          $this->examSession->update(['status' => 'completed']);

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'test-session-token',
               'answers' => [],
               'essay_answers' => []
          ]);

          $response->assertStatus(404)
               ->assertJson([
                    'success' => false,
                    'message' => 'Session ujian tidak ditemukan atau sudah berakhir'
               ]);
     }

     /** @test */
     public function user_can_get_session_status()
     {
          Sanctum::actingAs($this->user, ['*']);

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/status", [
               'session_token' => 'test-session-token'
          ]);

          $response->assertStatus(200)
               ->assertJson([
                    'success' => true
               ])
               ->assertJsonStructure([
                    'data' => [
                         'session_id',
                         'status',
                         'time_remaining',
                         'started_at',
                         'end_time',
                         'is_expired'
                    ]
               ]);
     }

     /** @test */
     public function validation_works_correctly()
     {
          Sanctum::actingAs($this->user, ['*']);

          // Test missing session token
          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'answers' => [],
               'essay_answers' => []
          ]);

          $response->assertStatus(422)
               ->assertJsonValidationErrors(['session_token']);

          // Test invalid answer format
          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'test-session-token',
               'answers' => [
                    '1' => str_repeat('A', 20) // Too long
               ]
          ]);

          $response->assertStatus(422)
               ->assertJsonValidationErrors(['answers.1']);
     }

     /** @test */
     public function scoring_calculation_is_accurate()
     {
          Sanctum::actingAs($this->user, ['*']);

          // Answer all questions correctly
          $answers = [
               (string)$this->questions[0]->id => 'A', // Correct - 20 points
               (string)$this->questions[1]->id => 'B', // Correct - 20 points
               (string)$this->questions[2]->id => 'TRUE', // Correct - 20 points
               (string)$this->questions[4]->id => 'C', // Correct - 20 points
          ];

          $essayAnswers = [
               (string)$this->questions[3]->id => 'Essay answer' // 0 points (manual grading)
          ];

          $response = $this->postJson("/api/siswa/exams/{$this->exam->id}/submit", [
               'session_token' => 'test-session-token',
               'answers' => $answers,
               'essay_answers' => $essayAnswers
          ]);

          $response->assertStatus(200);

          $data = $response->json('data');
          $this->assertEquals(80, $data['total_score']); // 4 correct x 20 points
          $this->assertEquals(100, $data['max_score']); // 5 questions x 20 points
          $this->assertEquals(80, $data['percentage']);
          $this->assertEquals('B', $data['grade']['letter']);
          $this->assertEquals(5, $data['answered_questions']);
          $this->assertEquals(0, $data['unanswered_questions']);
     }
}
