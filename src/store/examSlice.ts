import { createSlice, createAsyncThunk, PayloadAction, Draft } from '@reduxjs/toolkit';
import { examService } from '@/services/exam';
import { parseExamQuestions, findExamBySlug } from '@/lib/examUtils';
import { ParsedQuestion, StudentAnswer, AssignedExam } from '@/types';
import type { Question } from '@/types';

interface ExamState {
     currentExam: AssignedExam | null;
     questions: ParsedQuestion[];
     answers: Record<number, StudentAnswer>;
     isLoading: boolean;
     isError: boolean;
     errorMessage: string | null;
     examDuration: number;
     showSubmitModal: boolean;
     isExamEnded: boolean;
     isSubmitting: boolean;
     submitResult: {
          session_id?: number;
          exam_title?: string;
          total_score?: number;
          max_score?: number;
          percentage?: number;
          grade?: string;
          total_questions?: number;
          answered_questions?: number;
          unanswered_questions?: number;
          submission_time?: string;
     } | null;
}

const initialState: ExamState = {
     currentExam: null,
     questions: [],
     answers: {},
     isLoading: false,
     isError: false,
     errorMessage: null,
     examDuration: 0,
     showSubmitModal: false,
     isExamEnded: false,
     isSubmitting: false,
     submitResult: null,
};

export const fetchExam = createAsyncThunk(
     'exam/fetchExam',
     async ({ assigned, slug }: { assigned: AssignedExam[]; slug: string }) => {
          const exam = findExamBySlug(assigned, slug);
          if (!exam) throw new Error('Exam not found');
          const examData = await examService.examStart(Number(exam.exam_id));
          return { exam, examData };
     }
);

export const submitExam = createAsyncThunk(
     'exam/submitExam',
     async ({
          examId,
          answers,
          questions,
          forceSubmit,
          finalSubmit
     }: {
          examId: number;
          answers: Record<number, StudentAnswer>;
          questions: ParsedQuestion[];
          forceSubmit?: boolean;
          finalSubmit?: boolean;
     }) => {
          return await examService.submitExam(examId, answers, questions, { forceSubmit, finalSubmit });
     }
);

export const checkSessionStatus = createAsyncThunk(
     'exam/checkSessionStatus',
     async (examId: number) => {
          return await examService.getSessionStatus(examId);
     }
);

