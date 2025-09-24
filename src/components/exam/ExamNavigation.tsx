'use client';

import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { StudentAnswer, ParsedQuestion } from '@/types';
import { Flag, Check } from 'lucide-react';
import React, { useEffect } from 'react';
import { useNavigationDebug } from '@/lib/navigationDebugUtils';

interface ExamNavigationProps {
     totalQuestions: number;
     currentQuestion: number;
     answers: Record<number, StudentAnswer>;
     questions: ParsedQuestion[]; // Tambahkan questions untuk mendapatkan real questionId
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
     questions,
     onQuestionSelect,
}: ExamNavigationProps) {
     const debugUtils = useNavigationDebug();

     // Debug logging ketika answers atau questions berubah
     useEffect(() => {
          debugUtils.logQuestionMapping(questions, answers);
          debugUtils.logAllQuestionStatus(questions, answers);
     }, [answers, questions, debugUtils]);
     const getQuestionStatus = (questionNumber: number) => {
          // Dapatkan questionId yang sebenarnya dari questions array
          const questionIndex = questionNumber - 1;
          const question = questions[questionIndex];
          if (!question) return 'unanswered';

          const answer = answers[question.id]; // Gunakan question.id, bukan questionNumber

          if (!answer) return 'unanswered';

          if (answer.is_flagged) return 'flagged';

          if (Array.isArray(answer.answer)) {
               return answer.answer.length > 0 ? 'answered' : 'unanswered';
          } else {
               return answer.answer && answer.answer.toString().trim() !== '' ? 'answered' : 'unanswered';
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
          <div>
               <div className="space-y-4">
                    <Card className="p-3">
                         <div className="text-center space-y-2">
                              <h3 className="font-semibold text-gray-800">Progress Ujian</h3>
                              <div className="flex justify-between text-sm w-full gap-2">
                                   <div className="flex items-center gap-1">
                                        <div className="w-3 h-3 bg-primary rounded-full"></div>
                                        <span>Terjawab: {getAnsweredCount()}</span>
                                   </div>
                                   <div className="flex items-center gap-1">
                                        <div className="w-3 h-3 bg-orange-500 rounded-full"></div>
                                        <span>Ditandai: {getFlaggedCount()}</span>
                                   </div>
                                   <div className="flex items-center gap-1">
                                        <div className="w-3 h-3 bg-gray-300 rounded-full"></div>
                                        <span>Belum: {totalQuestions - getAnsweredCount()}</span>
                                   </div>
                              </div>
                         </div>
                    </Card>

                    <Card className="p-4">
                         <h4 className="font-medium text-gray-800 mb-3">Navigasi Soal</h4>
                         <div className="grid grid-cols-5 gap-2 mb-4">
                              {Array.from({ length: totalQuestions }, (_, index) => {
                                   const questionNumber = index + 1;
                                   const status = getQuestionStatus(questionNumber); // Hapus questionId parameter
                                   const isCurrentQuestion = questionNumber === currentQuestion;

                                   return (
                                        <Button
                                             key={questionNumber}
                                             variant={isCurrentQuestion ? "default" : "outline"}
                                             size="sm"
                                             onClick={() => onQuestionSelect(questionNumber)}
                                             className={`relative h-10 w-full ${isCurrentQuestion
                                                  ? 'bg-primary   text-white'
                                                  : status === 'answered'
                                                       ? 'bg-primary/10 border-primary/20 text-primary'
                                                       : status === 'flagged'
                                                            ? 'bg-orange-50 border-orange-200 text-orange-700'
                                                            : 'bg-gray-50 border-gray-200 text-gray-600'
                                                  }`}
                                        >
                                             <span className="text-xs font-medium">{questionNumber}</span>
                                             {status === 'answered' && !isCurrentQuestion && (
                                                  <Check className="absolute top-1 right-1 h-3 w-3 text-primary" />
                                             )}
                                             {status === 'flagged' && !isCurrentQuestion && (
                                                  <Flag className="absolute top-1 right-1 h-3 w-3 text-orange-600" />
                                             )}
                                        </Button>
                                   );
                              })}
                         </div>
                    </Card>

                    <Card className="p-4">
                         <div className="space-y-2">
                              <h4 className="font-medium text-gray-800 text-sm">Aksi Cepat</h4>
                              <div className="flex flex-col gap-2">
                                   <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => {
                                             const unansweredQuestions: number[] = [];

                                             for (let i = 0; i < totalQuestions; i++) {
                                                  const question = questions[i];
                                                  if (!question) continue;

                                                  const answer = answers[question.id];
                                                  let isUnanswered = true;

                                                  if (answer) {
                                                       if (Array.isArray(answer.answer)) {
                                                            isUnanswered = answer.answer.length === 0;
                                                       } else {
                                                            isUnanswered = !answer.answer || answer.answer.toString().trim() === '';
                                                       }
                                                  }

                                                  if (isUnanswered) {
                                                       unansweredQuestions.push(i + 1); // questionNumber
                                                  }
                                             }

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
                                             const flaggedQuestions = Array.from({ length: totalQuestions }, (_, i) => {
                                                  const question = questions[i];
                                                  return question && answers[question.id]?.is_flagged ? i + 1 : null;
                                             }).filter(Boolean) as number[];

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

                    {/* Navigation Controls */}

               </div>

          </div>
     );
}
