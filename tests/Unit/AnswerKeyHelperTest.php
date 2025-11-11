<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\AnswerKeyHelper;

class AnswerKeyHelperTest extends TestCase
{
     /**
      * Test index to letter conversion
      */
     public function test_index_to_letter_conversion()
     {
          $this->assertEquals('A', AnswerKeyHelper::indexToLetter(0));
          $this->assertEquals('B', AnswerKeyHelper::indexToLetter(1));
          $this->assertEquals('C', AnswerKeyHelper::indexToLetter(2));
          $this->assertEquals('Z', AnswerKeyHelper::indexToLetter(25));
     }

     /**
      * Test letter to index conversion
      */
     public function test_letter_to_index_conversion()
     {
          $this->assertEquals(0, AnswerKeyHelper::letterToIndex('A'));
          $this->assertEquals(1, AnswerKeyHelper::letterToIndex('B'));
          $this->assertEquals(2, AnswerKeyHelper::letterToIndex('C'));
          $this->assertEquals(25, AnswerKeyHelper::letterToIndex('Z'));
     }

     /**
      * Test normalize answer key with index format
      */
     public function test_normalize_answer_key_index_format()
     {
          // Single index
          $this->assertEquals('A', AnswerKeyHelper::normalizeAnswerKey(0));
          $this->assertEquals('C', AnswerKeyHelper::normalizeAnswerKey(2));

          // JSON string with index array
          $this->assertEquals(['A', 'C'], AnswerKeyHelper::normalizeAnswerKey('[0,2]'));
          $this->assertEquals(['A', 'B', 'C'], AnswerKeyHelper::normalizeAnswerKey('[0,1,2]'));
     }

     /**
      * Test normalize answer key with letter format
      */
     public function test_normalize_answer_key_letter_format()
     {
          // Single letter
          $this->assertEquals('A', AnswerKeyHelper::normalizeAnswerKey('A'));
          $this->assertEquals('C', AnswerKeyHelper::normalizeAnswerKey('c')); // lowercase

          // JSON string with letter array
          $this->assertEquals(['A', 'C'], AnswerKeyHelper::normalizeAnswerKey('["A","C"]'));
          $this->assertEquals(['A', 'B'], AnswerKeyHelper::normalizeAnswerKey('["a","b"]'));
     }

     /**
      * Test normalize student answer
      */
     public function test_normalize_student_answer()
     {
          // Single answer
          $this->assertEquals('A', AnswerKeyHelper::normalizeStudentAnswer('A'));
          $this->assertEquals('B', AnswerKeyHelper::normalizeStudentAnswer('b'));
          $this->assertEquals('A', AnswerKeyHelper::normalizeStudentAnswer(0));

          // Multiple answers
          $this->assertEquals(['A', 'C'], AnswerKeyHelper::normalizeStudentAnswer([0, 2]));
          $this->assertEquals(['A', 'B'], AnswerKeyHelper::normalizeStudentAnswer(['A', 'B']));

          // Null answer
          $this->assertNull(AnswerKeyHelper::normalizeStudentAnswer(null));
          $this->assertNull(AnswerKeyHelper::normalizeStudentAnswer(''));
     }

     /**
      * Test compare answers - single choice
      */
     public function test_compare_answers_single_choice()
     {
          // Student answer in letter, key in index
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A', 0));
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A', '[0]'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers('C', 2));

          // Student answer in letter, key in letter
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A', 'A'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers('a', 'A')); // case insensitive

          // Wrong answers
          $this->assertFalse(AnswerKeyHelper::compareAnswers('A', 1));
          $this->assertFalse(AnswerKeyHelper::compareAnswers('A', 'B'));
          $this->assertFalse(AnswerKeyHelper::compareAnswers('B', '[0]'));
     }

