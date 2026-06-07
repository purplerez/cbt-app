# Duplicate Exam Session Race Condition Analysis

## Problem Summary
User dapat membuat 2 ExamSession untuk exam_id yang sama. Session kedua tidak memiliki score (ini yang paling penting - menunjukkan session "fake").

## Root Cause Analysis

### Why This Happens

#### Scenario 1: Race Condition in Concurrent Requests ⚠️ **MOST LIKELY**

**Timeline of events:**

```
Time  | Request 1                                | Request 2
------|------------------------------------------|------------------------------------------
T0    | GET /api/exams/{examId}/start            | (waiting)
T1    | DB::transaction() starts                 | GET /api/exams/{examId}/start
T2    | Query: WHERE exam_id AND status=progress | (waiting)
T3    | Result: NULL (no existing session)       | DB::transaction() starts
T4    | (preparing to lock)                      | Query: WHERE exam_id AND status=progress
T5    | (preparing to lock)                      | Result: NULL (no existing session)
T6    | lockForUpdate() acquired on NULL result  | lockForUpdate() acquired on NULL result
T7    | Lock doesn't block (no row to lock!)    | Lock doesn't block (no row to lock!)
T8    | Creates Session 1                        | Creates Session 2 ❌
T9    | DB::commit()                             | DB::commit()
```

**Key Issue:** `lockForUpdate()` only locks EXISTING rows. When query returns NULL (no existing session), there's nothing to lock!

#### Why Session 2 Has No Score

When Session 2 is created:
- It has `total_score = 0` (from create statement in start() method)
- If user quickly submitted Session 1 before Session 2 was fully created
- Session 2 remains in 'progress' status with 0 score
- Never gets scored because it's a "ghost" session

### Current Code Analysis

**File:** `app/Http/Controllers/Api/ParticipantController.php` (line 79-200)

```php
return DB::transaction(function () use ($user, $examId, ...) {
    // ⚠️ PROBLEM: lockForUpdate() on NULL result does nothing!
    $existingSession = $user->examSessions()
        ->where('exam_id', $examId)
        ->where('status', 'progress')
        ->lockForUpdate()  // This only locks FOUND rows
        ->first();

    if ($existingSession) {
        // Return existing
    }
    
    // If two requests reach here at same time...
    // Both will create new session! 🐛
    $user->examSessions()->create([
        'user_id' => $user->id,
        'exam_id' => $examId,
        'session_token' => $sessionToken,
        'started_at' => $startTime,
        'submited_at' => $endTime,
        'total_score' => 0,  // ← Why Session 2 has no score
        'status' => 'progress',
        // ...
    ]);
});
```

## Why Lock Doesn't Work Here

1. **Lock Semantics**: `lockForUpdate()` is a **pessimistic lock** for existing rows
2. **Phantom Read Problem**: When no rows exist, there's nothing to lock
3. **Transaction Isolation**: Even with transactions, if both requests:
   - Query and find no existing session
   - Both try to create
   - At least one will succeed, possibly both if timing is right

## Why This Is A Race Condition (Not Prevented by Current Code)

| Aspect | Status | Reason |
|--------|--------|--------|
| **Database Constraint** | ❌ MISSING | No UNIQUE(user_id, exam_id) constraint |
| **lockForUpdate()** | ⚠️ INSUFFICIENT | Only locks existing rows, not absence |
| **Application Check** | ⚠️ INSUFFICIENT | Check-then-act pattern is inherently racy |

## Database Schema Issue

**File:** `database/migrations/2025_06_27_145800_create_exam_sessions_table.php`

```php
Schema::create('exam_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
    $table->string('session_token')->unique();  // ✓ This is unique
    $table->timestamp('started_at');
    $table->timestamp('submited_at');
    // ... other fields
    // ❌ MISSING: unique('user_id', 'exam_id')  when status = 'progress'
    // ❌ MISSING: unique('user_id', 'exam_id') with constraint WHERE status='progress'
});
```

## The Cleanup Migration (Doesn't Solve Root Cause)

**File:** `database/migrations/2026_01_27_000001_add_unique_constraint_to_exam_sessions.php`

This migration:
- ✓ Deletes existing duplicate 'progress' sessions
- ❌ Does NOT add any constraint
- ❌ Comment says: "We cannot add a unique constraint... because students can have multiple 'submited' sessions (retakes)"

**But this is flawed logic!** You CAN have:
- Multiple 'submited' sessions (for retakes)
- ONE 'progress' session at a time
- Multiple 'expired' sessions
- Multiple 'cancelled' sessions

