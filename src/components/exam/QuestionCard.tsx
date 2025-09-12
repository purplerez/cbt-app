'use client';

import { ParsedQuestion, StudentAnswer } from '@/types';
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Flag, FlagOff } from 'lucide-react';
import { useState, useEffect } from 'react';

interface QuestionCardProps {
     question: ParsedQuestion;
     questionNumber: number;
     totalQuestions: number;
     currentAnswer?: StudentAnswer;
     onAnswerChange: (questionId: number, answer: string | string[]) => void;
     onFlagToggle: (questionId: number, isFlagged: boolean) => void;
}

export default function QuestionCard({
     question,
     questionNumber,
     totalQuestions,
     currentAnswer,
     onAnswerChange,
     onFlagToggle
}: QuestionCardProps) {
     const [selectedAnswers, setSelectedAnswers] = useState<string[]>([]);
     const [essayAnswer, setEssayAnswer] = useState<string>('');
     const [isFlagged, setIsFlagged] = useState<boolean>(false);

     // Initialize state from current answer
     useEffect(() => {
          if (currentAnswer) {
               if (Array.isArray(currentAnswer.answer)) {
                    setSelectedAnswers(currentAnswer.answer);
               } else {
                    if (question.question_type_id === '2') { // Essay
                         setEssayAnswer(currentAnswer.answer);
                    } else {
                         setSelectedAnswers([currentAnswer.answer]);
                    }
               }
               setIsFlagged(currentAnswer.is_flagged || false);
          }
     }, [currentAnswer, question.question_type_id]);

     const handleSingleChoice = (optionKey: string) => {
          setSelectedAnswers([optionKey]);
          onAnswerChange(question.id, optionKey);
     };

     const handleMultipleChoice = (optionKey: string) => {
          const newAnswers = selectedAnswers.includes(optionKey)
               ? selectedAnswers.filter(ans => ans !== optionKey)
               : [...selectedAnswers, optionKey];

          setSelectedAnswers(newAnswers);
          onAnswerChange(question.id, newAnswers);
     };

     const handleEssayChange = (value: string) => {
          setEssayAnswer(value);
          onAnswerChange(question.id, value);
     };

     const handleFlagToggle = () => {
          const newFlagState = !isFlagged;
          setIsFlagged(newFlagState);
          onFlagToggle(question.id, newFlagState);
     };

     const renderQuestionType = () => {
          switch (question.question_type_id) {
               case '0': // Multiple Choice Complex (multiple answers)
                    return (
                         <div className="space-y-3">
                              <p className="text-sm text-gray-600 mb-4">
                                   <strong>Pilih semua jawaban yang benar:</strong>
                              </p>
                              {Object.entries(question.choices).map(([key, value]) => (
                                   <label
                                        key={key}
                                        className={`flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition-colors ${selectedAnswers.includes(key)
                                                  ? 'border-blue-500 bg-blue-50'
                                                  : 'border-gray-200 hover:border-gray-300'
                                             }`}
                                   >
                                        <input
                                             type="checkbox"
                                             checked={selectedAnswers.includes(key)}
                                             onChange={() => handleMultipleChoice(key)}
                                             className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                        />
                                        <div className="flex-1">
                                             <span className="font-medium text-gray-800">{key}.</span>
                                             <span className="ml-2 text-gray-700">{value}</span>
                                        </div>
                                   </label>
                              ))}
                         </div>
                    );

               case '1': // Multiple Choice Single
                    return (
                         <div className="space-y-3">
                              <p className="text-sm text-gray-600 mb-4">
                                   <strong>Pilih satu jawaban yang benar:</strong>
                              </p>
                              {Object.entries(question.choices).map(([key, value]) => (
                                   <label
                                        key={key}
                                        className={`flex items-start gap-3 p-4 border rounded-lg cursor-pointer transition-colors ${selectedAnswers.includes(key)
                                                  ? 'border-blue-500 bg-blue-50'
                                                  : 'border-gray-200 hover:border-gray-300'
                                             }`}
                                   >
                                        <input
                                             type="radio"
                                             name={`question-${question.id}`}
                                             checked={selectedAnswers.includes(key)}
                                             onChange={() => handleSingleChoice(key)}
                                             className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <div className="flex-1">
                                             <span className="font-medium text-gray-800">{key}.</span>
                                             <span className="ml-2 text-gray-700">{value}</span>
                                        </div>
                                   </label>
                              ))}
                         </div>
                    );

               case '2': // Essay
                    return (
                         <div className="space-y-3">
                              <p className="text-sm text-gray-600 mb-4">
                                   <strong>Jawab pertanyaan berikut dengan lengkap:</strong>
                              </p>
                              <textarea
                                   value={essayAnswer}
                                   onChange={(e) => handleEssayChange(e.target.value)}
                                   placeholder="Tulis jawaban Anda di sini..."
                                   className="w-full min-h-[200px] p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-vertical"
                              />
                              <p className="text-xs text-gray-500">
                                   Karakter: {essayAnswer.length}
                              </p>
                         </div>
                    );

               default:
                    return (
                         <div className="text-center py-8 text-gray-500">
                              <p>Tipe soal tidak dikenali</p>
                         </div>
                    );
          }
     };

     const getQuestionTypeLabel = () => {
          switch (question.question_type_id) {
               case '0': return 'Pilihan Ganda Kompleks';
               case '1': return 'Pilihan Ganda';
               case '2': return 'Essay';
               default: return 'Tipe Soal';
          }
     };

     return (
          <Card className="p-6 w-full max-w-4xl mx-auto">
               {/* Header */}
               <div className="flex justify-between items-start mb-6">
                    <div className="flex-1">
                         <div className="flex items-center gap-3 mb-2">
                              <span className="text-sm font-medium text-blue-600 bg-blue-100 px-3 py-1 rounded-full">
                                   {getQuestionTypeLabel()}
                              </span>
                              <span className="text-sm text-gray-500">
                                   {question.points} poin
                              </span>
                         </div>
                         <h3 className="text-lg font-semibold text-gray-800">
                              Soal {questionNumber} dari {totalQuestions}
                         </h3>
                    </div>

                    <Button
                         variant="ghost"
                         size="sm"
                         onClick={handleFlagToggle}
                         className={`flex items-center gap-2 ${isFlagged ? 'text-orange-600 hover:text-orange-700' : 'text-gray-400 hover:text-gray-600'
                              }`}
                    >
                         {isFlagged ? <Flag className="h-4 w-4" /> : <FlagOff className="h-4 w-4" />}
                         {isFlagged ? 'Ditandai' : 'Tandai'}
                    </Button>
               </div>

               {/* Question Text */}
               <div className="mb-6">
                    <div
                         className="text-gray-800 leading-relaxed"
                         dangerouslySetInnerHTML={{ __html: question.question_text }}
                    />
               </div>

               {/* Answer Options */}
               {renderQuestionType()}
          </Card>
     );
}
