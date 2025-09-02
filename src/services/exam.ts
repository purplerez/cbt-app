import api from '@/lib/api';
import { ApiResponse, Exam, Question, ExamResult } from '@/types';

export const examService = {
     getExams: async (): Promise<Exam[]> => {
          const response = await api.get<ApiResponse<Exam[]>>('/exams');
          return response.data.data;
     },

     getExamById: async (id: string): Promise<Exam> => {
          const response = await api.get<ApiResponse<Exam>>(`/exams/${id}`);
          return response.data.data;
     },

     getExamQuestions: async (examId: string): Promise<Question[]> => {
          const response = await api.get<ApiResponse<Question[]>>(`/exams/${examId}/questions`);
          return response.data.data;
     },

     submitExam: async (examId: string, answers: Record<string, string>): Promise<ExamResult> => {
          const response = await api.post<ApiResponse<ExamResult>>(`/exams/${examId}/submit`, {
               answers
          });
          return response.data.data;
     },

     getExamResult: async (examId: string): Promise<ExamResult> => {
          const response = await api.get<ApiResponse<ExamResult>>(`/exams/${examId}/result`);
          return response.data.data;
     }
};