     /**
      * Test compare answers - multiple choice
      */
     public function test_compare_answers_multiple_choice()
     {
          // Student answer in letter array, key in index array
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['A', 'C'], '[0,2]'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['A', 'B', 'C'], '[0,1,2]'));

          // Student answer in letter array, key in letter array
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['A', 'C'], '["A","C"]'));

          // Order doesn't matter (sorted internally)
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['C', 'A'], '[0,2]'));

          // Wrong answers
          $this->assertFalse(AnswerKeyHelper::compareAnswers(['A', 'B'], '[0,2]')); // B instead of C
          $this->assertFalse(AnswerKeyHelper::compareAnswers(['A'], '[0,2]')); // Missing C
     }

     /**
      * Test calculate partial score - all correct
      */
     public function test_calculate_partial_score_all_correct()
     {
          $result = AnswerKeyHelper::calculatePartialScore(['A', 'C'], '[0,2]', 10);

          $this->assertEquals(10.0, $result['points']);
          $this->assertTrue($result['is_correct']);
          $this->assertEquals(2, $result['matched']);
          $this->assertEquals(2, $result['total']);
          $this->assertEquals(100, $result['percentage']);
     }

     /**
      * Test calculate partial score - partial correct
      */
     public function test_calculate_partial_score_partial_correct()
     {
          // Student answered 2 out of 3 correct
          $result = AnswerKeyHelper::calculatePartialScore(['A', 'C'], '[0,1,2]', 10);

          $this->assertEquals(6.67, $result['points']);
          $this->assertFalse($result['is_correct']); // Not fully correct
          $this->assertEquals(2, $result['matched']);
          $this->assertEquals(3, $result['total']);
          $this->assertEquals(66.67, $result['percentage']);
     }

     /**
      * Test calculate partial score - one correct
      */
     public function test_calculate_partial_score_one_correct()
     {
          // Student answered 1 out of 3 correct
          $result = AnswerKeyHelper::calculatePartialScore(['A'], '[0,1,2]', 9);

          $this->assertEquals(3.0, $result['points']);
          $this->assertFalse($result['is_correct']);
          $this->assertEquals(1, $result['matched']);
          $this->assertEquals(3, $result['total']);
          $this->assertEquals(33.33, $result['percentage']);
     }

     /**
      * Test calculate partial score - none correct
      */
     public function test_calculate_partial_score_none_correct()
     {
          // Student answered wrong
          $result = AnswerKeyHelper::calculatePartialScore(['D'], '[0,1,2]', 10);

          $this->assertEquals(0.0, $result['points']);
          $this->assertFalse($result['is_correct']);
          $this->assertEquals(0, $result['matched']);
          $this->assertEquals(3, $result['total']);
          $this->assertEquals(0, $result['percentage']);
     }

     /**
      * Test edge case - empty student answer
      */
     public function test_edge_case_empty_student_answer()
     {
          $this->assertFalse(AnswerKeyHelper::compareAnswers(null, 'A'));
          $this->assertFalse(AnswerKeyHelper::compareAnswers('', 'A'));

          $result = AnswerKeyHelper::calculatePartialScore(null, '[0,1,2]', 10);
          $this->assertEquals(0.0, $result['points']);
     }

     /**
      * Test edge case - mixed format
      */
     public function test_edge_case_mixed_format()
     {
          // Student sends index, key is letter (unusual but should work)
          $this->assertTrue(AnswerKeyHelper::compareAnswers(0, 'A'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers([0, 2], '["A","C"]'));
     }

     /**
      * Test comma-separated string for complex multiple choice
      */
     public function test_comma_separated_string()
     {
          // Student answer as comma-separated string
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A,C', '[0,2]'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A,C', '["A","C"]'));

          // With spaces
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A, C', '[0,2]'));

          // Order doesn't matter (sorted internally)
          $this->assertTrue(AnswerKeyHelper::compareAnswers('C,A', '[0,2]'));
     }

     /**
      * Test backward compatibility
      */
     public function test_backward_compatibility()
     {
          // Old format (index) should still work
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A', '[0]'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['A', 'C'], '[0,2]'));

          // New format (letter) should work
          $this->assertTrue(AnswerKeyHelper::compareAnswers('A', '"A"'));
          $this->assertTrue(AnswerKeyHelper::compareAnswers(['A', 'C'], '["A","C"]'));
     }
}
