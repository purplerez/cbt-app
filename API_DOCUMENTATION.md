# Exam Submit API Documentation

## Endpoint: Submit Exam

**URL:** `POST /api/siswa/exams/{examId}/submit`

**Authentication:** Bearer Token (Sanctum)

**Role Required:** siswa

### Request Headers

```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### Request Parameters

#### Path Parameters

-   `examId` (integer, required) - ID ujian yang akan disubmit

#### Request Body

```json
{
    "session_token": "string", // Required - Token sesi ujian
    "answers": {
        // Optional - Jawaban pilihan ganda
        "1": "A", // question_id: answer
        "2": "B",
        "3": "C"
    },
    "essay_answers": {
        // Optional - Jawaban essay
        "4": "Jawaban essay panjang...",
        "5": "Jawaban essay lainnya..."
    },
    "force_submit": false, // Optional - Paksa submit meski waktu belum habis
    "final_submit": true // Optional - Menandai ini adalah submit final
}
```

### Response Examples

#### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Ujian berhasil diselesaikan",
    "data": {
        "session_id": 123,
        "exam_title": "Ujian Matematika Kelas 10",
        "total_score": 85.5,
        "max_score": 100.0,
        "percentage": 85.5,
        "grade": {
            "letter": "B",
            "description": "Baik"
        },
        "total_questions": 20,
        "answered_questions": 18,
        "unanswered_questions": 2,
        "submission_time": "2025-09-23T10:30:00.000Z",
        "score_breakdown": {
            "multiple_choice": {
                "correct": 15,
                "total": 18,
                "score": 75.0,
                "max_score": 90.0
            },
            "essay": {
                "answered": 2,
                "total": 2,
                "score": 0,
                "max_score": 10.0
            },
            "true_false": {
                "correct": 0,
                "total": 0,
                "score": 0,
                "max_score": 0
            },
            "complex_multiple": {
                "correct": 0,
                "total": 0,
                "score": 0,
                "max_score": 0
            }
        },
        "detailed_results": [
            {
                "question_id": 1,
                "question_type": 0,
                "question_type_label": "Pilihan Ganda",
                "points": 5.0,
                "earned_points": 5.0,
                "is_correct": true,
                "is_answered": true,
                "student_answer": "A",
                "correct_answer": "A"
            }
        ]
    }
}
```

#### Error Responses

**Session Not Found (404 Not Found)**

```json
{
    "success": false,
    "message": "Session ujian tidak ditemukan atau sudah berakhir"
}
```

**Time Expired (422 Unprocessable Entity)**

```json
{
    "success": false,
    "message": "Waktu ujian telah berakhir"
}
```

**Validation Error (422 Unprocessable Entity)**

```json
{
    "success": false,
    "message": "Data yang dikirim tidak valid",
    "errors": {
        "session_token": ["Token sesi ujian diperlukan"],
        "answers.1": ["Jawaban pilihan ganda terlalu panjang"]
    }
}
```

**Server Error (500 Internal Server Error)**

```json
{
    "success": false,
    "message": "Terjadi kesalahan saat menyimpan hasil ujian. Silakan coba lagi."
}
```

---

## Endpoint: Get Session Status

**URL:** `POST /api/siswa/exams/{examId}/status`

**Authentication:** Bearer Token (Sanctum)

**Role Required:** siswa

### Request Body

```json
{
    "session_token": "string" // Required - Token sesi ujian
}
```

### Success Response (200 OK)

```json
{
    "success": true,
    "data": {
        "session_id": 123,
        "status": "progress", // progress, completed, auto_submitted
        "time_remaining": 1800, // dalam detik
        "started_at": "2025-09-23T09:00:00.000Z",
        "end_time": "2025-09-23T11:00:00.000Z",
        "is_expired": false
    }
}
```

---

## Business Logic

### Scoring System

1. **Multiple Choice & True/False**: Otomatis dinilai berdasarkan kunci jawaban
2. **Essay**: Perlu penilaian manual (skor 0 hingga dikoreksi)
3. **Complex Multiple Choice**: Otomatis dinilai dengan penilaian yang ketat

### Grade Calculation

-   A (90-100%): Sangat Baik
-   B (80-89%): Baik
-   C (70-79%): Cukup
-   D (60-69%): Kurang
-   E (0-59%): Sangat Kurang

### Auto-Submit Feature

-   Ujian akan otomatis disubmit jika waktu habis
-   Status berubah menjadi `auto_submitted`
-   Semua jawaban yang tersimpan akan dihitung

### Security Features

-   Session token validation
-   User authorization check
-   Time limit enforcement
-   IP address logging
-   User agent tracking

### Events & Logging

-   Event `ExamSubmitted` dipicu setelah submit berhasil
-   Detailed logging untuk audit trail
-   Notifikasi admin untuk skor exceptional
-   Statistik siswa diupdate otomatis
