'use client';

import { useState, useEffect, useCallback } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { useMutation, useQuery } from '@tanstack/react-query';
import ProtectedRoute from '@/components/ProtectedRoute';
import { examService } from '@/services/exam';
import { ParsedQuestion, StudentAnswer } from '@/types';
import { parseExamQuestions, validateAnswers, calculateExamProgress } from '@/lib/examUtils';
import QuestionCard from '@/components/exam/QuestionCard';
import ExamNavigation from '@/components/exam/ExamNavigation';
import { ExamTimer } from '@/components/exam/ExamTimer';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Modal } from '@/components/ui/modal';
import { AlertTriangle } from 'lucide-react';

export default function ExamStartPage() {
  const router = useRouter();
  const params = useParams();
  const examId = localStorage.getItem('exam_id');

  // State management
  const [questions, setQuestions] = useState<ParsedQuestion[]>([]);
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);
  const [answers, setAnswers] = useState<Record<number, StudentAnswer>>({});
  const [showSubmitModal, setShowSubmitModal] = useState(false);
  const [isExamEnded, setIsExamEnded] = useState(false);
  const [examDuration, setExamDuration] = useState(0); // in seconds

  // Fetch exam data
  const examQuery = useQuery({
    queryKey: ['exam', examId],
    queryFn: () => examService.examStart(Number(examId)),
    enabled: !!examId,
    refetchOnWindowFocus: false,
    retry: false
  });

  // Initialize exam data
  useEffect(() => {
    if (examQuery.data?.success && examQuery.data.exam) {
      const parsedQuestions = parseExamQuestions(examQuery.data.exam);
      setQuestions(parsedQuestions);

      const duration = Number(localStorage.getItem('exam_duration')) || 120; // 120 minutes default
      setExamDuration(duration * 60); // Convert to seconds
    }
  }, [examQuery.data]);

  // Submit exam mutation
  const submitExamMutation = useMutation({
    mutationFn: (examAnswers: Record<number, StudentAnswer>) => {
      if (!examId) {
        throw new Error('Exam ID not found');
      }
      return examService.submitExam(Number(examId), examAnswers);
    },
    onSuccess: () => {
      setIsExamEnded(true);
      router.push(`/exam/${params.slug}/complete`);
    },
    onError: (error) => {
      console.error('Failed to submit exam:', error);
      alert('Gagal mengirim jawaban. Silakan coba lagi.');
    }
  });

  // Handle answer changes
  const handleAnswerChange = useCallback((questionId: number, answer: string | string[]) => {
    setAnswers(prev => ({
      ...prev,
      [questionId]: {
        question_id: questionId,
        answer,
        is_flagged: prev[questionId]?.is_flagged || false
      }
    }));
  }, []);

  // Handle flag toggle
  const handleFlagToggle = useCallback((questionId: number, isFlagged: boolean) => {
    setAnswers(prev => ({
      ...prev,
      [questionId]: {
        question_id: questionId,
        answer: prev[questionId]?.answer || '',
        is_flagged: isFlagged
      }
    }));
  }, []);

  // Navigation functions
  const goToQuestion = useCallback((questionNumber: number) => {
    setCurrentQuestionIndex(questionNumber - 1);
  }, []);

  const goToPrevious = useCallback(() => {
    if (currentQuestionIndex > 0) {
      setCurrentQuestionIndex(prev => prev - 1);
    }
  }, [currentQuestionIndex]);

  const goToNext = useCallback(() => {
    if (currentQuestionIndex < questions.length - 1) {
      setCurrentQuestionIndex(prev => prev + 1);
    }
  }, [currentQuestionIndex, questions.length]);

  // Handle exam submission
  const handleSubmitExam = useCallback(() => {
    const validation = validateAnswers(answers, questions);

    if (validation.warnings.length > 0) {
      setShowSubmitModal(true);
    } else {
      submitExamMutation.mutate(answers);
    }
  }, [answers, questions, submitExamMutation]);

  // Handle time up
  const handleTimeUp = useCallback(() => {
    if (!isExamEnded) {
      console.log('Time is up! Auto-submitting exam...');
      submitExamMutation.mutate(answers);
    }
  }, [answers, isExamEnded, submitExamMutation]);

  // Confirm submission with warnings
  const confirmSubmission = () => {
    setShowSubmitModal(false);
    submitExamMutation.mutate(answers);
  };

  // Prevent leaving page accidentally
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

  // Loading state
  if (examQuery.isLoading) {
    return (
      <ProtectedRoute>
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
          <Card className="p-8 text-center">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <p className="text-gray-600">Memuat soal ujian...</p>
          </Card>
        </div>
      </ProtectedRoute>
    );
  }

  // Error state
  if (examQuery.isError || !questions.length) {
    return (
      <ProtectedRoute>
        <div className="min-h-screen flex items-center justify-center bg-gray-50">
          <Card className="p-8 text-center max-w-md">
            <AlertTriangle className="h-12 w-12 text-red-500 mx-auto mb-4" />
            <h2 className="text-xl font-semibold text-gray-800 mb-2">
              Gagal Memuat Ujian
            </h2>
            <p className="text-gray-600 mb-4">
              Terjadi kesalahan saat memuat soal ujian. Silakan coba lagi.
            </p>
            <Button onClick={() => examQuery.refetch()}>
              Coba Lagi
            </Button>
          </Card>
        </div>
      </ProtectedRoute>
    );
  }

  const currentQuestion = questions[currentQuestionIndex];
  const progress = calculateExamProgress(answers, questions.length);

  return (
    <ProtectedRoute>
      <div className="min-h-screen bg-gray-50">
        {/* Header */}
        <div className="bg-white shadow-sm border-b sticky top-0 z-40">
          <div className="max-w-7xl mx-auto px-4 py-3">
            <div className="flex justify-between items-center">
              <div>
                <h1 className="text-lg font-semibold text-gray-800">
                  Ujian Berlangsung
                </h1>
                <p className="text-sm text-gray-600">
                  Progress: {progress.answered}/{questions.length} terjawab
                </p>
              </div>

              {/* Timer */}
              <div className="flex items-center gap-4">
                <ExamTimer
                  initialTime={examDuration}
                  onTimeUp={handleTimeUp}
                  autoSubmit={true}
                />
              </div>
            </div>
          </div>
        </div>

        {/* Main Content */}
        <div className="max-w-7xl mx-auto px-4 py-6">
          <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {/* Question Content */}
            <div className="lg:col-span-3">
              <QuestionCard
                question={currentQuestion}
                questionNumber={currentQuestionIndex + 1}
                totalQuestions={questions.length}
                currentAnswer={answers[currentQuestion.id]}
                onAnswerChange={handleAnswerChange}
                onFlagToggle={handleFlagToggle}
              />
            </div>

            {/* Sidebar Navigation */}
            <div className="lg:col-span-1">
              <div className="sticky top-24">
                <ExamNavigation
                  totalQuestions={questions.length}
                  currentQuestion={currentQuestionIndex + 1}
                  answers={answers}
                  onQuestionSelect={goToQuestion}
                  onPrevious={goToPrevious}
                  onNext={goToNext}
                  onSubmit={handleSubmitExam}
                  isLastQuestion={currentQuestionIndex === questions.length - 1}
                  isFirstQuestion={currentQuestionIndex === 0}
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      {/* Submit Confirmation Modal */}
      <Modal
        isOpen={showSubmitModal}
        onClose={() => setShowSubmitModal(false)}
      >
        <div className="space-y-4">
          <div className="text-center mb-4">
            <AlertTriangle className="h-12 w-12 text-orange-500 mx-auto mb-2" />
            <h3 className="text-lg font-semibold text-gray-900">
              Konfirmasi Selesai Ujian
            </h3>
          </div>

          <div className="flex items-start gap-3">
            <div>
              <p className="text-gray-800 mb-2">
                Anda akan menyelesaikan ujian dengan kondisi berikut:
              </p>
              <ul className="text-sm text-gray-600 space-y-1">
                <li>• Soal terjawab: {progress.answered}/{questions.length}</li>
                <li>• Soal ditandai: {progress.flagged}</li>
                <li>• Soal belum dijawab: {progress.unanswered}</li>
              </ul>

              {progress.unanswered > 0 && (
                <p className="text-orange-600 text-sm mt-2">
                  Masih ada {progress.unanswered} soal yang belum dijawab.
                </p>
              )}
            </div>
          </div>

          <div className="flex gap-3 pt-4">
            <Button
              variant="outline"
              onClick={() => setShowSubmitModal(false)}
              className="flex-1"
            >
              Lanjut Mengerjakan
            </Button>
            <Button
              onClick={confirmSubmission}
              disabled={submitExamMutation.isPending}
              className="flex-1 bg-green-600 hover:bg-green-700"
            >
              {submitExamMutation.isPending ? 'Mengirim...' : 'Selesai Ujian'}
            </Button>
          </div>
        </div>
      </Modal>
    </ProtectedRoute>
  );
}