const examSlice = createSlice({
     name: 'exam',
     initialState,
     reducers: {
          setAnswers(state: Draft<ExamState>, action: PayloadAction<{ questionId: number; answer: string | string[] }>) {
               const { questionId, answer } = action.payload;

               // Pastikan answer tidak kosong atau undefined untuk menghindari masalah state
               if (answer !== undefined && answer !== null) {
                    state.answers[questionId] = {
                         question_id: questionId,
                         answer,
                         is_flagged: state.answers[questionId]?.is_flagged || false,
                    };
               }
          },
          setFlag(state: Draft<ExamState>, action: PayloadAction<{ questionId: number; isFlagged: boolean }>) {
               const { questionId, isFlagged } = action.payload;

               // Jika belum ada answer untuk question ini, buat entry baru
               if (!state.answers[questionId]) {
                    state.answers[questionId] = {
                         question_id: questionId,
                         answer: '',
                         is_flagged: isFlagged,
                    };
               } else {
                    // Update flag status saja, pertahankan answer yang sudah ada
                    state.answers[questionId].is_flagged = isFlagged;
               }
          },
          setShowSubmitModal(state: Draft<ExamState>, action: PayloadAction<boolean>) {
               state.showSubmitModal = action.payload;
          },
          setIsExamEnded(state: Draft<ExamState>, action: PayloadAction<boolean>) {
               state.isExamEnded = action.payload;
          },
          resetExamState() {
               console.log('Resetting exam state to initial state');
               return initialState;
          },
          setSubmitResult(state: Draft<ExamState>, action: PayloadAction<ExamState['submitResult']>) {
               state.submitResult = action.payload;
          },
     },
     extraReducers: (builder) => {
          builder
               .addCase(fetchExam.pending, (state: Draft<ExamState>) => {
                    state.isLoading = true;
                    state.isError = false;
                    state.errorMessage = null;
               })
               .addCase(
                    fetchExam.fulfilled,
                    (
                         state: Draft<ExamState>,
                         action: PayloadAction<{
                              exam: AssignedExam;
                              examData: { exam: unknown; success: boolean };
                         }>
                    ) => {
                         console.log('Exam fetch fulfilled:', {
                              examId: action.payload.exam.exam_id,
                              examTitle: action.payload.exam.title,
                              questionsCount: (action.payload.examData.exam as Question[])?.length || 0
                         });

                         state.currentExam = action.payload.exam;
                         // pastikan examData.exam bertipe Question[]
                         state.questions = parseExamQuestions(action.payload.examData.exam as Question[]);
                         state.examDuration = (action.payload.exam.duration || 120) * 60;
                         state.isLoading = false;

                         console.log('Exam state updated:', {
                              currentExamId: state.currentExam?.exam_id,
                              questionsCount: state.questions.length,
                              duration: state.examDuration
                         });
                    }
               )
               .addCase(fetchExam.rejected, (state: Draft<ExamState>, action: { error: { message?: string } }) => {
                    state.isLoading = false;
                    state.isError = true;
                    state.errorMessage = action.error?.message || 'Failed to fetch exam';
               })
               .addCase(submitExam.pending, (state: Draft<ExamState>) => {
                    state.isSubmitting = true;
                    state.isError = false;
                    state.errorMessage = null;
               })
               .addCase(submitExam.fulfilled, (state: Draft<ExamState>, action) => {
                    console.log('Exam submission successful:', {
                         examId: state.currentExam?.exam_id,
                         examTitle: state.currentExam?.title
                    });

                    state.isSubmitting = false;
                    state.isExamEnded = true;
                    state.showSubmitModal = false;

                    // Store submit result if available - payload contains full response with data field
                    if (action.payload?.data) {
                         const examData = action.payload.data;
                         state.submitResult = {
                              session_id: examData.session_id,
                              exam_title: examData.exam_title,
                              total_score: examData.total_score,
                              max_score: examData.max_score,
                              percentage: examData.percentage,
                              grade: examData.grade,
                              total_questions: examData.total_questions,
                              answered_questions: examData.answered_questions,
                              unanswered_questions: examData.unanswered_questions,
                              submission_time: examData.submission_time,
                         };

                         // Note: We're skipping localStorage storage of exam_result 
                         // since we're not showing complete page anymore
                         console.log('Exam submitted successfully:', {
                              examTitle: examData.exam_title,
                              score: examData.total_score,
                              percentage: examData.percentage
                         });
                    }

                    // Update localStorage exam status
                    if (typeof window !== 'undefined' && state.currentExam) {
                         interface ExamStatus {
                              exam_id: number;
                              status: 'not_started' | 'in_progress' | 'completed';
                              last_accessed?: string;
                         }

                         const statuses: ExamStatus[] = JSON.parse(localStorage.getItem('exam_statuses') || '[]');
                         console.log('Current exam statuses before update:', statuses);

                         const updatedStatuses = statuses.map((status) =>
                              status.exam_id === state.currentExam?.exam_id
                                   ? { ...status, status: 'completed' as const, last_accessed: new Date().toISOString() }
                                   : status
                         );

                         const examExists = statuses.some((status) => status.exam_id === state.currentExam?.exam_id);
                         if (!examExists && state.currentExam.exam_id) {
                              updatedStatuses.push({
                                   exam_id: state.currentExam.exam_id,
                                   status: 'completed',
                                   last_accessed: new Date().toISOString()
                              });
                         }

                         console.log('Updated exam statuses:', updatedStatuses);
                         console.log('Marking exam as completed:', state.currentExam.exam_id, state.currentExam.title);
                         localStorage.setItem('exam_statuses', JSON.stringify(updatedStatuses));

                         // Clear session token after successful submission
                         localStorage.removeItem('session_token');
                    }
               })
               .addCase(submitExam.rejected, (state: Draft<ExamState>, action: { error: { message?: string } }) => {
                    state.isSubmitting = false;
                    state.isError = true;
                    state.errorMessage = action.error?.message || 'Failed to submit exam';
                    // Don't set isExamEnded to true on error - keep user in exam
                    console.error('Submit exam error:', action.error?.message);
               });
     },
});

export const {
     setAnswers,
     setFlag,
     setShowSubmitModal,
     setIsExamEnded,
     resetExamState,
     setSubmitResult,
} = examSlice.actions;

export default examSlice.reducer;
