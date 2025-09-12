import { useMutation } from '@tanstack/react-query';
import { examService, SubmitAnswerData, ExamResult } from '@/services/exam';
import { Exam } from '@/types';
import { useState } from 'react';

export function useExam() {
     const [currentExamId, setCurrentExamId] = useState<string | null>(null);
     const [examData, setExamData] = useState<Exam | null>(null);
     const [examResult, setExamResult] = useState<ExamResult | null>(null);

     const startExamMutation = useMutation({
          mutationFn: examService.examStart,
          onSuccess: (data) => {
               setExamData(data);
               console.log('Exam started successfully:', data);
          },
          onError: (error) => {
               console.error('Failed to start exam:', error);
          }
     });

     // const submitExamMutation = useMutation({
     //      mutationFn: examService.submitExam,
     //      onSuccess: (result) => {
     //           setExamResult(result);
     //           console.log('Exam submitted successfully:', result);
     //      },
     //      onError: (error) => {
     //           console.error('Failed to submit exam:', error);
     //      }
     // });

     const startExam = async (examId: string) => {
          setCurrentExamId(examId);
          return await startExamMutation.mutateAsync(examId);
     };

     // const submitExam = async (answers: { [key: number]: string }) => {
     //      if (!currentExamId) {
     //           throw new Error('No exam ID available');
     //      }

     //      const formattedAnswers = Object.entries(answers).map(([questionId, answer]) => ({
     //           question_id: parseInt(questionId),
     //           answer: answer
     //      }));

     //      const submitData: SubmitAnswerData = {
     //           exam_id: currentExamId,
     //           answers: formattedAnswers
     //      };

     //      return await submitExamMutation.mutateAsync(submitData);
     // };

     const resetExam = () => {
          setCurrentExamId(null);
          setExamData(null);
          setExamResult(null);
     };

     return {
          examData,
          examResult,
          currentExamId,
          startExam,
          // submitExam,
          resetExam,
          isStartingExam: startExamMutation.isPending,
          // isSubmittingExam: submitExamMutation.isPending,
          startExamError: startExamMutation.error,
          // submitExamError: submitExamMutation.error,
          isExamStarted: !!examData?.exam && examData.exam.length > 0,
          isExamSubmitted: !!examResult
     };
}
