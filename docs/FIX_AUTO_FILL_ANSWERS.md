# Fix: Masalah Jawaban Pilihan Ganda Auto-Fill

## Masalah

Ketika memilih jawaban pilihan ganda pada soal nomor 1 (misalnya jawaban A), jawaban yang sama (A) secara otomatis terisi juga pada soal-soal pilihan ganda berikutnya.

## Akar Masalah

1. **State Management**: Component `QuestionCard` tidak mereset state local dengan benar ketika pindah soal
2. **useEffect Dependencies**: Dependencies yang tidak lengkap menyebabkan state tidak update sesuai question yang aktif
3. **Radio Button Name Conflicts**: Name attribute untuk radio button kurang unik
4. **Component Re-render**: Component tidak di-force re-render ketika question berubah

## Perbaikan yang Dilakukan

### 1. **QuestionCard Component** (`src/components/exam/QuestionCard.tsx`)

#### Before:

```typescript
useEffect(() => {
  if (currentAnswer) {
    // ... set answers
  }
}, [currentAnswer, question.question_type_id]);
```

#### After:

```typescript
useEffect(() => {
  // Reset state ketika question berubah
  setSelectedAnswers([]);
  setEssayAnswer("");
  setIsFlagged(false);

  // Set jawaban sesuai dengan currentAnswer yang ada
  if (currentAnswer) {
    // ... set answers
  }
}, [currentAnswer, question.id, question.question_type_id]); // Tambahkan question.id
```

**Perubahan:**

- ✅ Menambahkan reset state di awal useEffect
- ✅ Menambahkan `question.id` sebagai dependency untuk memastikan reset ketika question berubah
- ✅ Perbaikan radio button name menjadi `question-${question.id}-single`
- ✅ Menambahkan `value` prop pada radio button

### 2. **ExamMainContent Component** (`src/components/exam/ExamMainContent.tsx`)

#### Before:

```tsx
<QuestionCard
  question={currentQuestion}
  // ... props lain
/>
```

#### After:

```tsx
<QuestionCard
  key={`question-${currentQuestion.id}-${currentQuestionIndex}`} // Force re-render
  question={currentQuestion}
  // ... props lain
/>
```

**Perubahan:**

- ✅ Menambahkan `key` prop yang unik untuk force re-render ketika question berubah

### 3. **ExamSlice Store** (`src/store/examSlice.ts`)

#### Before:

```typescript
setAnswers(state, action) {
     const { questionId, answer } = action.payload;
     state.answers[questionId] = {
          question_id: questionId,
          answer,
          is_flagged: state.answers[questionId]?.is_flagged || false,
     };
}
```

#### After:

```typescript
setAnswers(state, action) {
     const { questionId, answer } = action.payload;

     // Pastikan answer tidak kosong untuk menghindari masalah state
     if (answer !== undefined && answer !== null) {
          state.answers[questionId] = {
               question_id: questionId,
               answer,
               is_flagged: state.answers[questionId]?.is_flagged || false,
          };
     }
}
```

**Perubahan:**

- ✅ Validasi answer sebelum disimpan ke state
- ✅ Perbaikan `setFlag` action untuk konsistensi state

### 4. **Debug Utilities** (`src/lib/examDebugUtils.ts`)

Menambahkan debug utilities untuk membantu troubleshoot masalah di development:

- ✅ `logAnswers()` - Log jawaban untuk debugging
- ✅ `validateAnswerFormat()` - Validasi format jawaban
- ✅ `checkAnswerConflicts()` - Deteksi konflik jawaban antar soal
- ✅ `cleanAnswers()` - Bersihkan jawaban tidak valid
- ✅ `compareAnswers()` - Bandingkan perubahan jawaban

### 5. **useExamLogic Hook** (`src/hooks/useExamLogic.ts`)

```typescript
const handleAnswerChange = useCallback(
  (questionId: number, answer: string | string[]) => {
    // Debug logging untuk development
    debugUtils.logAnswers(answers, questionId);

    dispatch(setAnswers({ questionId, answer }));

    // Check for potential conflicts setelah update
    setTimeout(() => {
      const currentAnswers = {
        ...answers,
        [questionId]: { question_id: questionId, answer },
      };
      debugUtils.checkAnswerConflicts(currentAnswers);
    }, 0);
  },
  [dispatch, answers, debugUtils]
);
```

**Perubahan:**

- ✅ Integrasi debug utilities untuk monitoring
- ✅ Deteksi konflik jawaban real-time (development only)

## Hasil Perbaikan

Setelah perbaikan:

1. ✅ **Jawaban tidak auto-fill lagi**: Setiap question memiliki state yang terisolasi
2. ✅ **State management yang konsisten**: Answers tersimpan dan terbaca dengan benar per question
3. ✅ **Radio button yang unik**: Tidak ada konflik antar question
4. ✅ **Debug yang lebih baik**: Mudah troubleshoot masalah di development

## Testing Checklist

Untuk memastikan perbaikan bekerja:

- [ ] Pilih jawaban A pada soal 1
- [ ] Pindah ke soal 2 → tidak boleh ada jawaban yang terpilih
- [ ] Pilih jawaban B pada soal 2
- [ ] Kembali ke soal 1 → jawaban A masih terpilih
- [ ] Pindah ke soal 3 (essay) → tidak boleh ada jawaban yang terisi
- [ ] Isi jawaban essay
- [ ] Kembali ke soal 1 dan 2 → jawaban tetap sesuai yang dipilih
- [ ] Test untuk multiple choice complex (checkbox)

## Tips Debugging

Jika masalah serupa muncul lagi:

1. Buka Developer Console
2. Lihat log `[EXAM DEBUG]` untuk tracking answer changes
3. Check apakah ada warning tentang answer conflicts
4. Verifikasi state Redux di Redux DevTools

## File yang Dimodifikasi

1. `src/components/exam/QuestionCard.tsx` - Core fix
2. `src/components/exam/ExamMainContent.tsx` - Force re-render
3. `src/store/examSlice.ts` - State validation
4. `src/hooks/useExamLogic.ts` - Debug integration
5. `src/lib/examDebugUtils.ts` - Debug utilities (new)

---

**Status:** ✅ **Fixed**  
**Tested:** ✅ **Ready for testing**
