<?php
/**
 * RACE CONDITION SIMULATION
 * 
 * This file simulates how duplicate exam sessions can be created
 * when two requests arrive simultaneously without proper locking.
 * 
 * Scenario: Student clicks "Mulai Ujian" button twice rapidly
 * Result: Two exam sessions created for same user and exam
 */

// ============================================================================
// SCENARIO 1: CONCURRENT REQUESTS - THE BUG
// ============================================================================

echo "=" . str_repeat("=", 78) . "\n";
echo "SCENARIO 1: RACE CONDITION - How Duplicate Sessions Are Created\n";
echo "=" . str_repeat("=", 78) . "\n\n";

echo "📊 Timeline of Concurrent Requests:\n";
echo str_repeat("-", 80) . "\n";
echo "Time | Request 1                              | Request 2                       \n";
echo str_repeat("-", 80) . "\n";

$timeline = [
    "T0"  => "Start HTTP request                  | waiting",
    "T1"  => "POST /api/exams/5/start             | Start HTTP request",
    "T2"  => "ParticipantController start()       | router processing",
    "T3"  => "Get user from request               | ParticipantController start()",
    "T4"  => "DB transaction begins               | Get user from request",
    "T5"  => "Query WHERE exam_id=5               | DB transaction begins",
    "     | AND status=progress                 | Query WHERE exam_id=5",
    "T6"  => "Query result NULL (no row)          | Query result NULL",
    "     | no existing session                 | no existing session",
    "T7"  => "lockForUpdate() called              | lockForUpdate() called",
    "     | lockForUpdate() on NULL             | lockForUpdate() on NULL",
    "     | does NOTHING no row                 | does NOTHING",
    "T8"  => "Check if existingSession false      | Check if existingSession",
    "     | not entered                         | false not entered",
    "T9"  => "Generate session_token              | Generate session_token",
    "     | sessionToken = bin2hex...           | sessionToken = bin2hex",
    "T10" => "create new session                  | create new session",
    "     | session_token ABC123                | session_token XYZ789",
    "     | exam_id 5                           | exam_id 5",
    "     | status progress                     | status progress",
    "     | total_score 0 IMPORTANT             | total_score 0",
    "     | Session 1 CREATED                   | Session 2 CREATED BUG",
    "T11" => "DB commit                           | DB commit",
    "T12" => "Return response Session 1           | Return response Session 2",
    "     | token ABC123                        | token XYZ789",
];

foreach ($timeline as $time => $event) {
    echo sprintf("%4s | %s\n", $time, $event);
}

echo str_repeat("-", 80) . "\n\n";

// ============================================================================
// THE PROBLEM EXPLAINED
// ============================================================================

echo "❌ THE BUG: Why lockForUpdate() Doesn't Work Here\n";
echo str_repeat("~", 80) . "\n\n";

echo "Current Code (ParticipantController@start):\n";
echo "────────────────────────────────────────────────────────────────────────────\n";
echo <<<'CODE'
return DB::transaction(function () use ($user, $examId, ...) {
    // ⚠️ PROBLEM: This locks rows IF they exist
    $existingSession = $user->examSessions()
        ->where('exam_id', $examId)
        ->where('status', 'progress')
        ->lockForUpdate()  // SELECT ... FOR UPDATE
        ->first();

    if ($existingSession) {
        // Return existing session
        return response()->json(['success' => true, ...]);
    }

    // ⚠️ RACE CONDITION: If two requests reach here simultaneously...
    // Both will try to create a new session!
    
    $user->examSessions()->create([
        'exam_id' => $examId,
        'status' => 'progress',
        'total_score' => 0,  // ← Why Session 2 has no score!
        ...
    ]);
});
CODE;
echo "────────────────────────────────────────────────────────────────────────────\n\n";

echo "🔍 Why This Fails:\n";
echo "──────────────────\n";
echo "1. lockForUpdate() is a PESSIMISTIC lock for EXISTING rows\n";
echo "2. When no session exists, query returns NULL\n";
echo "3. There is NO row to lock, so lockForUpdate() does nothing\n";
echo "4. Both concurrent requests pass the 'if (\$existingSession)' check\n";
echo "5. Both try to INSERT → Database allows both (no UNIQUE constraint)\n";
echo "6. Result: 2 sessions created!\n\n";

// ============================================================================
// DATABASE QUERY ANALYSIS
// ============================================================================

