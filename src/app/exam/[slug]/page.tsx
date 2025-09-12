'use client'

import Content from '@/components/exam/ExamContent'
import Header from '@/components/exam/ExamHeader'
import ProtectedRoute from '@/components/ProtectedRoute'
import { Button } from '@/components/ui/button'
import { authService } from '@/services/auth'
import { examService } from '@/services/exam'
import { useMutation, useQuery } from '@tanstack/react-query'
import { useRouter, useParams } from 'next/navigation'
import React from 'react'

export default function ExamDetailPage() {
     const router = useRouter();
     const params = useParams();
     const examId = localStorage.getItem('exam_id')

     const { data: userData } = useQuery({
          queryKey: ['currentUser'],
          queryFn: () => authService.getCurrentUser(),
     })

     const examMutation = useMutation({
          mutationFn: () => examService.examStart(Number(examId) || 0),
          onSuccess: (data) => {
               console.log('Exam started:', data);
               // Redirect to start page after successful exam start
               router.push(`/exam/${params.slug}/start`);
          },
          onError: (error) => {
               console.error('Failed to start exam:', error);
               alert('Gagal memulai ujian. Silakan coba lagi.');
          }
     })

     const handleStartExam = () => {
          if (!examId) {
               alert('ID ujian tidak ditemukan. Silakan kembali ke halaman ujian.');
               return;
          }

          // Confirm start exam
          const confirmed = confirm(
               'Setelah ujian dimulai, timer akan berjalan otomatis. Pastikan koneksi internet Anda stabil. Mulai ujian sekarang?'
          );

          if (confirmed) {
               examMutation.mutate();
          }
     }

     React.useEffect(() => {
          console.log('User Data:', userData)
          console.log('Exam ID from localStorage:', examId)
          console.log('Exam Slug:', params.slug)
     }, [userData, examId, params.slug])

     if (!examId) {
          return (
               <ProtectedRoute>
                    <div className="min-h-screen flex items-center justify-center bg-gray-50">
                         <div className="text-center">
                              <h2 className="text-2xl font-bold text-gray-800 mb-4">Ujian Tidak Ditemukan</h2>
                              <p className="text-gray-600 mb-6">
                                   ID ujian tidak ditemukan. Silakan kembali ke halaman ujian dan pilih ujian yang tersedia.
                              </p>
                              <Button onClick={() => router.push('/exam')}>
                                   Kembali ke Daftar Ujian
                              </Button>
                         </div>
                    </div>
               </ProtectedRoute>
          );
     }

     return (
          <ProtectedRoute>
               <div className="flex flex-col justify-between gap-10 p-4 sm:p-8 md:p-10 lg:p-12 min-h-screen bg-white">
                    {/* Navbar */}
                    <Header userData={userData} />

                    {/* Content */}
                    <Content userData={userData} />

                    {/* Start Exam Button */}
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