import { Question, ParsedQuestion, StudentAnswer } from '@/types';

export const parseQuestion = (question: Question): ParsedQuestion => {
     try {
          // Parse choices from JSON string to object
          const choices = JSON.parse(question.choices);

          // Parse answer_key from JSON string to array
          const answer_key = JSON.parse(question.answer_key);

          // Parse points from string to number
          const points = parseFloat(question.points);

          return {
               ...question,
               choices,
               answer_key,
               points
          };
     } catch (error) {
          console.error('Error parsing question:', error);
          // Return question with fallback values
          return {
               ...question,
               choices: {},
               answer_key: [],
               points: 0
          };
     }
};

export const parseExamQuestions = (questions: Question[]): ParsedQuestion[] => {
     return questions.map(parseQuestion);
};

export const formatTimeRemaining = (seconds: number): string => {
     const hours = Math.floor(seconds / 3600);
     const minutes = Math.floor((seconds % 3600) / 60);
     const secs = seconds % 60;

     if (hours > 0) {
          return `${hours}j ${minutes}m ${secs}d`;
     }
     if (minutes > 0) {
          return `${minutes}m ${secs}d`;
     }
     return `${secs}d`;
};

export const calculateExamProgress = (answers: Record<number, StudentAnswer>, totalQuestions: number) => {
     const answeredCount = Object.values(answers).filter(answer => {
          if (Array.isArray(answer.answer)) {
               return answer.answer.length > 0;
          }
          return answer.answer && answer.answer.trim() !== '';
     }).length;

     const flaggedCount = Object.values(answers).filter(answer => answer.is_flagged).length;

     return {
          answered: answeredCount,
          flagged: flaggedCount,
          unanswered: totalQuestions - answeredCount,
          percentage: Math.round((answeredCount / totalQuestions) * 100)
     };
};

export const validateAnswers = (answers: Record<number, StudentAnswer>, questions: ParsedQuestion[]) => {
     const validation = {
          isValid: true,
          errors: [] as string[],
          warnings: [] as string[]
     };

     // Check if all required questions are answered
     questions.forEach((question, index) => {
          const answer = answers[question.id];

          if (!answer) {
               validation.warnings.push(`Soal ${index + 1} belum dijawab`);
               return;
          }

          // Validate based on question type
          switch (question.question_type_id) {
               case '0': // Multiple choice complex
                    if (!Array.isArray(answer.answer) || answer.answer.length === 0) {
                         validation.warnings.push(`Soal ${index + 1} (pilihan ganda kompleks) belum dijawab`);
                    }
                    break;

               case '1': // Single choice
                    if (!answer.answer || (typeof answer.answer === 'string' && answer.answer.trim() === '')) {
                         validation.warnings.push(`Soal ${index + 1} (pilihan ganda) belum dijawab`);
                    }
                    break;

               case '2': // Essay
                    if (!answer.answer || (typeof answer.answer === 'string' && answer.answer.trim() === '')) {
                         validation.warnings.push(`Soal ${index + 1} (essay) belum dijawab`);
                    } else if (typeof answer.answer === 'string' && answer.answer.trim().length < 10) {
                         validation.warnings.push(`Soal ${index + 1} (essay) mungkin terlalu singkat`);
                    }
                    break;
          }
     });

     return validation;
};