So the constraint should be:
```sql
UNIQUE KEY user_exam_progress (user_id, exam_id) USING BTREE 
WHERE status = 'progress'
```

But MySQL didn't support partial unique indexes until version 8.0.13.

## Sequence of Events (Real World Scenario)

1. **Student clicks "Mulai Ujian" button twice rapidly** (within milliseconds)
   - Browser makes 2 requests to POST /api/exams/{examId}/start
   - Both reach server nearly simultaneously

2. **Request 1 & 2 race condition**
   - T0-T5: Both execute parallel until lock attempt
   - T6: Both query returns NULL (no existing session)
   - T7-T8: Both requests create new session (database allows it)

3. **Results**
   - ✓ Session 1 created (ID=100)
   - ✓ Session 2 created (ID=101) with same user_id & exam_id
   - Session 2 gets total_score = 0 in create() statement
   - Session 2 token generated but might be used by Session 1 if frontend caches old response

4. **Why Session 2 Never Gets Score**
   - If only Session 1 is submitted via frontend
   - Session 2 remains in 'progress' with total_score=0
   - autoSubmitExpiredExam() only runs when session expires (if user logs back in)
   - So Session 2 can persist indefinitely with score=0

## Verification Checklist

You can verify this is happening by running:

```sql
-- Find users with multiple 'progress' sessions for same exam
SELECT user_id, exam_id, COUNT(*) as session_count
FROM exam_sessions
WHERE status = 'progress'
GROUP BY user_id, exam_id
HAVING COUNT(*) > 1;

-- Find sessions with zero score
SELECT id, user_id, exam_id, total_score, status
FROM exam_sessions
WHERE total_score = 0 AND status = 'progress'
ORDER BY created_at DESC;

-- Find user sessions with multiple progress status
SELECT user_id, exam_id, id, session_token, total_score, status, created_at
FROM exam_sessions
WHERE user_id IN (
    SELECT user_id FROM exam_sessions 
    WHERE status = 'progress' 
    GROUP BY user_id, exam_id 
    HAVING COUNT(*) > 1
)
AND exam_id IN (
    SELECT exam_id FROM exam_sessions 
    WHERE status = 'progress' 
    GROUP BY user_id, exam_id 
    HAVING COUNT(*) > 1
)
ORDER BY user_id, exam_id, created_at;
```

## Solution Options

### Option 1: Database Level - UNIQUE Constraint (RECOMMENDED) 🏆
Add constraint that prevents multiple 'progress' sessions:

```php
// In migration
Schema::table('exam_sessions', function (Blueprint $table) {
    // For MySQL 8.0.13+
    DB::statement('ALTER TABLE exam_sessions ADD UNIQUE KEY unique_user_exam_progress 
        (user_id, exam_id) WHERE status = "progress"');
});
```

**Pros:**
- Guaranteed data integrity
- Prevents race condition at database level
- Simple and foolproof

**Cons:**
- Requires MySQL 8.0.13+ for partial unique index
- If on older MySQL, need workaround

### Option 2: Application Level - SELECT FOR UPDATE (CURRENT) 🔒
Fix the existing lockForUpdate() pattern:

Instead of:
```php
$existingSession = $user->examSessions()
    ->where('exam_id', $examId)
    ->where('status', 'progress')
    ->lockForUpdate()
    ->first();
```

Use a different approach - lock on user_id level to create a session slot:

```php
// Lock the user to prevent concurrent session creation
$user = User::lockForUpdate()->find($user->id);

// Then check again for existing session
$existingSession = $user->examSessions()
    ->where('exam_id', $examId)
    ->where('status', 'progress')
    ->first();

// Now safe to create
if (!$existingSession) {
    $user->examSessions()->create([...]);
}
```

**Pros:**
- No database schema changes needed
- Works on all MySQL versions
- Explicit lock semantics

**Cons:**
- Locks entire user (might affect other exams)
- Slight performance impact

### Option 3: Hybrid - Database + Application 🛡️
- Add unique constraint if MySQL 8.0.13+
- Fallback to application check with better error handling
- Catches race condition at database level with proper error handling

## Recommended Implementation

Use **Option 3** (Hybrid approach):

1. Add migration to create unique constraint (with fallback for older MySQL)
2. Add database error handling in controller
3. Keep application-level check as safety net
4. Handle IntegrityConstraintViolationException for duplicate creation attempts

## Impact on "No Score" Issue

Once you prevent duplicate session creation, the "Session 2 with no score" problem disappears automatically because:
- Only 1 'progress' session can exist per user per exam
- If user tries to start again, they get existing session back
- Score gets calculated only on valid, single submit
