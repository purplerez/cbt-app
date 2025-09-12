'use client';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { StudentAnswer } from '@/types';
import { ChevronLeft, ChevronRight, Flag, Check } from 'lucide-react';

interface ExamNavigationProps {
     totalQuestions: number;
     currentQuestion: number;
     answers: Record<number, StudentAnswer>;
     onQuestionSelect: (questionNumber: number) => void;
     onPrevious: () => void;
     onNext: () => void;
     onSubmit: () => void;
     isLastQuestion: boolean;
     isFirstQuestion: boolean;
}

export default function ExamNavigation({
     totalQuestions,
     currentQuestion,
     answers,
     onQuestionSelect,
     onPrevious,
     onNext,
     onSubmit,
     isLastQuestion,
     isFirstQuestion
}: ExamNavigationProps) {
     const getQuestionStatus = (questionNumber: number, questionId: number) => {
          const answer = answers[questionId];

          if (!answer) return 'unanswered';

          if (answer.is_flagged) return 'flagged';

          // Check if question is answered
          if (Array.isArray(answer.answer)) {
               return answer.answer.length > 0 ? 'answered' : 'unanswered';
          } else {
               return answer.answer.trim() !== '' ? 'answered' : 'unanswered';
          }
     };

     const getAnsweredCount = () => {
          return Object.values(answers).filter(answer => {
               if (Array.isArray(answer.answer)) {
                    return answer.answer.length > 0;
               } else {
                    return answer.answer.trim() !== '';
               }
          }).length;
     };

     const getFlaggedCount = () => {
          return Object.values(answers).filter(answer => answer.is_flagged).length;
     };

     return (
          <div className="space-y-4">
               {/* Progress Summary */}
               <Card className="p-4">
                    <div className="text-center space-y-2">
                         <h3 className="font-semibold text-gray-800">Progress Ujian</h3>
                         <div className="flex justify-center gap-6 text-sm">
                              <div className="flex items-center gap-2">
                                   <div className="w-3 h-3 bg-green-500 rounded-full"></div>
                                   <span>Terjawab: {getAnsweredCount()}</span>
                              </div>
                              <div className="flex items-center gap-2">
                                   <div className="w-3 h-3 bg-orange-500 rounded-full"></div>
                                   <span>Ditandai: {getFlaggedCount()}</span>
                              </div>
                              <div className="flex items-center gap-2">
                                   <div className="w-3 h-3 bg-gray-300 rounded-full"></div>
                                   <span>Belum: {totalQuestions - getAnsweredCount()}</span>
                              </div>
                         </div>
                    </div>
               </Card>

               {/* Question Grid */}
               <Card className="p-4">
                    <h4 className="font-medium text-gray-800 mb-3">Navigasi Soal</h4>
                    <div className="grid grid-cols-5 gap-2 mb-4">
                         {Array.from({ length: totalQuestions }, (_, index) => {
                              const questionNumber = index + 1;
                              const questionId = questionNumber; // Assuming question ID matches number for simplicity
                              const status = getQuestionStatus(questionNumber, questionId);
                              const isCurrentQuestion = questionNumber === currentQuestion;

                              return (
                                   <Button
                                        key={questionNumber}
                                        variant={isCurrentQuestion ? "default" : "outline"}
                                        size="sm"
                                        onClick={() => onQuestionSelect(questionNumber)}
                                        className={`relative h-10 w-full ${isCurrentQuestion
                                                  ? 'bg-blue-600 text-white'
                                                  : status === 'answered'
                                                       ? 'bg-green-50 border-green-200 text-green-700'
                                                       : status === 'flagged'
                                                            ? 'bg-orange-50 border-orange-200 text-orange-700'
                                                            : 'bg-gray-50 border-gray-200 text-gray-600'
                                             }`}
                                   >
                                        <span className="text-xs font-medium">{questionNumber}</span>
                                        {status === 'answered' && !isCurrentQuestion && (
                                             <Check className="absolute top-1 right-1 h-3 w-3 text-green-600" />
                                        )}
                                        {status === 'flagged' && !isCurrentQuestion && (
                                             <Flag className="absolute top-1 right-1 h-3 w-3 text-orange-600" />
                                        )}
                                   </Button>
                              );
                         })}
                    </div>
               </Card>

               {/* Navigation Controls */}
               <Card className="p-4">
                    <div className="flex justify-between items-center">
                         <Button
                              variant="outline"
                              onClick={onPrevious}
                              disabled={isFirstQuestion}
                              className="flex items-center gap-2"
                         >
                              <ChevronLeft className="h-4 w-4" />
                              Sebelumnya
                         </Button>

                         <span className="text-sm text-gray-600">
                              {currentQuestion} / {totalQuestions}
                         </span>

                         {isLastQuestion ? (
                              <Button
                                   onClick={onSubmit}
                                   className="flex items-center gap-2 bg-green-600 hover:bg-green-700"
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

               {/* Quick Actions */}
               <Card className="p-4">
                    <div className="space-y-2">
                         <h4 className="font-medium text-gray-800 text-sm">Aksi Cepat</h4>
                         <div className="flex flex-col gap-2">
                              <Button
                                   variant="outline"
                                   size="sm"
                                   onClick={() => {
                                        const unansweredQuestions = Array.from({ length: totalQuestions }, (_, i) => i + 1)
                                             .filter(num => {
                                                  const answer = answers[num];
                                                  if (!answer) return true;
                                                  if (Array.isArray(answer.answer)) {
                                                       return answer.answer.length === 0;
                                                  }
                                                  return answer.answer.trim() === '';
                                             });

                                        if (unansweredQuestions.length > 0) {
                                             onQuestionSelect(unansweredQuestions[0]);
                                        }
                                   }}
                                   className="text-xs"
                              >
                                   Soal Belum Dijawab
                              </Button>

                              <Button
                                   variant="outline"
                                   size="sm"
                                   onClick={() => {
                                        const flaggedQuestions = Array.from({ length: totalQuestions }, (_, i) => i + 1)
                                             .filter(num => answers[num]?.is_flagged);

                                        if (flaggedQuestions.length > 0) {
                                             onQuestionSelect(flaggedQuestions[0]);
                                        }
                                   }}
                                   className="text-xs"
                              >
                                   Soal Ditandai
                              </Button>
                         </div>
                    </div>
               </Card>
          </div>
     );
}
