'use client';

import React from 'react';
import ProtectedRoute from '@/components/ProtectedRoute';
import { LoadingExamScreen } from '@/components/exam/LoadingExamScreen';
import { ErrorExamScreen } from '@/components/exam/ErrorExamScreen';
import { ExamProgressHeader } from '@/components/exam/ExamProgressHeader';
import { ExamMainContent } from '@/components/exam/ExamMainContent';
import { ExamNavigationFooter } from '@/components/exam/ExamNavigationFooter';
import { ExamSubmitModal } from '@/components/exam/ExamSubmitModal';
// import { SessionExpiredModal } from '@/components/exam/SessionExpiredModal'; // Temporarily disabled
import { useExamLogic } from '@/hooks/useExamLogic';
import { useAppDispatch } from '@/store/hooks';
import { resetExamState } from '@/store/examSlice';

export default function ExamStartPage() {
     const dispatch = useAppDispatch();

     // Reset exam state when entering start page
     React.useEffect(() => {
          console.log('Entering exam start page - Resetting state');
          dispatch(resetExamState());

          // Clear session tokens and previous exam data
          localStorage.removeItem('exam_result');
     }, [dispatch]);

     const {
          userData,
          currentExam,
          questions,
          isLoading,
          isError,
          isExamEnded,
          currentQuestionIndex,
          currentQuestion,
          isFirstQuestion,
          isLastQuestion,
          slug,
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
          // showSessionExpired, // Temporarily disabled
          // closeSessionExpiredModal, // Temporarily disabled
     } = useExamLogic();

     // Loading state or during exam end transition
     if (!userData || !currentExam || isLoading || isExamEnded) {
          return <LoadingExamScreen />;
     }

     // Exam not found
     if (userData?.assigned && !currentExam) {
          return (
               <ErrorExamScreen
                    title="Ujian Tidak Ditemukan"
                    message={`Ujian dengan slug "${slug}" tidak ditemukan. Silakan kembali ke halaman ujian.`}
                    onRetry={goBackToExamList}
                    retryButtonText="Kembali ke Ujian"
               />
          );
     }

     // Error loading exam - but not if exam has ended (prevents error screen during redirect)
     if ((isError || (!questions.length && !isLoading)) && !isExamEnded) {
          return (
               <ErrorExamScreen
                    title="Gagal Memuat Ujian"
                    message="Terjadi kesalahan saat memuat soal ujian. Silakan coba lagi."
                    onRetry={retryFetchExam}
               />
          );
     }

     return (
          <ProtectedRoute>
               <div className="min-h-screen bg-gray-50">
                    <ExamProgressHeader onTimeUp={handleTimeUp} />

                    <ExamMainContent
                         currentQuestionIndex={currentQuestionIndex}
                         currentQuestion={currentQuestion}
                         onAnswerChange={handleAnswerChange}
                         onFlagToggle={handleFlagToggle}
                         onQuestionSelect={goToQuestion}
                         onPrevious={goToPrevious}
                         onNext={goToNext}
                         onSubmit={handleSubmitExam}
                         isFirstQuestion={isFirstQuestion}
                         isLastQuestion={isLastQuestion}
                    />
               </div>

               <ExamNavigationFooter
                    onPrevious={goToPrevious}
                    onNext={goToNext}
                    onSubmit={handleSubmitExam}
                    isFirstQuestion={isFirstQuestion}
                    isLastQuestion={isLastQuestion}
               />

               <ExamSubmitModal onConfirmSubmit={confirmSubmission} />
               {/* SessionExpiredModal temporarily disabled to prevent false positives */}
               {/* 
               <SessionExpiredModal 
                    isOpen={showSessionExpired}
                    onClose={closeSessionExpiredModal}
               />
               */}
          </ProtectedRoute>
     );
}