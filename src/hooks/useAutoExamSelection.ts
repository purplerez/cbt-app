'use client';

import { useEffect, useState, useCallback } from 'react';
import { useRouter } from 'next/navigation';
import { AssignedExam } from '@/types';

interface UseAutoExamSelectionProps {
     assignedExams?: AssignedExam[];
     currentExamId?: string;
}

interface ExamStatus {
     exam_id: number;
     status: 'not_started' | 'in_progress' | 'completed';
     last_accessed?: string;
}

export const useAutoExamSelection = ({ assignedExams, currentExamId }: UseAutoExamSelectionProps) => {
     const router = useRouter();
     const [isAutoSelecting, setIsAutoSelecting] = useState(false);

     // Get exam statuses from localStorage (in real app, this would come from API)
     const getExamStatuses = useCallback((): ExamStatus[] => {
          const saved = localStorage.getItem('exam_statuses');
          if (saved) {
               try {
                    return JSON.parse(saved);
               } catch {
                    return [];
               }
          }
          return [];
     }, []);

     // Save exam status
     const updateExamStatus = useCallback((examId: number, status: ExamStatus['status']) => {
          const statuses = getExamStatuses();
          const existingIndex = statuses.findIndex(s => s.exam_id === examId);

          const newStatus: ExamStatus = {
               exam_id: examId,
               status,
               last_accessed: new Date().toISOString()
          };

          if (existingIndex >= 0) {
               statuses[existingIndex] = newStatus;
          } else {
               statuses.push(newStatus);
          }

          localStorage.setItem('exam_statuses', JSON.stringify(statuses));
     }, [getExamStatuses]);

     // Find the next exam to work on
     const findNextExam = useCallback((exams: AssignedExam[]): AssignedExam | null => {
          const statuses = getExamStatuses();

          // Priority 1: Find exam in progress
          const inProgressStatus = statuses.find(s => s.status === 'in_progress');
          if (inProgressStatus) {
               const inProgressExam = exams.find(e => e.exam_id === inProgressStatus.exam_id);
               if (inProgressExam) return inProgressExam;
          }

          // Priority 2: Find first exam not started
          const notStartedExam = exams.find(exam => {
               const status = statuses.find(s => s.exam_id === exam.exam_id);
               return !status || status.status === 'not_started';
          });

          if (notStartedExam) return notStartedExam;

          // Priority 3: Return first exam if all are completed (for review)
          return exams[0] || null;
     }, [getExamStatuses]);

     // Auto-select exam logic
     useEffect(() => {
          if (!assignedExams || assignedExams.length === 0) return;

          // If no exam is currently selected
          if (!currentExamId) {
               setIsAutoSelecting(true);

               const nextExam = findNextExam(assignedExams);

               if (nextExam) {
                    localStorage.setItem('exam_id', nextExam.exam_id.toString());
                    localStorage.setItem('exam_duration', nextExam.duration.toString());

                    // Update status to in_progress if not started
                    const statuses = getExamStatuses();
                    const currentStatus = statuses.find(s => s.exam_id === nextExam.exam_id);

                    if (!currentStatus || currentStatus.status === 'not_started') {
                         updateExamStatus(nextExam.exam_id, 'in_progress');
                    }

                    console.log('Auto-selected exam:', nextExam);

                    // Small delay to prevent race condition
                    setTimeout(() => {
                         router.push(`/exam/${nextExam.exam_id}`);
                         setIsAutoSelecting(false);
                    }, 100);
               } else {
                    setIsAutoSelecting(false);
               }
          }
     }, [assignedExams, currentExamId, router, findNextExam, getExamStatuses, updateExamStatus]);

     return {
          isAutoSelecting,
          findNextExam,
          updateExamStatus,
          getExamStatuses
     };
};
