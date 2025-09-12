import { Student, AssignedExam } from '@/types';
import React from 'react'
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useRouter } from 'next/navigation';
import { BookOpen, Clock, FileText, Play, Pause, CheckCircle } from 'lucide-react';

interface ExamContentProps {
     userData?: {
          student: Student;
          assigned: AssignedExam[];
     },
}

interface ExamStatus {
     exam_id: number;
     status: 'not_started' | 'in_progress' | 'completed';
     last_accessed?: string;
}

const ExamContent: React.FC<ExamContentProps> = ({ userData }) => {
     const router = useRouter();
     const examId = localStorage.getItem('exam_id')

     const currentExam = React.useMemo(() => {
          if (!examId || !userData?.assigned) return null;
          return userData.assigned.find(exam => exam.exam_id.toString() === examId);
     }, [examId, userData?.assigned]);

     const otherExams = React.useMemo(() => {
          if (!userData?.assigned || !examId) return [];
          return userData.assigned.filter(exam => exam.exam_id.toString() !== examId);
     }, [userData?.assigned, examId]);

     // Get exam statuses
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

     const handleSelectExam = (exam: AssignedExam) => {
          localStorage.setItem('exam_id', exam.exam_id.toString());
          localStorage.setItem('exam_duration', exam.duration.toString());
          router.push(`/exam/${exam.exam_id}`);
     };

     React.useEffect(() => {
          console.log('User Data in Content:', userData)
          console.log('Exam ID from localStorage in Content:', examId)
          console.log('Current Exam Data:', currentExam)
     }, [examId, userData, currentExam])

     // If no exam is selected but there are assigned exams, this should not happen due to auto-select
     if (!examId && userData?.assigned && userData.assigned.length > 0) {
          return (
               <div className="flex flex-col items-center justify-center gap-8 flex-1">
                    <div className="text-center">
                         <h2 className="text-2xl font-bold mb-4">Memuat Ujian...</h2>
                         <p className="text-gray-600">Sedang memilih ujian otomatis...</p>
                    </div>
               </div>
          );
     }

     return (
          <div className="flex flex-col items-center justify-center gap-8 flex-1">
               {examId && currentExam ? (
                    <>
                         {/* Current Exam Info */}
                         <div className="text-center">
                              <div className="flex items-center justify-center gap-2 mb-2">
                                   {getStatusIcon(getExamStatus(currentExam.exam_id))}
                                   <span className={`text-sm px-3 py-1 rounded-full border ${getStatusColor(getExamStatus(currentExam.exam_id))}`}>
                                        {getStatusText(getExamStatus(currentExam.exam_id))}
                                   </span>
                              </div>

                              <h2 className="text-3xl font-bold mb-4">
                                   {currentExam.title}
                              </h2>
                              <div className="flex justify-center gap-8 mb-8">
                                   <div className="text-center">
                                        <p className="text-2xl font-bold text-blue-600">
                                             {currentExam.duration}
                                        </p>
                                        <p className="text-sm text-gray-600">Menit</p>
                                   </div>
                                   <div className="text-center">
                                        <p className="text-2xl font-bold text-green-600">
                                             {currentExam.total_quest}
                                        </p>
                                        <p className="text-sm text-gray-600">Soal</p>
                                   </div>
                              </div>
                         </div>

                         <div className="text-center max-w-md">
                              <p className="text-gray-600 mb-6">
                                   {getExamStatus(currentExam.exam_id) === 'completed'
                                        ? 'Ujian ini telah selesai dikerjakan. Anda dapat meninjau ulang atau melanjutkan ke ujian berikutnya.'
                                        : 'Pastikan koneksi internet Anda stabil sebelum memulai ujian. Setelah ujian dimulai, timer akan berjalan otomatis.'
                                   }
                              </p>

                              {/* Navigation to Exam List */}
                              <Button
                                   variant="outline"
                                   onClick={() => router.push('/exam')}
                                   className="mb-4"
                              >
                                   Lihat Semua Ujian
                              </Button>
                         </div>

                         {/* Other Available Exams */}
                         {otherExams.length > 0 && (
                              <div className="w-full max-w-4xl">
                                   <h3 className="text-lg font-semibold text-gray-800 mb-4 text-center">
                                        Ujian Lainnya
                                   </h3>
                                   <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {otherExams.map((exam) => {
                                             const status = getExamStatus(exam.exam_id);
                                             return (
                                                  <Card key={exam.exam_id} className="p-4 hover:shadow-md transition-shadow">
                                                       <div className="flex items-start gap-3">
                                                            <BookOpen className="h-5 w-5 text-blue-600 mt-1" />
                                                            <div className="flex-1">
                                                                 <div className="flex items-center gap-2 mb-2">
                                                                      {getStatusIcon(status)}
                                                                      <span className={`text-xs px-2 py-1 rounded-full border ${getStatusColor(status)}`}>
                                                                           {getStatusText(status)}
                                                                      </span>
                                                                 </div>

                                                                 <h4 className="font-medium text-gray-800 mb-2">
                                                                      {exam.title}
                                                                 </h4>
                                                                 <div className="flex gap-4 text-sm text-gray-600 mb-3">
                                                                      <div className="flex items-center gap-1">
                                                                           <Clock className="h-4 w-4" />
                                                                           {exam.duration} menit
                                                                      </div>
                                                                      <div className="flex items-center gap-1">
                                                                           <FileText className="h-4 w-4" />
                                                                           {exam.total_quest} soal
                                                                      </div>
                                                                 </div>
                                                                 <Button
                                                                      size="sm"
                                                                      variant={status === 'completed' ? 'outline' : 'default'}
                                                                      onClick={() => handleSelectExam(exam)}
                                                                      className="w-full"
                                                                 >
                                                                      {status === 'completed' ? 'Tinjau Ulang' :
                                                                           status === 'in_progress' ? 'Lanjutkan' : 'Mulai Ujian'}
                                                                 </Button>
                                                            </div>
                                                       </div>
                                                  </Card>
                                             );
                                        })}
                                   </div>
                              </div>
                         )}
                    </>
               ) : (
                    <div className="text-center">
                         <h2 className="text-2xl font-bold mb-4">
                              {examId ? 'Ujian Tidak Ditemukan' : 'Selamat Datang!'}
                         </h2>
                         <p className="text-gray-600">
                              {examId ?
                                   'Ujian yang Anda pilih tidak ditemukan atau sudah tidak tersedia.' :
                                   'Saat ini tidak ada ujian yang ditugaskan untuk Anda. Silakan kunjungi halaman ujian untuk melihat daftar ujian yang tersedia.'
                              }
                         </p>
                         {!examId && (
                              <Button
                                   onClick={() => router.push('/exam')}
                                   className="mt-4"
                              >
                                   Lihat Daftar Ujian
                              </Button>
                         )}
                    </div>
               )}
          </div>
     )
}

export default ExamContent