'use client'

import ProtectedRoute from '@/components/ProtectedRoute'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { authService } from '@/services/auth'
import { useQuery } from '@tanstack/react-query'
import { useRouter } from 'next/navigation'
import { useAutoExamSelection } from '@/hooks/useAutoExamSelection'
import { BookOpen, Clock, FileText, Play, Pause, CheckCircle } from 'lucide-react'
import React from 'react'

interface ExamStatus {
     exam_id: number;
     status: 'not_started' | 'in_progress' | 'completed';
     last_accessed?: string;
}

const ExamPage = () => {
     const router = useRouter()
     const examId = localStorage.getItem('exam_id')

     const { data: userData, isError, isLoading } = useQuery({
          queryKey: ['currentUser'],
          queryFn: authService.getCurrentUser
     })

     const { isAutoSelecting } = useAutoExamSelection({
          assignedExams: userData?.assigned,
          currentExamId: examId || undefined
     })

     const getExamStatuses = (): ExamStatus[] => {
          const saved = localStorage.getItem('exam_statuses');
          if (saved) {
               try {
                    return JSON.parse(saved);
               } catch {
                    return [];
               }
          }
          return [];
     };

     const getExamStatus = (examId: number): ExamStatus['status'] => {
          const statuses = getExamStatuses();
          const status = statuses.find(s => s.exam_id === examId);
          return status?.status || 'not_started';
     };

     const getStatusIcon = (status: ExamStatus['status']) => {
          switch (status) {
               case 'not_started':
                    return <Play className="h-4 w-4 text-blue-600" />;
               case 'in_progress':
                    return <Pause className="h-4 w-4 text-orange-600" />;
               case 'completed':
                    return <CheckCircle className="h-4 w-4 text-green-600" />;
               default:
                    return <Play className="h-4 w-4 text-gray-400" />;
          }
     };

     const getStatusText = (status: ExamStatus['status']) => {
          switch (status) {
               case 'not_started':
                    return 'Belum Dimulai';
               case 'in_progress':
                    return 'Sedang Berlangsung';
               case 'completed':
                    return 'Selesai';
               default:
                    return 'Belum Dimulai';
          }
     };

     const getStatusColor = (status: ExamStatus['status']) => {
          switch (status) {
               case 'not_started':
                    return 'text-blue-600 bg-blue-50 border-blue-200';
               case 'in_progress':
                    return 'text-orange-600 bg-orange-50 border-orange-200';
               case 'completed':
                    return 'text-green-600 bg-green-50 border-green-200';
               default:
                    return 'text-gray-600 bg-gray-50 border-gray-200';
          }
     };

     const onClickExam = (examId: string) => {
          localStorage.setItem('exam_id', examId)
          localStorage.setItem('exam_duration', userData?.assigned.find(exam => exam.exam_id.toString() === examId)?.duration.toString() || '120')
          router.push(`/exam/${userData?.assigned.find(exam => exam.exam_id.toString() === examId)?.exam_id || examId}`)
     }

     if (isAutoSelecting) {
          return (
               <ProtectedRoute>
                    <div className="min-h-screen flex items-center justify-center bg-gray-50">
                         <Card className="p-8 text-center">
                              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                              <h2 className="text-xl font-semibold text-gray-800 mb-2">
                                   Memilih Ujian Otomatis
                              </h2>
                              <p className="text-gray-600">
                                   Sedang menentukan ujian yang akan dikerjakan...
                              </p>
                         </Card>
                    </div>
               </ProtectedRoute>
          );
     }

     return (
          <ProtectedRoute>
               <div className="min-h-screen bg-gray-50 p-4 sm:p-6 lg:p-8">
                    <div className="max-w-6xl mx-auto">
                         {/* Header */}
                         <div className="text-center mb-8">
                              <h1 className="text-3xl font-bold text-gray-800 mb-2">Daftar Ujian</h1>
                              <p className="text-gray-600">Pilih ujian yang ingin Anda kerjakan</p>
                         </div>

                         {isLoading && (
                              <div className="flex justify-center items-center py-12">
                                   <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                                   <span className="ml-3 text-gray-600">Memuat daftar ujian...</span>
                              </div>
                         )}

                         {isError && (
                              <Card className="p-8 text-center">
                                   <h2 className="text-xl font-semibold text-red-600 mb-2">Error</h2>
                                   <p className="text-gray-600">Gagal memuat data ujian. Silakan refresh halaman.</p>
                              </Card>
                         )}

                         {userData && userData.assigned && userData.assigned.length > 0 && (
                              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                   {userData.assigned.map((exam, index) => {
                                        const status = getExamStatus(exam.exam_id);
                                        const isCurrentExam = examId === exam.exam_id.toString();

                                        return (
                                             <Card
                                                  key={index}
                                                  className={`p-6 hover:shadow-lg transition-all cursor-pointer ${isCurrentExam ? 'ring-2 ring-blue-500 bg-blue-50' : 'hover:bg-gray-50'
                                                       }`}
                                                  onClick={() => onClickExam(exam.exam_id.toString())}
                                             >
                                                  {/* Status Badge */}
                                                  <div className="flex items-center justify-between mb-4">
                                                       <div className="flex items-center gap-2">
                                                            {getStatusIcon(status)}
                                                            <span className={`text-xs px-3 py-1 rounded-full border ${getStatusColor(status)}`}>
                                                                 {getStatusText(status)}
                                                            </span>
                                                       </div>
                                                       {isCurrentExam && (
                                                            <span className="text-xs bg-blue-600 text-white px-2 py-1 rounded-full">
                                                                 Terpilih
                                                            </span>
                                                       )}
                                                  </div>

                                                  {/* Exam Icon */}
                                                  <div className="flex items-center gap-3 mb-4">
                                                       <BookOpen className="h-8 w-8 text-blue-600" />
                                                       <div className="flex-1">
                                                            <h3 className="text-lg font-semibold text-gray-800 mb-1">
                                                                 {exam.title}
                                                            </h3>
                                                       </div>
                                                  </div>

                                                  {/* Exam Details */}
                                                  <div className="grid grid-cols-2 gap-4 mb-4">
                                                       <div className="flex items-center gap-2 text-sm text-gray-600">
                                                            <Clock className="h-4 w-4" />
                                                            <span>{exam.duration} menit</span>
                                                       </div>
                                                       <div className="flex items-center gap-2 text-sm text-gray-600">
                                                            <FileText className="h-4 w-4" />
                                                            <span>{exam.total_quest} soal</span>
                                                       </div>
                                                  </div>

                                                  {/* Action Button */}
                                                  <Button
                                                       className="w-full"
                                                       variant={isCurrentExam ? "default" : "outline"}
                                                       onClick={(e) => {
                                                            e.stopPropagation();
                                                            onClickExam(exam.exam_id.toString());
                                                       }}
                                                  >
                                                       {status === 'completed' ? 'Tinjau Ulang' :
                                                            status === 'in_progress' ? 'Lanjutkan' :
                                                                 isCurrentExam ? 'Mulai Ujian' : 'Pilih Ujian'}
                                                  </Button>
                                             </Card>
                                        );
                                   })}
                              </div>
                         )}

                         {userData && userData.assigned && userData.assigned.length === 0 && (
                              <Card className="p-8 text-center">
                                   <BookOpen className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                   <h2 className="text-xl font-semibold text-gray-800 mb-2">Tidak Ada Ujian</h2>
                                   <p className="text-gray-600">Saat ini tidak ada ujian yang ditugaskan untuk Anda.</p>
                              </Card>
                         )}
                    </div>
               </div>
          </ProtectedRoute>
     )
}

export default ExamPage