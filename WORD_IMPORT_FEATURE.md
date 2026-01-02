# Word Document Question Import Feature

## Overview
Feature import soal dari file Word document (.docx) dengan dukungan:
- ✅ Format text (bold, italic, underline, strikethrough)
- ✅ Auto-deteksi jenis soal (PG, PG Kompleks, Benar/Salah, Essay)
- ✅ Ekstraksi otomatis gambar dari dokumen (future enhancement)
- ✅ Fallback parsing dengan XML jika PHPWord gagal

## Format Word Document

### Struktur yang Didukung
```
1. Pertanyaan pertama (optional: bold/italic/underline)?
A. Pilihan A
B. Pilihan B
C. Pilihan C
D. Pilihan D

2. Pertanyaan kedua tentang topik lain?
A. Jawab 1
B. Jawab 2

3. Pertanyaan True/False.
A. Benar
B. Salah

4. Pertanyaan essay (tanpa pilihan jawaban)
```

### Rules
- Soal HARUS dimulai dengan nomor dan titik/kurung: `1.`, `1)`, `2.`, dst
- Pilihan HARUS dimulai dengan huruf (A-D) atau nomor (1-4) dan titik/kurung: `A.`, `A)`, `1.`, dst
- Baris kosong akan di-skip
- Format text di Word akan dipertahankan (bold, italic, dll)

## Proses Upload

1. Klik tombol "Import Word" di halaman Manage Questions
2. Pilih file Word (.docx)
3. Sistem akan parse file dan show preview soal
4. Review soal yang terdeteksi
5. Klik "Simpan Semua Soal" untuk save ke database

## Auto-Detection Jenis Soal

| Kondisi | Tipe | Keterangan |
|---------|------|-----------|
| 0 pilihan | 3 (Essay) | Isian Singkat / Essay |
| 2 pilihan | 2 (True/False) | Benar/Salah |
| 3-4 pilihan | 0 (PG) | Pilihan Ganda (single answer) |
| Harus custom | 1 (PG Kompleks) | Pilihan Ganda Kompleks (multiple answers) |

**Note**: Jenis soal 1 (PG Kompleks) harus di-set secara manual karena sistem tidak bisa auto-detect multiple correct answers dari format dokumen biasa.

## Files Modified

1. **app/Services/WordQuestionImportService.php** (NEW)
   - Parse Word files dengan dua metode: PhpWord library & XML parsing
   - Extract questions dan choices
   - Auto-detect question types
   - Handle formatting

2. **app/Http/Controllers/QuestionController.php**
   - `importFromWord()` - Handle Word file upload
   - `saveWordQuestions()` - Save parsed questions to database

3. **routes/web.php**
   - `POST /exams/banksoal/{exam}/import-word` - Import route
   - `POST /exams/banksoal/save-word-questions` - Save route

4. **resources/views/admin/manageperexam.blade.php**
   - Add "Import Word" button UI
   - Add preview modal
   - Add JavaScript handler: `handleWordFileUpload()`, `saveWordQuestions()`

## Library Dependencies

- **phpoffice/phpword** - Parse Word documents (1.4.0)
- **ZipArchive** - Built-in PHP extension untuk XML fallback parsing

## API Responses

### Import Endpoint Response
```json
{
  "success": true,
  "message": "File Word berhasil diparse. 4 soal ditemukan.",
  "questions": [
    {
      "question_text": "Pertanyaan?",
      "question_html": "Pertanyaan?",
      "question_type": 0,
      "choices": {
        "1": "Pilihan A",
        "2": "Pilihan B",
        "3": "Pilihan C",
        "4": "Pilihan D"
      },
      "choice_texts": {...},
      "answer_key": [],
      "question_images": []
    }
  ],
  "exam_id": 1
}
```

### Save Endpoint Response
```json
{
  "success": true,
  "message": "4 dari 4 soal berhasil disimpan",
  "saved_count": 4,
  "total_count": 4
}
```

## Notes

- File Word yang diupload akan dihapus secara otomatis setelah parsing
- User harus merevisi manual soal yang belum sempurna setelah import
- Answer key harus di-set manual melalui edit form
- Question type bisa di-ubah setelah import jika diperlukan
