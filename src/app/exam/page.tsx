'use client'

import ProtectedRoute from '@/components/ProtectedRoute'
import { Card } from '@/components/ui/card'
import { authService } from '@/services/auth'
import { useQuery } from '@tanstack/react-query'
import { useRouter } from 'next/navigation'
import { createExamSlug } from '@/lib/examUtils'
import React, { useEffect } from 'react'

const ExamPage = () => {
     const router = useRouter()

     const { data: userData, isError, isLoading } = useQuery({
          queryKey: ['currentUser'],
          queryFn: authService.getCurrentUser
     })

     useEffect(() => {
          if (userData?.assigned && userData.assigned.length > 0) {
               const firstExam = userData.assigned[0];
               const examSlug = createExamSlug(firstExam.title);

               localStorage.setItem('exam_id', firstExam.exam_id.toString());
               localStorage.setItem('exam_duration', firstExam.duration.toString());
               localStorage.setItem('current_exam_slug', examSlug);

               router.push(`/exam/${examSlug}`);
          } else if (!isLoading && userData && (!userData.assigned || userData.assigned.length === 0)) {
               router.push('/dashboard');
          }
     }, [userData, isLoading, router]);

     if (isLoading) {
          return (
               <ProtectedRoute>
                    <div className="min-h-screen flex items-center justify-center bg-gray-50">
                         <Card className="p-8 text-center">
                              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                              <h2 className="text-xl font-semibold text-gray-800 mb-2">
                                   Memuat Data Ujian
                              </h2>
                              <p className="text-gray-600">
                                   Sedang mengambil informasi ujian yang tersedia...
                              </p>
                         </Card>
                    </div>
               </ProtectedRoute>
          );
     }

     if (isError) {
          return (
               <ProtectedRoute>
                    <div className="min-h-screen flex items-center justify-center bg-gray-50">
                         <Card className="p-8 text-center max-w-md">
                              <h2 className="text-xl font-semibold text-red-600 mb-2">Error</h2>
                              <p className="text-gray-600 mb-4">Gagal memuat data ujian. Silakan refresh halaman.</p>
                              <button
                                   onClick={() => window.location.reload()}
                                   className="px-4 py-2 bg-primary text-white rounded hover:bg-primary/90"
                              >
                                   Refresh Halaman
                              </button>
                         </Card>
                    </div>
               </ProtectedRoute>
          );
     }

     return (
          <ProtectedRoute>
               <div className="min-h-screen flex items-center justify-center bg-gray-50">
                    <Card className="p-8 text-center">
                         <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto mb-4"></div>
                         <h2 className="text-xl font-semibold text-gray-800 mb-2">
                              Menyiapkan Ujian
                         </h2>
                         <p className="text-gray-600">
                              Sedang mengarahkan ke ujian yang tersedia...
                         </p>
                    </Card>
               </div>
          </ProtectedRoute>
     )
}

export default ExamPage