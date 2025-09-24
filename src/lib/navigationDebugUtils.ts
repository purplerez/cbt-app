/**
 * Debug utility untuk ExamNavigation mapping
 */

import { StudentAnswer, ParsedQuestion } from '@/types';

export const navigationDebugUtils = {
     /**
      * Log mapping antara questionNumber dan questionId
      */
     logQuestionMapping: (questions: ParsedQuestion[], answers: Record<number, StudentAnswer>) => {
          if (process.env.NODE_ENV === 'development') {
               console.log('[NAV DEBUG] Question Mapping:');
               questions.forEach((question, index) => {
                    const questionNumber = index + 1;
                    const hasAnswer = !!answers[question.id];
                    const answerValue = answers[question.id]?.answer;

                    console.log(`[NAV DEBUG] Q${questionNumber} -> ID:${question.id} | HasAnswer:${hasAnswer} | Value:`, answerValue);
               });

               console.log('[NAV DEBUG] Answers by ID:', answers);
          }
     },

     /**
      * Validate answer status
      */
     validateAnswerStatus: (questionId: number, answer: StudentAnswer | undefined) => {
          if (!answer) {
               return { status: 'unanswered', reason: 'No answer object' };
          }

          if (answer.is_flagged) {
               return { status: 'flagged', reason: 'Question is flagged' };
          }

          if (Array.isArray(answer.answer)) {
               const hasAnswer = answer.answer.length > 0;
               return {
                    status: hasAnswer ? 'answered' : 'unanswered',
                    reason: hasAnswer ? `Array with ${answer.answer.length} items` : 'Empty array'
               };
          } else {
               const hasAnswer = answer.answer && answer.answer.toString().trim() !== '';
               return {
                    status: hasAnswer ? 'answered' : 'unanswered',
                    reason: hasAnswer ? `Value: "${answer.answer}"` : 'Empty or null value'
               };
          }
     },

     /**
      * Log status untuk semua questions
      */
     logAllQuestionStatus: (questions: ParsedQuestion[], answers: Record<number, StudentAnswer>) => {
          if (process.env.NODE_ENV === 'development') {
               console.log('[NAV DEBUG] Question Status Summary:');

               let answeredCount = 0;
               let flaggedCount = 0;
               let unansweredCount = 0;

               questions.forEach((question, index) => {
                    const questionNumber = index + 1;
                    const validation = navigationDebugUtils.validateAnswerStatus(question.id, answers[question.id]);

                    console.log(`[NAV DEBUG] Q${questionNumber} (ID:${question.id}): ${validation.status} - ${validation.reason}`);

                    switch (validation.status) {
                         case 'answered': answeredCount++; break;
                         case 'flagged': flaggedCount++; break;
                         case 'unanswered': unansweredCount++; break;
                    }
               });

               console.log(`[NAV DEBUG] Summary: Answered=${answeredCount}, Flagged=${flaggedCount}, Unanswered=${unansweredCount}`);
          }
     }
};

// Hook untuk debugging dalam development
export const useNavigationDebug = () => {
     if (process.env.NODE_ENV === 'development') {
          return navigationDebugUtils;
     }

     // Return no-op functions in production
     return {
          logQuestionMapping: () => { },
          validateAnswerStatus: () => ({ status: 'unknown' as const, reason: '' }),
          logAllQuestionStatus: () => { }
     };
};
