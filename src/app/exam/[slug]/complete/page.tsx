'use client';

import { useEffect } from 'react';
import { useRouter } from 'next/navigation';
import ProtectedRoute from '@/components/ProtectedRoute';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { CheckCircle, Clock, FileText, Home } from 'lucide-react';

export default function ExamCompletePage() {
     const router = useRouter();

     useEffect(() => {
          localStorage.removeItem('exam_id');
          localStorage.removeItem('exam_duration');
     }, []);

     const handleBackToExamList = () => {
          router.push('/exam');
     };

     return (
          <ProtectedRoute>
               <div className="min-h-screen bg-gray-50 flex items-center justify-center px-4">
                    <Card className="max-w-2xl w-full p-8 text-center">
                         {/* Success Icon */}
                         <div className="flex justify-center mb-6">
                              <div className="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                                   <CheckCircle className="w-12 h-12 text-green-600" />
                              </div>
                         </div>

                         {/* Title */}
                         <h1 className="text-3xl font-bold text-gray-800 mb-4">
                              Ujian Selesai!
                         </h1>

                         {/* Message */}
                         <p className="text-lg text-gray-600 mb-8">
                              Selamat! Anda telah berhasil menyelesaikan ujian.
                              Jawaban Anda telah tersimpan dengan aman.
                         </p>

                         {/* Status Cards */}
                         <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                              <Card className="p-4">
                                   <div className="flex items-center justify-center mb-2">
                                        <CheckCircle className="w-8 h-8 text-green-600" />
                                   </div>
                                   <h3 className="font-semibold text-gray-800">Status</h3>
                                   <p className="text-sm text-gray-600">Berhasil Dikirim</p>
                              </Card>

                              <Card className="p-4">
                                   <div className="flex items-center justify-center mb-2">
                                        <Clock className="w-8 h-8 text-blue-600" />
                                   </div>
                                   <h3 className="font-semibold text-gray-800">Waktu</h3>
                                   <p className="text-sm text-gray-600">
                                        {new Date().toLocaleString('id-ID')}
                                   </p>
                              </Card>

                              <Card className="p-4">
                                   <div className="flex items-center justify-center mb-2">
                                        <FileText className="w-8 h-8 text-purple-600" />
                                   </div>
                                   <h3 className="font-semibold text-gray-800">Hasil</h3>
                                   <p className="text-sm text-gray-600">Segera Diumumkan</p>
                              </Card>
                         </div>

                         {/* Information */}
                         <div className="bg-blue-50 rounded-lg p-6 mb-8 text-left">
                              <h3 className="font-semibold text-blue-800 mb-3">Informasi Penting:</h3>
                              <ul className="space-y-2 text-sm text-blue-700">
                                   <li>• Hasil ujian akan diumumkan sesuai dengan jadwal yang telah ditentukan</li>
                                   <li>• Anda dapat melihat hasil ujian melalui halaman ujian setelah diumumkan</li>
                                   <li>• Jika ada pertanyaan, silakan hubungi pengawas ujian</li>
                                   <li>• Terima kasih telah mengikuti ujian dengan tertib</li>
                              </ul>
                         </div>

                         {/* Action Buttons */}
                         <div className="flex flex-col sm:flex-row gap-4 justify-center">
                              <Button
                                   onClick={handleBackToExamList}
                                   className="flex items-center gap-2"
                              >
                                   <Home className="w-4 h-4" />
                                   Kembali ke Daftar Ujian
                              </Button>
                         </div>
                    </Card>
               </div>
          </ProtectedRoute>
     );
}
