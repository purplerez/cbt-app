import api from "@/lib/api";
import { Exam, StudentAnswer, AssignedExam, ParsedQuestion, ExamSubmitOptions, AutoSaveResponse } from "@/types";
import { findExamBySlug } from "@/lib/examUtils";

export const examService = {
     examStart: async (examId: number): Promise<Exam> => {
          if (!examId) {
               throw new Error('Exam ID is required');
          }
          const response = await api.post<Exam>(`/siswa/exams/${examId}/start`, {}, {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });

          // Store session_token if provided in response
          if (response.data && typeof response.data === 'object' && 'session_token' in response.data) {
               const responseWithToken = response.data as { session_token?: string };
               if (responseWithToken.session_token) {
                    localStorage.setItem('session_token', responseWithToken.session_token);
               }
          }

          console.log('exam id:', examId);
          console.log('data exam start :', response.data);
          return response.data;
     },

     examStartBySlug: async (slug: string, assignedExams: AssignedExam[]): Promise<Exam> => {
          const exam = findExamBySlug(assignedExams, slug);
          if (!exam) {
               throw new Error(`Exam not found for slug: ${slug}`);
          }

          localStorage.setItem('exam_id', exam.exam_id.toString());
          localStorage.setItem('exam_duration', exam.duration.toString());
          localStorage.setItem('current_exam_slug', slug);

          return examService.examStart(exam.exam_id);
     },

     getExamIdFromSlug: (slug: string, assignedExams: AssignedExam[]): number | null => {
          const exam = findExamBySlug(assignedExams, slug);
          return exam ? exam.exam_id : null;
     },

     submitExam: async (
          examId: number,
          answers: Record<number, StudentAnswer>,
          questions: ParsedQuestion[],
          options: ExamSubmitOptions = {}
     ) => {
          if (!examId || examId <= 0) {
               throw new Error('Exam ID tidak valid.');
          }

          const sessionToken = localStorage.getItem('session_token');

          if (!sessionToken) {
               throw new Error('Session token tidak ditemukan. Silakan mulai ulang ujian.');
          }

          // Separate multiple choice and essay answers based on question type
          const multipleChoiceAnswers: Record<string, string> = {};
          const essayAnswers: Record<string, string> = {};

          Object.values(answers).forEach(answer => {
               const question = questions.find(q => q.id === answer.question_id);

               if (question && answer.answer !== undefined && answer.answer !== null && answer.answer !== '') {
                    // Question type: "0" = Multiple Choice Complex, "1" = Multiple Choice Single, "2" = Essay
                    if (question.question_type_id === "2") {
                         // Essay question - store as string (max 5000 chars as per backend validation)
                         const essayAnswer = Array.isArray(answer.answer)
                              ? answer.answer.join(', ')
                              : String(answer.answer || '');

                         // Only add if not empty
                         if (essayAnswer.trim()) {
                              essayAnswers[answer.question_id.toString()] = essayAnswer.substring(0, 5000);
                         }
                    } else {
                         // Multiple choice questions - store as string (max 10 chars as per backend validation)
                         let mcAnswer = '';

                         if (Array.isArray(answer.answer)) {
                              // For multiple choice complex (type "0"), join array elements
                              mcAnswer = answer.answer.filter(a => a !== '').join(',');
                         } else {
                              // For single choice (type "1"), use as is
                              mcAnswer = String(answer.answer || '');
                         }

                         // Only add if not empty and truncate to 10 characters
                         if (mcAnswer.trim()) {
                              multipleChoiceAnswers[answer.question_id.toString()] = mcAnswer.substring(0, 10);
                         }
                    }
               }
          });

          // Validate that we have at least some answers or it's a force submit
          if (Object.keys(multipleChoiceAnswers).length === 0 && Object.keys(essayAnswers).length === 0 && !options.forceSubmit) {
               throw new Error('Tidak ada jawaban yang ditemukan. Silakan jawab minimal satu soal atau gunakan force submit.');
          }

          // Format payload according to backend validation rules
          const payload: {
               session_token: string;
               answers?: Record<string, string>;
               essay_answers?: Record<string, string>;
               force_submit: boolean;
               final_submit: boolean;
          } = {
               session_token: sessionToken,
               force_submit: options.forceSubmit || false,
               final_submit: options.finalSubmit || false
          };

          // Only include answers if there are any
          if (Object.keys(multipleChoiceAnswers).length > 0) {
               payload.answers = multipleChoiceAnswers;
          }

          if (Object.keys(essayAnswers).length > 0) {
               payload.essay_answers = essayAnswers;
          }

          // Debug logging
          console.log('Submit Exam Debug Info:', {
               examId,
               sessionToken: sessionToken?.substring(0, 10) + '...',
               payload,
               answersCount: Object.keys(answers).length,
               questionsCount: questions.length,
               multipleChoiceCount: Object.keys(multipleChoiceAnswers).length,
               essayCount: Object.keys(essayAnswers).length
          });

          try {
               const response = await api.post(`/siswa/exams/${examId}/submit`, payload, {
                    headers: {
                         Authorization: `Bearer ${localStorage.getItem('api_token')}`
                    }
               });

               console.log('exam submitted successfully:', response.data);

               // Verify response structure
               if (!response.data || typeof response.data !== 'object' || response.data.success !== true) {
                    console.error('Response validation failed, received:', response.data);
                    throw new Error('Response tidak valid dari server');
               }

               // Clean up session token on successful submit if final submit
               if (options.finalSubmit) {
                    localStorage.removeItem('session_token');
               }

               return response.data;
          } catch (error: unknown) {
               console.error('Submit exam error:', error);

               // Handle specific error cases
               if (error && typeof error === 'object' && 'response' in error) {
                    const axiosError = error as { response?: { status?: number; data?: { message?: string } } };

                    console.log('Backend Error Details:', {
                         status: axiosError.response?.status,
                         data: axiosError.response?.data,
                         examId,
                         sessionToken: sessionToken?.substring(0, 10) + '...'
                    });

                    if (axiosError.response?.status === 500) {
                         throw new Error('Terjadi kesalahan saat menyimpan hasil ujian. Silakan coba lagi.');
                    } else if (axiosError.response?.status === 405) {
                         throw new Error('Metode request tidak didukung. Silakan refresh halaman dan coba lagi.');
                    } else if (axiosError.response?.status === 404) {
                         throw new Error('Endpoint ujian tidak ditemukan. Silakan hubungi administrator.');
                    } else if (axiosError.response?.status === 422) {
                         throw new Error('Data yang dikirim tidak valid. Silakan periksa jawaban Anda.');
                    } else if (axiosError.response?.data?.message) {
                         throw new Error(axiosError.response.data.message);
                    }
               }

               const errorMessage = error && typeof error === 'object' && 'message' in error
                    ? String((error as { message: string }).message)
                    : 'Gagal mengirim jawaban ujian. Silakan coba lagi.';

               throw new Error(errorMessage);
          }
     },

     getSessionStatus: async (examId: number) => {
          const sessionToken = localStorage.getItem('session_token');

          if (!sessionToken) {
               throw new Error('Session token tidak ditemukan.');
          }

          const response = await api.post(`/siswa/exams/${examId}/status`, {
               session_token: sessionToken
          }, {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });

          return response.data;
     },

     saveAnswer: async (examId: number, questionId: number, answer: StudentAnswer) => {
          const response = await api.post(`/siswa/exams/${examId}/questions/${questionId}/answer`, answer, {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });

          return response.data;
     },

     // Auto-save answers without final submission (for periodic saves)
     autoSaveAnswers: async (
          examId: number,
          answers: Record<number, StudentAnswer>,
          questions: ParsedQuestion[]
     ): Promise<AutoSaveResponse> => {
          const sessionToken = localStorage.getItem('session_token');

          if (!sessionToken) {
               throw new Error('Session token tidak ditemukan. Silakan mulai ulang ujian.');
          }

          // Separate multiple choice and essay answers based on question type
          const multipleChoiceAnswers: Record<string, string> = {};
          const essayAnswers: Record<string, string> = {};

          Object.values(answers).forEach(answer => {
               const question = questions.find(q => q.id === answer.question_id);

               if (question) {
                    if (question.question_type_id === "2") {
                         // Essay question
                         const essayAnswer = Array.isArray(answer.answer)
                              ? answer.answer.join(', ')
                              : String(answer.answer || '');

                         essayAnswers[answer.question_id.toString()] = essayAnswer.substring(0, 5000);
                    } else {
                         // Multiple choice questions
                         let mcAnswer = '';

                         if (Array.isArray(answer.answer)) {
                              mcAnswer = answer.answer.join(',');
                         } else {
                              mcAnswer = String(answer.answer || '');
                         }

                         multipleChoiceAnswers[answer.question_id.toString()] = mcAnswer.substring(0, 10);
                    }
               }
          });

          const payload = {
               session_token: sessionToken,
               answers: Object.keys(multipleChoiceAnswers).length > 0 ? multipleChoiceAnswers : null,
               essay_answers: Object.keys(essayAnswers).length > 0 ? essayAnswers : null,
               force_submit: false,
               final_submit: false // This is auto-save, not final submission
          };

          const response = await api.post(`/siswa/exams/${examId}/submit`, payload, {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });

          return response.data;
     }
}