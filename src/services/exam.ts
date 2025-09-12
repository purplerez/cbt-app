import api from "@/lib/api";
import { Exam, StudentAnswer } from "@/types";

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

          console.log('exam id:', examId);
          console.log('data exam start :', response.data);
          return response.data;
     },

     submitExam: async (examId: number, answers: Record<number, StudentAnswer>) => {
          const response = await api.post(`/siswa/exams/${examId}/submit`, {
               answers: Object.values(answers)
          }, {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });

          console.log('exam submitted:', response.data);
          return response.data;
     },

     saveAnswer: async (examId: number, questionId: number, answer: StudentAnswer) => {
          // Auto-save individual answers (optional feature)
          const response = await api.post(`/siswa/exams/${examId}/questions/${questionId}/answer`, answer, {
               headers: {
                    Authorization: `Bearer ${localStorage.getItem('api_token')}`
               }
          });

          return response.data;
     }
}