# Scanning & Simulasi: Duplicate Exam Sessions Bug

## 🎯 Kesimpulan: ROOT CAUSE IDENTIFIED

**Masalah:** Satu user dapat membuat 2 session dengan exam_id sama, session kedua memiliki `total_score = 0`

**Penyebab:** Race Condition di concurrent requests tanpa database-level constraint

---

## 1️⃣ RACE CONDITION FLOW (Simulasi)

```
Waktu │ Request 1 (POST /api/exams/5/start)    │ Request 2 (POST /api/exams/5/start)
──────┼────────────────────────────────────────┼─────────────────────────────────
T0    │ HTTP Request masuk                      │ (waiting in queue)
T1    │ Mulai eksekusi ParticipantController   │ HTTP Request masuk
T2    │ DB::transaction() { ...                │ ParticipantController::start()
T3    │ Query: WHERE exam_id=5                 │ (compressed T0-T2)
      │      AND status='progress'              │
T4    │ Hasil: NULL (no session)                │ DB::transaction() {
      │                                         │
T5    │ lockForUpdate() → tidak ada baris!     │ Query: WHERE exam_id=5
      │ ⚠️ PROBLEM: lock tidak berfungsi       │      AND status='progress'
      │    karena tidak ada row yang di-lock   │
T6    │ Check if($existingSession) → FALSE     │ Hasil: NULL (no session)
      │ → Masuk ke bagian create                │
T7    │ Mulai create session                    │ lockForUpdate() → NULL
      │                                         │ ⚠️ LOCK TIDAK BERFUNGSI
T8    │ $user->examSessions()->create({        │ if($existingSession) → FALSE
      │   total_score: 0,                      │ → Masuk ke create
      │   status: 'progress',                  │
      │   ...                                  │ $user->examSessions()->create({
T9    │ });                                     │   total_score: 0,
      │ ✅ SESSION 1 CREATED (ID=100)          │   status: 'progress',
      │                                         │   ...
T10   │ DB::commit()                            │ });
      │                                         │ ❌ SESSION 2 CREATED (ID=101)
T11   │ Return response (token ABC123...)       │
      │                                         │ DB::commit()
T12   │ (request selesai)                       │ Return response (token XYZ789...)
      │                                         │
      │                                         │ (request selesai)
```

---

## 2️⃣ MENGAPA lockForUpdate() TIDAK BEKERJA

### Konsep lockForUpdate()
- **Pessimistic Lock** untuk existing rows
- Melakukan `SELECT ... FOR UPDATE`
- Hanya bisa lock row yang sudah ada (FOUND)

### Masalah di Kode Current
```php
$existingSession = $user->examSessions()
    ->where('exam_id', $examId)
    ->where('status', 'progress')
    ->lockForUpdate()  // ⚠️ Ini untuk lock EXISTING rows
    ->first();         // ⚠️ Tapi query kali ini return NULL!
```

**Ketika tidak ada session yang ditemukan:**
- Query: `SELECT * FROM exam_sessions WHERE user_id=5 AND exam_id=10 AND status='progress' FOR UPDATE`
- Hasil: Tidak ada row
- Lock: **Tidak ada yang bisa di-lock!** 
- Akibat: Request 2 bisa lolos juga

---

## 3️⃣ MENGAPA SESSION 2 MEMILIKI total_score = 0

**Dalam `start()` method:**
```php
$user->examSessions()->create([
    'session_token' => $sessionToken,
    'exam_id' => $examId,
    'status' => 'progress',
    'total_score' => 0,  // ← Default value saat create!
    'submitted_at' => $endTime,
    'started_at' => $startTime,
    // ...
]);
```

**Kenapa Session 2 tidak pernah dapat score:**

1. ✅ Session 1 & 2 kedua-duanya dibuat (race condition)
2. 📱 Frontend hanya menerima response dari Request 1 (Session 1 token)
3. ✅ Student submit ujian menggunakan Session 1 token (bukan Session 2)
4. 💾 Session 1 mendapat score saat submit
5. ❌ Session 2 tidak pernah submit (frontend tidak tahu ada Session 2)
6. ⏱️ Session 2 tetap `status = 'progress'` dengan `total_score = 0`
7. 👻 Bahkan jika session 2 expire, autoSubmitExpiredExam() tidak memberikan score

---

## 4️⃣ VERIFIKASI DI DATABASE

Jalankan query ini untuk memverifikasi:

```sql
-- Cari user dengan multiple progress sessions untuk exam yang sama
SELECT user_id, exam_id, COUNT(*) as session_count, 
       GROUP_CONCAT(id) as session_ids,
       GROUP_CONCAT(total_score) as scores
FROM exam_sessions
WHERE status = 'progress'
GROUP BY user_id, exam_id
HAVING COUNT(*) > 1
ORDER BY session_count DESC;

-- Cari sessions dengan score = 0 yang masih progress
SELECT id, user_id, exam_id, total_score, status, created_at
FROM exam_sessions
WHERE total_score = 0 AND status = 'progress'
ORDER BY created_at DESC
LIMIT 20;
```

