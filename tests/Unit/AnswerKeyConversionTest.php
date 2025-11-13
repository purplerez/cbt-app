<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\AnswerKeyHelper;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AnswerKeyConversionTest extends TestCase
{
     use RefreshDatabase;

     /** @test */
     public function it_converts_index_to_letter()
     {
          $this->assertEquals('A', AnswerKeyHelper::indexToLetter(0));
          $this->assertEquals('B', AnswerKeyHelper::indexToLetter(1));
          $this->assertEquals('C', AnswerKeyHelper::indexToLetter(2));
          $this->assertEquals('D', AnswerKeyHelper::indexToLetter(3));
          $this->assertEquals('E', AnswerKeyHelper::indexToLetter(4));
     }

     /** @test */
     public function it_converts_letter_to_index()
     {
          $this->assertEquals(0, AnswerKeyHelper::letterToIndex('A'));
          $this->assertEquals(1, AnswerKeyHelper::letterToIndex('B'));
          $this->assertEquals(2, AnswerKeyHelper::letterToIndex('C'));
          $this->assertEquals(3, AnswerKeyHelper::letterToIndex('D'));
          $this->assertEquals(4, AnswerKeyHelper::letterToIndex('E'));
     }

     /** @test */
     public function it_handles_true_false_conversion()
     {
          // Index to letter with question type 2 (True/False)
          $this->assertEquals('T', AnswerKeyHelper::indexToLetter(0));
          $this->assertEquals('F', AnswerKeyHelper::indexToLetter(1));

          // Letter to index for True/False
          $this->assertEquals(0, AnswerKeyHelper::letterToIndex('T'));
          $this->assertEquals(1, AnswerKeyHelper::letterToIndex('F'));
     }

     /** @test */
     public function it_normalizes_answer_key_from_index_array()
     {
          // Single answer
          $result = AnswerKeyHelper::normalizeAnswerKey([0]);
          $this->assertEquals('A', $result);

          // Multiple answers
          $result = AnswerKeyHelper::normalizeAnswerKey([0, 2]);
          $this->assertEquals(['A', 'C'], $result);
     }

     /** @test */
     public function it_normalizes_answer_key_from_letter_array()
     {
          // Already in letter format
          $result = AnswerKeyHelper::normalizeAnswerKey(['A', 'C']);
          $this->assertEquals(['A', 'C'], $result);
     }

     /** @test */
     public function it_normalizes_answer_key_from_json_string()
     {
          // JSON with indices
          $result = AnswerKeyHelper::normalizeAnswerKey('[0,2]');
          $this->assertEquals(['A', 'C'], $result);

          // JSON with letters
          $result = AnswerKeyHelper::normalizeAnswerKey('["A","C"]');
          $this->assertEquals(['A', 'C'], $result);
     }

     /** @test */
     public function it_compares_answers_correctly()
     {
          // Letter vs Letter
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A', 'A'));
          $this->assertFalse(AnswerKeyHelper::compareAnswers('A', 'B'));

          // Index vs Letter (auto-normalize)
          $this->assertTrue(AnswerKeyHelper::compareAnswers(0, 'A'));
          $this->assertFalse(AnswerKeyHelper::compareAnswers(0, 'B'));

          // Array comparison
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['A', 'C'], ['A', 'C']));
          $this->assertTrue(AnswerKeyHelper::compareAnswers([0, 2], ['A', 'C']));
          $this->assertFalse(AnswerKeyHelper::compareAnswers(['A'], ['A', 'C']));
     }

     /** @test */
     public function it_calculates_partial_score_for_complex_multiple_choice()
     {
          $totalPoints = 10;

          // All correct
          $result = AnswerKeyHelper::calculatePartialScore(['A', 'C'], ['A', 'C'], $totalPoints);
          $this->assertEquals(10, $result['points']);
          $this->assertTrue($result['is_correct']);

          // Partially correct (1 out of 2)
          $result = AnswerKeyHelper::calculatePartialScore(['A'], ['A', 'C'], $totalPoints);
          $this->assertEquals(5, $result['points']);
          $this->assertFalse($result['is_correct']);

          // None correct
          $result = AnswerKeyHelper::calculatePartialScore(['B'], ['A', 'C'], $totalPoints);
          $this->assertEquals(0, $result['points']);
          $this->assertFalse($result['is_correct']);
     }

     /** @test */
     public function question_model_converts_answer_key_on_save()
     {
          // Create question with index format (simulating form input)
          $question = new Question([
               'exam_id' => 1,
               'question_type_id' => '0', // Multiple Choice
               'question_text' => 'Test Question',
               'choices' => json_encode(['Option A', 'Option B', 'Option C']),
               'answer_key' => [0], // Index format
               'points' => 10,
               'created_by' => 1
          ]);

          // Check that mutator converts to letter format
          $this->assertEquals('A', $question->getAttributes()['answer_key']);
     }

     /** @test */
     public function question_model_handles_complex_multiple_choice()
     {
          $question = new Question([
               'exam_id' => 1,
               'question_type_id' => '1', // Complex Multiple Choice
               'question_text' => 'Test Question',
               'choices' => json_encode(['Option A', 'Option B', 'Option C']),
               'answer_key' => [0, 2], // Multiple indices
               'points' => 10,
               'created_by' => 1
          ]);

          // Should be stored as JSON array of letters
          $stored = $question->getAttributes()['answer_key'];
          $decoded = json_decode($stored, true);
          $this->assertEquals(['A', 'C'], $decoded);
     }

     /** @test */
     public function question_model_handles_true_false()
     {
          $question = new Question([
               'exam_id' => 1,
               'question_type_id' => '2', // True/False
               'question_text' => 'Test Question',
               'choices' => json_encode(['Benar', 'Salah']),
               'answer_key' => [0], // 0 = True
               'points' => 10,
               'created_by' => 1
          ]);

          // Should be stored as 'T'
          $this->assertEquals('T', $question->getAttributes()['answer_key']);

          // Test False
          $question->answer_key = [1];
          $this->assertEquals('F', $question->getAttributes()['answer_key']);
     }

     /** @test */
     public function question_model_accessor_normalizes_legacy_data()
     {
          // Simulate legacy data with index format
          $question = new Question();
          $question->setRawAttributes([
               'question_type_id' => '0',
               'answer_key' => '0' // Legacy index format
          ]);

          // Accessor should convert to letter
          $this->assertEquals('A', $question->answer_key);
     }

     /** @test */
     public function letter_to_index_format_converts_arrays()
     {
          $result = AnswerKeyHelper::letterToIndexFormat(['A', 'C']);
          $this->assertEquals([0, 2], $result);

          $result = AnswerKeyHelper::letterToIndexFormat('A');
          $this->assertEquals(0, $result);
     }
}