echo "=" . str_repeat("=", 78) . "\n";
echo "DATABASE LEVEL: Why No Constraint?  \n";
echo "=" . str_repeat("=", 78) . "\n\n";

echo "Migration: database/migrations/2025_06_27_145800_create_exam_sessions_table.php\n";
echo str_repeat("─", 80) . "\n";
echo <<<'SQL'
Schema::create('exam_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users');
    $table->foreignId('exam_id')->constrained('exams');
    $table->string('session_token')->unique();        // ✓ This is unique
    $table->timestamp('started_at');
    $table->timestamp('submited_at');
    $table->decimal('total_score', 8, 2);
    $table->enum('status', ['progress', 'submited', 'expired', 'cancelled']);
    // ...
    // ❌ MISSING: unique('user_id', 'exam_id') 
    // ❌ MISSING: unique index WHERE status = 'progress'
});
SQL;
echo str_repeat("─", 80) . "\n\n";

echo "Migration: database/migrations/2026_01_27_000001_add_unique_constraint_to_exam_sessions.php\n";
echo str_repeat("─", 80) . "\n";
echo "This migration:\n";
echo "  ✓ DELETES existing duplicate 'progress' sessions\n";
echo "  ❌ Does NOT add any constraint to prevent future duplicates\n";
echo "  ❌ Comment says: Can't add constraint because of retakes\n\n";
echo "  BUT THIS IS FLAWED LOGIC!\n";
echo "  You CAN have:\n";
echo "    - 1 'progress' session at a time per user per exam\n";
echo "    - Multiple 'submited' sessions (for retakes)\n";
echo "    - Multiple 'expired' or 'cancelled' sessions\n\n";
echo "  Solution: Add partial unique index\n";
echo "    UNIQUE KEY unique_user_exam_progress (user_id, exam_id)\n";
echo "    WHERE status = 'progress'\n";
echo str_repeat("─", 80) . "\n\n";

// ============================================================================
// WHY SESSION 2 HAS NO SCORE
// ============================================================================

echo "=" . str_repeat("=", 78) . "\n";
echo "WHY SESSION 2 HAS SCORE = 0\n";
echo "=" . str_repeat("=", 78) . "\n\n";

$reasons = [
    1 => [
        "title" => "Session 2 Created with Default total_score = 0",
        "code" => "\$user->examSessions()->create([\n    'total_score' => 0,  // ← Default when created\n    ...\n]);",
    ],
    2 => [
        "title" => "Frontend Only Submits Session 1",
        "explanation" => "Frontend receives first response (Session 1 token). Second\nresponse may be ignored or cached. Only Session 1 is used.",
    ],
    3 => [
        "title" => "Session 2 Stays in 'progress' State",
        "explanation" => "Session 2 never gets submitted because frontend doesn't\nknow about it. Status remains 'progress'.",
    ],
    4 => [
        "title" => "No Auto-Submit of Session 2",
        "explanation" => "autoSubmitExpiredExam() only runs when user logs back in\nand tries to access exam. Not automatic background process.",
    ],
    5 => [
        "title" => "Result: Orphaned Session with Zero Score",
        "explanation" => "Session 2 can persist in database indefinitely with:\n  - status = 'progress'\n  - total_score = 0\n  - Never submitted\n  - Never gets score calculated",
    ],
];

foreach ($reasons as $num => $item) {
    echo "Step {$num}: {$item['title']}\n";
    echo str_repeat("─", 80) . "\n";
    if (isset($item['code'])) {
        echo $item['code'] . "\n\n";
    } else {
        echo $item['explanation'] . "\n\n";
    }
}

// ============================================================================
// QUERY TO VERIFY THIS IS HAPPENING
// ============================================================================

echo "=" . str_repeat("=", 78) . "\n";
echo "VERIFICATION QUERIES\n";
echo "=" . str_repeat("=", 78) . "\n\n";