---

## 5️⃣ MISSING: DATABASE CONSTRAINT

### Database Schema (Current)
```php
// File: database/migrations/2025_06_27_145800_create_exam_sessions_table.php
Schema::create('exam_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('exam_id')->constrained('exams');
    $table->string('session_token')->unique();    // ✓ Ini unique
    $table->string('status')->default('progress'); // 'progress', 'submitted', 'expired', dll
    
    // ❌ MISSING: PARTIAL unique constraint di (user_id, exam_id) 
    //             HANYA untuk status='progress'
    // ❌ MISSING: Tidak ada constraint yang mencegah duplicate ACTIVE sessions
    // 
    // NOTE: Ini bukan full unique, jadi tidak mencegah retakes!
    //       - Session 1 'progress' → submit → 'submitted' → membebaskan constraint
    //       - Session 2 'progress' → bisa dibuat (beda status!)
});
```

### Cleanup Migration (Tidak Mengatasi Akar Masalah)
```php
// File: database/migrations/2026_01_27_000001_add_unique_constraint_to_exam_sessions.php
// Ini hanya DELETE existing duplicates, tidak mencegah future duplicates!
DB::statement("
    DELETE es1 FROM exam_sessions es1 ...
    WHERE status = 'progress' AND COUNT(*) > 1
");
// ❌ Tidak ada constraint ditambahkan
```

**Comment di migration:**
> "We cannot add a unique constraint... because students can have multiple 'submited' sessions (retakes)"

**Ini logika yang SALAH!** Seharusnya:
- ✓ 1 session 'progress' per user per exam
- ✓ Multiple 'submited' sessions (untuk retakes)
- ✓ Multiple 'expired' atau 'cancelled' sessions

Solusi: Partial unique index

```sql
-- MySQL 8.0.13+
ALTER TABLE exam_sessions 
ADD UNIQUE KEY unique_user_exam_progress (user_id, exam_id) 
WHERE status = 'progress';
```

---

## 6️⃣ TIMELINE: BAGAIMANA BUG BISA TERJADI

### Skenario Real-World

1. **Student menekan tombol "Mulai Ujian" 2 kali dengan cepat**
   - Keyboard double-click atau network retry
   - 2 requests dikirim dalam milliseconds

2. **Web server menerima 2 requests secara concurrent**
   - Laravel router mengirim ke ParticipantController@start
   - Kedua request berjalan di separate worker/process

3. **Race Condition Terjadi (T0-T12 timeline di atas)**
   - Kedua request query database pada waktu yang sama
   - Kedua menemukan `NULL` (no existing session)
   - lockForUpdate() tidak berfungsi (nothing to lock)
   - Kedua request membuat session

4. **Hasil**
   - Session 1 (ID=100) dengan token ABC123
   - Session 2 (ID=101) dengan token XYZ789
   - Kedua memiliki user_id dan exam_id yang sama
   - Kedua memiliki status='progress'
   - Session 2 memiliki total_score=0

5. **Frontend Behavior**
   - Menerima 2 responses (tapi biasanya hanya pakai yang pertama)
   - Hanya submit Session 1
   - Session 2 menjadi "ghost" session

---

## 7️⃣ PROOF: Timing Requirement

**Race condition hanya terjadi jika:**

```
Condition: Timing yang TEPAT antara Request 1 dan Request 2
─────────────────────────────────────────────────────────────
T(query1) < T(query2) < T(lock1acquire) < T(lock2acquire) < T(create1) < T(create2)

Artinya:
- Request 1 query habis sebelum Request 2 query
- Request 2 query habis sebelum Request 1 acquire lock
- Dst...

Jika timing beda:
- Kalau Request 1 create dulu → Session 1 created
- Request 2 query masih NULL → Race condition!
- Kalau Request 1 lock acquired dulu → Session 2 blocked → Tidak race condition

KESIMPULAN: Ini murni race condition yang paling klasik!
```

---

## ✅ SOLUSI YANG DIREKOMENDASIKAN

### Opsi 1: Database Constraint (TERBAIK) 🏆

**⚠️ PENTING: Partial Unique Index (Bukan Full Unique!)**

```sql
-- MySQL 8.0.13+
-- PARTIAL index: Hanya untuk status='progress'
ALTER TABLE exam_sessions 
ADD UNIQUE KEY unique_user_exam_progress (user_id, exam_id) 
WHERE status = "progress";
```

**Mengapa Ini Tidak Mencegah Retakes?**

