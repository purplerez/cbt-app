# Dokumentasi Backend Validation Rules untuk Exam Submission

## Overview

Backend Anda menggunakan validation rules untuk endpoint exam submission yang perlu disesuaikan dengan frontend. Berikut adalah penjelasan lengkap dan implementasi yang sudah dilakukan.

## Backend Validation Rules

```php
public function rules(): array
{
    return [
        'session_token' => 'required|string|min:10|max:255',
        'answers' => 'nullable|array',
        'answers.*' => 'string|max:10', // For multiple choice answers (A, B, C, D, etc.)
        'essay_answers' => 'nullable|array',
        'essay_answers.*' => 'string|max:5000', // For essay answers
        'force_submit' => 'boolean',
        'final_submit' => 'boolean' // To differentiate between auto-save and final submit
    ];
}
```

### Penjelasan Rules:

1. **`session_token`**:

   - Wajib ada (required)
   - Tipe string dengan panjang 10-255 karakter
   - Digunakan untuk validasi sesi exam

2. **`answers`**:

   - Boleh kosong (nullable) atau array
   - Berisi jawaban untuk soal multiple choice

3. **`answers.*`**:

   - Setiap elemen dalam array `answers` harus string
   - Maksimal 10 karakter per jawaban
   - Format: `"A"`, `"B,C"`, `"A,B,C,D"` (untuk multiple choice complex)

4. **`essay_answers`**:

   - Boleh kosong (nullable) atau array
   - Berisi jawaban untuk soal essay

5. **`essay_answers.*`**:

   - Setiap elemen dalam array `essay_answers` harus string
   - Maksimal 5000 karakter per jawaban essay

6. **`force_submit`**:

   - Tipe boolean
   - `true`: Paksa submit (misal waktu habis)
   - `false`: Submit normal

7. **`final_submit`**:
   - Tipe boolean
   - `true`: Submit akhir (tidak bisa diubah lagi)
   - `false`: Auto-save (masih bisa diubah)

## Implementasi Frontend

### 1. Updated Types (`src/types/index.ts`)

```typescript
export interface ExamSubmitOptions {
  forceSubmit?: boolean;
  finalSubmit?: boolean;
}

export interface AutoSaveResponse {
  success: boolean;
  message: string;
  data?: {
    saved_answers: number;
    last_saved_at: string;
  };
}
```

### 2. Updated Exam Service (`src/services/exam.ts`)

#### `submitExam` Method:

- Menerima parameter `questions` untuk menentukan tipe soal
- Memisahkan jawaban berdasarkan tipe soal (multiple choice vs essay)
- Mendukung `finalSubmit` dan `forceSubmit` options
- Validasi panjang karakter sesuai backend rules

#### `autoSaveAnswers` Method (Baru):

- Khusus untuk auto-save berkala
- Selalu menggunakan `final_submit: false`
- Format data sama dengan `submitExam`

### 3. Question Type Mapping

```typescript
// Question types dari backend:
// "0" = Multiple Choice Complex (bisa pilih lebih dari 1)
// "1" = Multiple Choice Single (pilih 1 saja)
// "2" = Essay

// Contoh mapping dalam code:
if (question.question_type_id === "2") {
  // Essay question -> essay_answers
  essayAnswers[answer.question_id.toString()] = essayAnswer.substring(0, 5000);
} else {
  // Multiple choice -> answers
  multipleChoiceAnswers[answer.question_id.toString()] = mcAnswer.substring(
    0,
    10
  );
}
```

## Format Request ke Backend

### Auto-Save Request:

```json
{
  "session_token": "abc123...",
  "answers": {
    "1": "A",
    "2": "A,B,D"
  },
  "essay_answers": {
    "3": "Penjelasan essay yang panjang..."
  },
  "force_submit": false,
  "final_submit": false
}
```

### Final Submit Request:

```json
{
  "session_token": "abc123...",
  "answers": {
    "1": "A",
    "2": "A,B,D"
  },
  "essay_answers": {
    "3": "Penjelasan essay yang panjang..."
  },
  "force_submit": false,
  "final_submit": true
}
```

### Force Submit Request (Time Expired):

```json
{
  "session_token": "abc123...",
  "answers": {
    "1": "A",
    "2": "A,B,D"
  },
  "essay_answers": {
    "3": "Penjelasan essay yang panjang..."
  },
  "force_submit": true,
  "final_submit": true
}
```

## Cara Penggunaan

### 1. Auto-Save (Berkala)

```typescript
// Untuk save otomatis setiap X menit
await examService.autoSaveAnswers(examId, answers, questions);
```

### 2. Final Submit (Submit Akhir)

```typescript
// Submit akhir oleh user
await examService.submitExam(examId, answers, questions, {
  finalSubmit: true,
  forceSubmit: false,
});
```

### 3. Force Submit (Waktu Habis)

```typescript
// Submit paksa saat waktu habis
await examService.submitExam(examId, answers, questions, {
  finalSubmit: true,
  forceSubmit: true,
});
```

## Validasi di Frontend

Sebelum mengirim request, pastikan:

1. **Session Token**: Ada dan valid (disimpan di localStorage)
2. **Multiple Choice Answers**: Setiap jawaban ≤ 10 karakter
3. **Essay Answers**: Setiap jawaban ≤ 5000 karakter
4. **Question Mapping**: Jawaban dipetakan ke tipe soal yang benar

## Error Handling

Backend akan mengembalikan validation error jika:

- Session token tidak ada/invalid
- Jawaban multiple choice > 10 karakter
- Jawaban essay > 5000 karakter
- Format boolean tidak valid untuk force_submit/final_submit

Pastikan frontend handle error ini dengan appropriately.

## Migration Notes

Jika ada komponen yang masih menggunakan method `submitExam` lama:

1. Tambahkan parameter `questions`
2. Tambahkan parameter `options` jika ingin menggunakan finalSubmit
3. Update call sites yang menggunakan auto-save untuk pakai method `autoSaveAnswers`

## Testing

Lihat file `src/examples/examSubmissionExamples.ts` untuk contoh lengkap penggunaan semua method.
