'use client';

import React from 'react';
import ProtectedRoute from '@/components/ProtectedRoute';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-react';

interface ErrorExamScreenProps {
     title: string;
     message: string;
     onRetry?: () => void;
     retryButtonText?: string;
}

export const ErrorExamScreen: React.FC<ErrorExamScreenProps> = ({
     title,
     message,
     onRetry,
     retryButtonText = "Coba Lagi"
}) => {
     return (
          <ProtectedRoute>
               <div className="min-h-screen flex items-center justify-center bg-gray-50">
                    <Card className="p-8 text-center max-w-md">
                         <AlertTriangle className="h-12 w-12 text-red-500 mx-auto mb-4" />
                         <h2 className="text-xl font-semibold text-gray-800 mb-2">
                              {title}
                         </h2>
                         <p className="text-gray-600 mb-4">
                              {message}
                         </p>
                         {onRetry && (
                              <Button onClick={onRetry}>
                                   {retryButtonText}
                              </Button>
                         )}
                    </Card>
               </div>
          </ProtectedRoute>
     );
};