$queries = [
    "Find users with multiple 'progress' sessions for same exam" => <<<'SQL'
SELECT user_id, exam_id, COUNT(*) as session_count, GROUP_CONCAT(id) as session_ids
FROM exam_sessions
WHERE status = 'progress'
GROUP BY user_id, exam_id
HAVING COUNT(*) > 1
ORDER BY session_count DESC;
SQL,
    
    "Find sessions with zero score in progress state" => <<<'SQL'
SELECT id, user_id, exam_id, session_token, total_score, status, 
       created_at, updated_at
FROM exam_sessions
WHERE total_score = 0 AND status = 'progress'
ORDER BY created_at DESC
LIMIT 20;
SQL,

    "Full detail of duplicate sessions for a specific user-exam" => <<<'SQL'
SELECT e1.id, e1.user_id, e1.exam_id, e1.session_token, 
       e1.total_score, e1.status, e1.started_at, e1.submited_at,
       TIMEDIFF(e1.created_at, LAG(e1.created_at) 
           OVER (PARTITION BY e1.user_id, e1.exam_id 
                 ORDER BY e1.created_at)) as time_diff_from_previous
FROM exam_sessions e1
WHERE (e1.user_id, e1.exam_id) IN (
    SELECT user_id, exam_id FROM exam_sessions 
    WHERE status = 'progress' 
    GROUP BY user_id, exam_id 
    HAVING COUNT(*) > 1
)
ORDER BY e1.user_id, e1.exam_id, e1.created_at;
SQL,
];

foreach ($queries as $title => $query) {
    echo "🔍 {$title}:\n";
    echo str_repeat("─", 80) . "\n";
    echo $query . "\n\n";
}

// ============================================================================
// VISUAL EXPLANATION
// ============================================================================

echo "=" . str_repeat("=", 78) . "\n";
echo "VISUAL: How Duplicate Sessions Happen\n";
echo "=" . str_repeat("=", 78) . "\n\n";

echo "Initial State: No session exists\n";
echo "┌─────────────────────────────────────┐\n";
echo "│ exam_sessions table                 │\n";
echo "├─────────────────────────────────────┤\n";
echo "│ (empty for user_id=5, exam_id=10) │\n";
echo "└─────────────────────────────────────┘\n\n";

echo "Request 1 & 2 arrive simultaneously:\n\n";
echo "Request 1:                          Request 2:\n";
echo "┌──────────────────────────┐       ┌──────────────────────────┐\n";
echo "│ POST /api/exams/10/start │       │ POST /api/exams/10/start │\n";
echo "│                          │       │                          │\n";
echo "│ Check: any session?      │       │ Check: any session?      │\n";
echo "│ → No                     │       │ → No                     │\n";
echo "│                          │       │                          │\n";
echo "│ Create Session ID=100 ✓  │       │ Create Session ID=101 ✓  │\n";
echo "│ token: ABC123...         │       │ token: XYZ789...         │\n";
echo "│ total_score: 0           │       │ total_score: 0           │\n";
echo "└──────────────────────────┘       └──────────────────────────┘\n\n";

echo "End State: Two sessions exist (BUG!):\n";
echo "┌──────────────────────────────────────────────────────┐\n";
echo "│ exam_sessions (for user_id=5, exam_id=10)           │\n";
echo "├──────────────────────────────────────────────────────┤\n";
echo "│ ID=100 | Session 1 | token=ABC123... | score=0 ✓   │\n";
echo "│ ID=101 | Session 2 | token=XYZ789... | score=0 ❌  │\n";
echo "└──────────────────────────────────────────────────────┘\n\n";

echo "Frontend Result:\n";
echo "┌──────────────────────────────────────────────────────┐\n";
echo "│ Only Session 1 token is used (first response)       │\n";
echo "│ Session 2 remains orphaned, never submitted         │\n";
echo "│ → Zero score persists                               │\n";
echo "└──────────────────────────────────────────────────────┘\n\n";

// ============================================================================
// SUMMARY
// ============================================================================

echo "=" . str_repeat("=", 78) . "\n";
echo "SUMMARY\n";
echo "=" . str_repeat("=", 78) . "\n\n";

echo "🐛 THE BUG:\n";
echo "   Race condition in concurrent start() requests allows creation of\n";
echo "   multiple 'progress' sessions for same user-exam combination.\n\n";

echo "🔓 WHY IT HAPPENS:\n";
echo "   1. lockForUpdate() doesn't prevent phantom reads\n";
echo "   2. No UNIQUE database constraint on (user_id, exam_id)\n";
echo "   3. Check-then-act pattern is inherently racy\n\n";

echo "💥 THE SYMPTOM:\n";
echo "   Session 2 appears with total_score = 0 and stays in progress state\n";
echo "   because it's never submitted (frontend only knows about Session 1).\n\n";

echo "✅ THE FIX:\n";
echo "   Add UNIQUE constraint on (user_id, exam_id) WHERE status='progress'\n";
echo "   at database level with proper error handling in controller.\n\n";

?>
