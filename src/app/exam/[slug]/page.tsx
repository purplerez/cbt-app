'use client'

import Content from '@/components/exam/ExamContent'
import ProtectedRoute from '@/components/ProtectedRoute'
import { Button } from '@/components/ui/button'
import { authService } from '@/services/auth'
import { examService } from '@/services/exam'
import { findExamBySlug } from '@/lib/examUtils'
import { useMutation, useQuery } from '@tanstack/react-query'
import { useRouter, useParams } from 'next/navigation'
import { useAppDispatch } from '@/store/hooks'
import { resetExamState } from '@/store/examSlice'
import React from 'react'

export default function ExamDetailPage() {
     const router = useRouter();
     const params = useParams();
     const slug = params.slug as string;
     const dispatch = useAppDispatch();

     const { data: userData } = useQuery({
          queryKey: ['currentUser'],
          queryFn: () => authService.getCurrentUser(),
     })

     // Reset exam state when entering exam detail page
     React.useEffect(() => {
          console.log('Entering exam detail page for slug:', slug, '- Resetting state');
          dispatch(resetExamState());

          // Also clear any exam-related localStorage from previous exam
          localStorage.removeItem('session_token');
          localStorage.removeItem('exam_result');
     }, [dispatch, slug]);

     const currentExam = React.useMemo(() => {
          if (!userData?.assigned || !slug) return null;
          return findExamBySlug(userData.assigned, slug);
     }, [userData?.assigned, slug]);

     const examMutation = useMutation({
          mutationFn: () => {
               if (!currentExam) {
                    throw new Error('Exam not found');
               }
               localStorage.setItem('exam_id', currentExam.exam_id.toString());
               localStorage.setItem('exam_duration', currentExam.duration.toString());
               localStorage.setItem('current_exam_slug', slug);

               return examService.examStart(currentExam.exam_id);
          },
          onSuccess: (data) => {
               console.log('Exam started:', data);
               router.push(`/exam/${slug}/start`);
          },
          onError: (error) => {
               console.error('Failed to start exam:', error);
               alert('Gagal memulai ujian. Silakan coba lagi.');
          }
     })

     const handleStartExam = () => {
          if (!currentExam) {
               alert('Ujian tidak ditemukan. Silakan kembali ke halaman ujian.');
               return;
          }

          const confirmed = confirm(
               'Setelah ujian dimulai, timer akan berjalan otomatis. Pastikan koneksi internet Anda stabil. Mulai ujian sekarang?'
          );

          if (confirmed) {
               examMutation.mutate();
          }
     }

     React.useEffect(() => {
          console.log('User Data:', userData)
          console.log('Slug:', slug)
          console.log('Current Exam:', currentExam)
     }, [userData, slug, currentExam])

     if (userData?.assigned && !currentExam && slug) {
          return (
               <ProtectedRoute>
                    <div className="min-h-screen flex items-center justify-center bg-gray-50">
                         <div className="text-center">
                              <h2 className="text-2xl font-bold text-gray-800 mb-4">Ujian Tidak Ditemukan</h2>
                              <p className="text-gray-600 mb-6">
                                   Ujian dengan slug &quot;{slug}&quot; tidak ditemukan. Silakan kembali ke halaman ujian dan pilih ujian yang tersedia.
                              </p>
                              <Button onClick={() => router.push('/exam')}>
                                   Kembali ke Daftar Ujian
                              </Button>
                         </div>
                    </div>
               </ProtectedRoute>
          );
     }

     if (!userData || !currentExam) {
          return (
               <ProtectedRoute>
                    <div className="min-h-screen flex items-center justify-center bg-gray-50">
                         <div className="text-center">
                              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                              <p className="text-gray-600">Memuat data ujian...</p>
                         </div>
                    </div>
               </ProtectedRoute>
          );
     }

     return (
          <ProtectedRoute>
               <div className="flex flex-col justify-between gap-10 p-4 sm:p-8 md:p-10 lg:p-12 min-h-screen bg-white">
                    <Content userData={userData} />

                    <div className="flex justify-center">
                         <Button
                              className="px-8 py-3 text-lg bg-blue-600 hover:bg-blue-700"
                              onClick={handleStartExam}
                              disabled={examMutation.isPending}
                         >
                              {examMutation.isPending ? 'Memulai Ujian...' : 'Mulai Ujian'}
                         </Button>
                    </div>
               </div>
          </ProtectedRoute>
     )
}