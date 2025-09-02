import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { examService } from '@/services/exam';

export const useExams = () => {
     return useQuery({
          queryKey: ['exams'],
          queryFn: examService.getExams
     });
};

export const useExam = (id: string) => {
     return useQuery({
          queryKey: ['exam', id],
          queryFn: () => examService.getExamById(id),
          enabled: !!id
     });
};

export const useExamQuestions = (examId: string) => {
     return useQuery({
          queryKey: ['exam-questions', examId],
          queryFn: () => examService.getExamQuestions(examId),
          enabled: !!examId
     });
};

export const useSubmitExam = () => {
     const queryClient = useQueryClient();

     return useMutation({
          mutationFn: ({ examId, answers }: { examId: string; answers: Record<string, string> }) =>
               examService.submitExam(examId, answers),
          onSuccess: (data, variables) => {
               queryClient.invalidateQueries({ queryKey: ['exam-result', variables.examId] });
          }
     });
};

export const useExamResult = (examId: string) => {
     return useQuery({
          queryKey: ['exam-result', examId],
          queryFn: () => examService.getExamResult(examId),
          enabled: !!examId
     });
};
