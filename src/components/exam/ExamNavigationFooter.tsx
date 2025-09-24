'use client';

import React from 'react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { ChevronLeft, ChevronRight } from 'lucide-react';

interface ExamNavigationFooterProps {
     onPrevious: () => void;
     onNext: () => void;
     onSubmit: () => void;
     isFirstQuestion: boolean;
     isLastQuestion: boolean;
}

export const ExamNavigationFooter: React.FC<ExamNavigationFooterProps> = ({
     onPrevious,
     onNext,
     onSubmit,
     isFirstQuestion,
     isLastQuestion,
}) => {
     return (
          <div className='sticky bottom-0 left-0'>
               <Card>
                    <div className="flex justify-center items-center p-5 gap-20">
                         <Button
                              variant="outline"
                              onClick={onPrevious}
                              disabled={isFirstQuestion}
                              className="flex items-center gap-2 hover:bg-gray-100"
                         >
                              <ChevronLeft className="h-4 w-4" />
                              Sebelumnya
                         </Button>

                         {isLastQuestion ? (
                              <Button
                                   onClick={onSubmit}
                                   className="flex items-center gap-2 bg-red-500 hover:bg-red-600"
                              >
                                   Selesai Ujian
                              </Button>
                         ) : (
                              <Button
                                   onClick={onNext}
                                   className="flex items-center gap-2"
                              >
                                   Selanjutnya
                                   <ChevronRight className="h-4 w-4" />
                              </Button>
                         )}
                    </div>
               </Card>
          </div>
     );
};
