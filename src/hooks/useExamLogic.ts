'use client';

import { useEffect, useCallback, useState } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { fetchExam, setAnswers, setFlag, setShowSubmitModal, submitExam, resetExamState } from '@/store/examSlice';
import { getCurrentUser } from '@/store/authSlice';
import { validateAnswers } from '@/lib/examUtils';
import { useExamDebug } from '@/lib/examDebugUtils';

export const useExamLogic = () => {
     const router = useRouter();
     const params = useParams();
     const slug = params.slug as string;
     const dispatch = useAppDispatch();
     const debugUtils = useExamDebug();

     const { dashboardData: userData } = useAppSelector((state) => state.auth);
     const {
          currentExam,
          questions,
          answers,
          isLoading,
          isError,
          examDuration,
          showSubmitModal,
          isExamEnded,
          isSubmitting,
     } = useAppSelector((state) => state.exam);

     const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);

     // Session monitoring temporarily disabled to prevent false positive modals
     // TODO: Re-enable when backend session management is properly integrated

     // Disable session expiration handling for now - will be enabled after backend integration
     // This prevents false positive session expired modals
     useEffect(() => {
          // Temporarily disabled to prevent false positives
          // TODO: Enable after proper backend session management is ready
          console.log('Session monitoring temporarily disabled');
     }, []);

     // Reset exam state when slug changes (navigating between exams)
     useEffect(() => {
          console.log('Slug changed to:', slug, '- Resetting exam state');
          dispatch(resetExamState());
     }, [slug, dispatch]);

     // Fetch user and exam data on mount
     useEffect(() => {
          dispatch(getCurrentUser());
     }, [dispatch]);

     useEffect(() => {
          if (userData?.assigned && slug) {
               console.log('Fetching exam for slug:', slug);
               console.log('Available exams:', userData.assigned.map(e => ({ id: e.exam_id, title: e.title })));
               dispatch(fetchExam({ assigned: userData.assigned, slug }));
          }
     }, [userData, slug, dispatch]);

     // Navigate to dashboard when exam ends (skip complete page)
     useEffect(() => {
          if (isExamEnded && !isSubmitting) {
               console.log('Exam ended, navigating to dashboard to continue with next exam');

               // Add small delay to ensure state is properly updated
               setTimeout(() => {
                    // Clear current exam data
                    localStorage.removeItem('session_token');
                    localStorage.removeItem('exam_result');
                    localStorage.removeItem('current_exam_slug');

                    // Navigate to dashboard
                    router.push('/dashboard');
               }, 500);
          }
     }, [isExamEnded, isSubmitting, router]);

     // Prevent page unload during exam
     useEffect(() => {
          const handleBeforeUnload = (e: BeforeUnloadEvent) => {
               if (!isExamEnded) {
                    e.preventDefault();
                    e.returnValue = 'Anda sedang mengerjakan ujian. Yakin ingin meninggalkan halaman?';
               }
          };
          window.addEventListener('beforeunload', handleBeforeUnload);
          return () => window.removeEventListener('beforeunload', handleBeforeUnload);
     }, [isExamEnded]);

     // Answer and flag handlers
     const handleAnswerChange = useCallback((questionId: number, answer: string | string[]) => {
          // Debug logging untuk development
          debugUtils.logAnswers(answers, questionId);

          dispatch(setAnswers({ questionId, answer }));

          // Check for potential conflicts setelah update
          setTimeout(() => {
               const currentAnswers = { ...answers, [questionId]: { question_id: questionId, answer } };
               debugUtils.checkAnswerConflicts(currentAnswers);
          }, 0);
     }, [dispatch, answers, debugUtils]);

     const handleFlagToggle = useCallback((questionId: number, isFlagged: boolean) => {
          dispatch(setFlag({ questionId, isFlagged }));
     }, [dispatch]);

     // Navigation handlers
     const goToQuestion = useCallback((questionNumber: number) => {
          setCurrentQuestionIndex(questionNumber - 1);
     }, []);

     const goToPrevious = useCallback(() => {
          if (currentQuestionIndex > 0) {
               setCurrentQuestionIndex((prev) => prev - 1);
          }
     }, [currentQuestionIndex]);

     const goToNext = useCallback(() => {
          if (currentQuestionIndex < questions.length - 1) {
               setCurrentQuestionIndex((prev) => prev + 1);
          }
     }, [currentQuestionIndex, questions.length]);

     // Submit handlers
     const handleSubmitExam = useCallback(() => {
          const validation = validateAnswers(answers, questions);
          if (validation.warnings.length > 0) {
               dispatch(setShowSubmitModal(true));
          } else {
               if (currentExam?.exam_id) {
                    dispatch(submitExam({
                         examId: currentExam.exam_id,
                         answers,
                         questions,
                         finalSubmit: true
                    }));
               }
          }
     }, [answers, questions, dispatch, currentExam]);

     const handleTimeUp = useCallback(() => {
          if (!isExamEnded && currentExam?.exam_id) {
               // Force submit when time is up
               dispatch(submitExam({
                    examId: currentExam.exam_id,
                    answers,
                    questions,
                    forceSubmit: true,
                    finalSubmit: true
               }));
          }
     }, [isExamEnded, currentExam, answers, questions, dispatch]);

     const confirmSubmission = useCallback(() => {
          dispatch(setShowSubmitModal(false));
          if (currentExam?.exam_id) {
               dispatch(submitExam({
                    examId: currentExam.exam_id,
                    answers,
                    questions,
                    finalSubmit: true
               }));
          }
     }, [dispatch, currentExam, answers, questions]);

     // Retry handlers
     const retryFetchExam = useCallback(() => {
          if (userData?.assigned) {
               dispatch(fetchExam({ assigned: userData.assigned, slug }));
          }
     }, [userData, slug, dispatch]);

     const goBackToExamList = useCallback(() => {
          router.push('/exam');
     }, [router]);

     // Computed values
     const currentQuestion = questions[currentQuestionIndex];
     const isFirstQuestion = currentQuestionIndex === 0;
     const isLastQuestion = currentQuestionIndex === questions.length - 1;

     const closeSessionExpiredModal = useCallback(() => {
          // Session expired modal is disabled for now
          router.push('/dashboard');
     }, [router]);

     return {
          // State
          userData,
          currentExam,
          questions,
          answers,
          isLoading,
          isError,
          examDuration,
          showSubmitModal,
          isExamEnded,
          isSubmitting,
          currentQuestionIndex,
          currentQuestion,
          isFirstQuestion,
          isLastQuestion,
          slug,
          showSessionExpired: false, // Always false for now
          isSessionValid: true, // Always true for now (no session monitoring)

          // Handlers
          handleAnswerChange,
          handleFlagToggle,
          goToQuestion,
          goToPrevious,
          goToNext,
          handleSubmitExam,
          handleTimeUp,
          confirmSubmission,
          retryFetchExam,
          goBackToExamList,
          closeSessionExpiredModal,
     };
};