```
Skenario Retake:
─────────────────────────────────────────────────────────────

1. User membuat Session 1 (exam_id=5)
   Status: 'progress' ← Unique constraint berlaku
   Constraint check: ✓ Unique (no other progress session for this user+exam)
   ✅ Session 1 CREATED

2. User submit Session 1
   Status: 'submitted' (atau 'completed')
   Unique constraint: ✗ TIDAK BERLAKU (WHERE status='progress')
   ✅ Status updated to 'submitted'

3. User membuat Session 2 (exam_id=5, RETAKE)
   Status: 'progress'
   Constraint check: ✓ Unique (Session 1 is 'submitted', not 'progress')
   ✅ Session 2 CREATED (untuk retake!)

Hasil:
- Session 1 (exam_id=5): status='submitted', score=95
- Session 2 (exam_id=5): status='progress', score=0 (new attempt)
```

**Keuntungan:**
- ✅ Guaranteed data integrity
- ✅ Prevents race condition at database level
- ✅ Tidak mencegah retakes (multiple 'submitted' sessions OK)
- ✅ Simple dan foolproof
- ✅ No application logic needed

**Kerugian:**
- ⚠️ Memerlukan MySQL 8.0.13+

```php
// Migration baru: add_unique_constraint_progress_sessions.php
Schema::table('exam_sessions', function (Blueprint $table) {
    // Untuk MySQL 8.0.13+
    if (DB::connection()->getDriverName() === 'mysql') {
        $version = DB::select("SELECT VERSION() as version")[0]->version;
        if (version_compare($version, '8.0.13', '>=')) {
            DB::statement('
                ALTER TABLE exam_sessions 
                ADD UNIQUE KEY unique_user_exam_progress (user_id, exam_id) 
                WHERE status = "progress"
            ');
        }
    }
});
```

### Opsi 2: User-Level Locking (Fallback)

```php
// Di ParticipantController::start()
return DB::transaction(function () use ($user, $examId, ...) {
    // Lock the user (not just the session)
    $user = User::lockForUpdate()->find($user->id);
    
    // Now check again for existing session
    $existingSession = $user->examSessions()
        ->where('exam_id', $examId)
        ->where('status', 'progress')
        ->first();
    
    if ($existingSession) {
        // Return existing
    } else {
        // Now safe to create (user is locked)
        $user->examSessions()->create([...]);
    }
});
```

**Keuntungan:**
- ✅ Works on all MySQL versions
- ✅ Prevents race condition at app level
- ✅ Explicit lock semantics

**Kerugian:**
- ⚠️ Locks entire user (affects other exams)
- ⚠️ Slight performance impact

### Opsi 3: Hybrid (Recommended) 🛡️

Kombinasi keduanya:
1. ✅ Add database constraint jika MySQL 8.0.13+
2. ✅ Implement user-level locking sebagai fallback
3. ✅ Handle `IntegrityConstraintViolationException` di controller
4. ✅ Log violations untuk monitoring

---

## 🔎 RINGKASAN FINDINGS

| Aspek | Status | Penjelasan |
|-------|--------|-----------|
| **Database Constraint** | ❌ MISSING | Tidak ada UNIQUE(user_id, exam_id) PARTIAL WHERE status='progress' |
| **lockForUpdate()** | ⚠️ INSUFFICIENT | Tidak bisa lock NULL results (phantom reads) |
| **Application Logic** | ⚠️ RACY | Check-then-act pattern adalah TOCTOU |
| **Cleanup Migration** | ✓ PRESENT | Tapi hanya cleanup, tidak prevent future |
| **Impact to Retakes** | ✅ NONE | Constraint PARTIAL (hanya progress), jadi retakes tetap bisa |

---

## 📊 VISUAL: State Diagram

```
┌─────────────────────────────────────────────────────────┐
│ Initial: No session for user_id=5, exam_id=10          │
├─────────────────────────────────────────────────────────┤
│ exam_sessions table: (empty)                           │
└─────────────────────────────────────────────────────────┘
                          ↓
┌──────────────────────┬──────────────────────┐
│  Request 1 Query     │  Request 2 Query     │ ← Concurrent!
│  Result: NULL        │  Result: NULL        │
└──────────────────────┴──────────────────────┘
         ↓                       ↓
┌────────────────────┐  ┌────────────────────┐
│ Session 1 Created  │  │ Session 2 Created  │ ← Both succeed!
│ ID=100             │  │ ID=101             │
│ score=0            │  │ score=0            │ 🐛 BUG!
└────────────────────┘  └────────────────────┘
         ↓                       ↓
    Frontend submits        Frontend ignores
    Session 1               (or doesn't know)
         ↓
┌─────────────────────────────────────────┐
│ Final State:                            │
│ - Session 1: status='submited', score=X│
│ - Session 2: status='progress', score=0│ ← Ghost!
└─────────────────────────────────────────┘
```

---

## 📝 Files Created for Analysis

1. **`RACE_CONDITION_ANALYSIS.md`** - Detailed technical analysis
2. **`RACE_CONDITION_SIMULATION.php`** - Simulation script
3. **`FINDINGS_SUMMARY.md`** - This file

---

**Sekarang race condition ini sudah jelas dan sudah punya solusi yang tepat!**
