/**
 * Contoh penggunaan examService dengan backend validation rules yang baru
 */

import { examService } from '@/services/exam';
import { StudentAnswer, ParsedQuestion } from '@/types';

// Contoh data dummy untuk demonstrasi
const sampleQuestions: ParsedQuestion[] = [
     {
          id: 1,
          exam_id: 1,
          question_type_id: "1", // Multiple Choice Single
          question_text: "Apa ibu kota Indonesia?",
          choices: { "A": "Jakarta", "B": "Surabaya", "C": "Bandung", "D": "Medan" },
          answer_key: ["A"],
          points: 10,
          created_by: 1,
          created_at: "2024-01-01T00:00:00Z",
          updated_at: "2024-01-01T00:00:00Z"
     },
     {
          id: 2,
          exam_id: 1,
          question_type_id: "0", // Multiple Choice Complex
          question_text: "Pilih negara ASEAN:",
          choices: { "A": "Indonesia", "B": "Malaysia", "C": "Australia", "D": "Thailand" },
          answer_key: ["A", "B", "D"],
          points: 15,
          created_by: 1,
          created_at: "2024-01-01T00:00:00Z",
          updated_at: "2024-01-01T00:00:00Z"
     },
     {
          id: 3,
          exam_id: 1,
          question_type_id: "2", // Essay
          question_text: "Jelaskan tentang sejarah kemerdekaan Indonesia",
          choices: {},
          answer_key: [],
          points: 25,
          created_by: 1,
          created_at: "2024-01-01T00:00:00Z",
          updated_at: "2024-01-01T00:00:00Z"
     }
];

const sampleAnswers: Record<number, StudentAnswer> = {
     1: {
          question_id: 1,
          answer: "A", // Single choice - akan menjadi string max 10 chars
          is_flagged: false
     },
     2: {
          question_id: 2,
          answer: ["A", "B", "D"], // Multiple choice - akan di-join menjadi "A,B,D" max 10 chars
          is_flagged: false
     },
     3: {
          question_id: 3,
          answer: "Indonesia merdeka pada tanggal 17 Agustus 1945...", // Essay - max 5000 chars
          is_flagged: false
     }
};

export const examSubmissionExamples = {
     // Contoh 1: Auto-save (tidak final, untuk save berkala)
     autoSaveExample: async () => {
          try {
               const result = await examService.autoSaveAnswers(
                    1, // examId
                    sampleAnswers,
                    sampleQuestions
               );

               console.log('Auto-save berhasil:', result);
               // Backend akan menerima:
               // {
               //      session_token: "xxx...",
               //      answers: { "1": "A", "2": "A,B,D" },
               //      essay_answers: { "3": "Indonesia merdeka pada tanggal..." },
               //      force_submit: false,
               //      final_submit: false
               // }
          } catch (error) {
               console.error('Auto-save gagal:', error);
          }
     },

     // Contoh 2: Final submit (submit akhir)
     finalSubmitExample: async () => {
          try {
               const result = await examService.submitExam(
                    1, // examId
                    sampleAnswers,
                    sampleQuestions,
                    {
                         finalSubmit: true, // Ini akan set final_submit: true
                         forceSubmit: false
                    }
               );

               console.log('Final submit berhasil:', result);
               // Backend akan menerima:
               // {
               //      session_token: "xxx...",
               //      answers: { "1": "A", "2": "A,B,D" },
               //      essay_answers: { "3": "Indonesia merdeka pada tanggal..." },
               //      force_submit: false,
               //      final_submit: true
               // }
          } catch (error) {
               console.error('Final submit gagal:', error);
          }
     },

     // Contoh 3: Force submit (paksa submit, misal waktu habis)
     forceSubmitExample: async () => {
          try {
               const result = await examService.submitExam(
                    1, // examId
                    sampleAnswers,
                    sampleQuestions,
                    {
                         finalSubmit: true,
                         forceSubmit: true // Ini akan set force_submit: true
                    }
               );

               console.log('Force submit berhasil:', result);
               // Backend akan menerima:
               // {
               //      session_token: "xxx...",
               //      answers: { "1": "A", "2": "A,B,D" },
               //      essay_answers: { "3": "Indonesia merdeka pada tanggal..." },
               //      force_submit: true,
               //      final_submit: true
               // }
          } catch (error) {
               console.error('Force submit gagal:', error);
          }
     }
};

/**
 * Validasi yang dilakukan oleh backend:
 * 
 * 1. session_token: required|string|min:10|max:255
 *    - Harus ada dan berupa string 10-255 karakter
 * 
 * 2. answers: nullable|array
 *    - Boleh null atau array untuk jawaban multiple choice
 * 
 * 3. answers.*: string|max:10
 *    - Setiap jawaban multiple choice maksimal 10 karakter
 *    - Contoh: "A", "B,C", "A,B,C,D" (untuk complex choice)
 * 
 * 4. essay_answers: nullable|array
 *    - Boleh null atau array untuk jawaban essay
 * 
 * 5. essay_answers.*: string|max:5000
 *    - Setiap jawaban essay maksimal 5000 karakter
 * 
 * 6. force_submit: boolean
 *    - true: paksa submit (misal waktu habis)
 *    - false: submit normal
 * 
 * 7. final_submit: boolean
 *    - true: submit akhir (tidak bisa diubah lagi)
 *    - false: auto-save (masih bisa diubah)
 */
