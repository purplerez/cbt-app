'use client';

import React from 'react';
import ProtectedRoute from '@/components/ProtectedRoute';
import { Card } from '@/components/ui/card';

interface LoadingExamScreenProps {
     message?: string;
}

export const LoadingExamScreen: React.FC<LoadingExamScreenProps> = ({
     message = "Memuat soal ujian..."
}) => {
     return (
          <ProtectedRoute>
               <div className="min-h-screen flex items-center justify-center bg-gray-50">
                    <Card className="p-8 text-center">
                         <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                         <p className="text-gray-600">{message}</p>
                    </Card>
               </div>
          </ProtectedRoute>
     );
};
