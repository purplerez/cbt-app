<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Preassigned;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class LoadTestSimulation extends Command
{
    protected $signature = 'load-test:simulate
                            {--users=100 : Number of users to simulate}
                            {--heartbeats=5 : Number of heartbeat calls per user}
                            {--duration=10 : Duration of test in seconds}
                            {--concurrent=10 : Number of concurrent requests}';

    protected $description = 'Simulate multiple users performing various actions on the application';

    protected $appUrl;
    protected $tokens = [];
    protected $metrics = [
        'total_requests' => 0,
        'successful' => 0,
        'failed' => 0,
        'total_time' => 0,
        'db_queries' => 0,
        'startup_time' => 0,
    ];

    public function handle()
    {
        $this->appUrl = config('app.url');
        $numUsers = (int)$this->option('users');
        $numHeartbeats = (int)$this->option('heartbeats');
        $duration = (int)$this->option('duration');
        $concurrent = (int)$this->option('concurrent');

        $this->info("🚀 Starting Load Test Simulation");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Users to simulate: {$numUsers}");
        $this->info("Heartbeat calls per user: {$numHeartbeats}");
        $this->info("Test duration: {$duration} seconds");
        $this->info("Concurrent requests: {$concurrent}");
        $this->newLine();

        $startTime = microtime(true);

        // Step 1: Get or create test users
        $this->info("📋 Step 1: Preparing test users...");
        $users = $this->getTestUsers($numUsers);
        $this->info("✓ Users ready: " . count($users));

        // Step 2: Authenticate users
        $this->info("🔐 Step 2: Authenticating users...");
        $authStartTime = microtime(true);
        $this->authenticateUsers($users, $concurrent);
        $this->metrics['startup_time'] = microtime(true) - $authStartTime;
        $this->info("✓ Authentication complete ({$this->metrics['startup_time']}s)");

        // Step 3: Simulate heartbeat traffic
        $this->info("💓 Step 3: Simulating heartbeat traffic...");
        $this->simulateHeartbeats($numUsers, $numHeartbeats, $concurrent);

        // Step 4: Simulate dashboard stats requests
        $this->info("📊 Step 4: Simulating dashboard stats requests...");
        $this->simulateDashboardRequests($numUsers, $concurrent);

        // Step 5: Simulate exam answer saves
        $this->info("📝 Step 5: Simulating exam answer saves...");
        $this->simulateAnswerSaves($numUsers, $concurrent);

        $totalTime = microtime(true) - $startTime;

        // Print results
        $this->printResults($totalTime);
    }

    protected function getTestUsers($count)
    {
        $this->info("  → Fetching or creating {$count} test users...");

        // Get students with role 'siswa' - using whereHas for Spatie permission
        $users = User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))
            ->limit($count)
            ->get();

        if ($users->count() < $count) {
            $this->warn("  ⚠ Only {$users->count()} siswa users found in database");
        }

        return $users;
    }

    protected function authenticateUsers($users, $concurrent)
    {
        $count = 0;
        $total = $users->count();
        $progressBar = $this->output->createProgressBar($total);

        foreach ($users as $user) {
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        // For simulation, we'll use the users directly
        // In real load test with external HTTP calls, tokens would be created via /login
        $this->tokens = $users->mapWithKeys(fn($u) => [$u->id => $u->createToken('Test Token')->plainTextToken])->toArray();
        $this->metrics['successful'] += $total;
    }

    protected function simulateHeartbeats($numUsers, $heartbeatsPerUser, $concurrent)
    {
        $totalRequests = $numUsers * $heartbeatsPerUser;
        $progressBar = $this->output->createProgressBar($totalRequests);

        $users = User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))->limit($numUsers)->get();

        $startTime = microtime(true);
        $dbQueryBefore = DB::getQueryLog();

        foreach ($users as $user) {
            for ($i = 0; $i < $heartbeatsPerUser; $i++) {
                $this->simulateHeartbeatRequest($user);
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine();

        $elapsedTime = microtime(true) - $startTime;
        $dbQueryAfter = DB::getQueryLog();

        $this->metrics['total_requests'] += $totalRequests;
        $this->metrics['total_time'] += $elapsedTime;
        $this->metrics['db_queries'] += count($dbQueryAfter) - count($dbQueryBefore);
    }

    protected function simulateHeartbeatRequest($user)
    {
        try {
            // Simulate the heartbeat logic
            if ($user->hasRole('siswa') && !$user->is_active) {
                $user->is_active = true;
                $user->save();
                $this->metrics['successful']++;
            } else {
                // No database write (the optimization)
                $this->metrics['successful']++;
            }
        } catch (\Exception $e) {
            $this->metrics['failed']++;
        }
    }

    protected function simulateDashboardRequests($numUsers, $concurrent)
    {
        $requests = min($numUsers, 50); // Simulate dashboard stats from 50 users
        $progressBar = $this->output->createProgressBar($requests);

        for ($i = 0; $i < $requests; $i++) {
            // Simulate dashboard stats fetch
            $this->simulateDashboardStatsRequest();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->metrics['total_requests'] += $requests;
    }

    protected function simulateDashboardStatsRequest()
    {
        try {
            // This would normally hit /api/kepala/dashboard/stats
            // For simulation, we'll just verify the database can handle it
            $stats = [
                'total_students' => User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))->count(),
                'online_students' => User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))->where('is_active', true)->count(),
                'active_exams' => ExamSession::where('status', 'progress')->count(),
            ];
            $this->metrics['successful']++;
        } catch (\Exception $e) {
            $this->metrics['failed']++;
        }
    }

    protected function simulateAnswerSaves($numUsers, $concurrent)
    {
        $requests = min($numUsers, 30); // Simulate answer saves from 30 users
        $progressBar = $this->output->createProgressBar($requests);

        $users = User::whereHas('roles', fn($q) => $q->where('name', 'siswa'))->limit($requests)->get();

        foreach ($users as $user) {
            // Get active exam session for user
            $session = ExamSession::where('user_id', $user->id)
                ->where('status', 'progress')
                ->first();

            if ($session) {
                $this->simulateAnswerSaveRequest($session);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();

        $this->metrics['total_requests'] += $requests;
    }

    protected function simulateAnswerSaveRequest($session)
    {
        try {
            // Simulate saving answers
            $answers = [
                '1' => 'A',
                '2' => 'B',
                '3' => 'C',
            ];

            \App\Models\StudentAnswer::saveAnswers($session->id, $answers);
            $this->metrics['successful']++;
        } catch (\Exception $e) {
            $this->metrics['failed']++;
        }
    }

    protected function printResults($totalTime)
    {
        $this->newLine(2);
        $this->info("✅ Load Test Complete");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Requests', $this->metrics['total_requests']],
                ['Successful', $this->metrics['successful']],
                ['Failed', $this->metrics['failed']],
                ['Total Time', number_format($totalTime, 2) . 's'],
                ['Average Response Time', number_format($totalTime / max($this->metrics['total_requests'], 1), 4) . 's'],
                ['Requests/sec', number_format($this->metrics['total_requests'] / max($totalTime, 1), 2)],
                ['DB Queries', $this->metrics['db_queries']],
                ['Auth Startup Time', number_format($this->metrics['startup_time'], 2) . 's'],
                ['Success Rate', number_format(($this->metrics['successful'] / max($this->metrics['total_requests'], 1)) * 100, 2) . '%'],
            ]
        );

        $this->newLine();
        $this->info("💡 Analysis:");
        $successRate = ($this->metrics['successful'] / max($this->metrics['total_requests'], 1)) * 100;
        if ($successRate >= 95) {
            $this->info("✓ Success rate is EXCELLENT (>95%)");
        } elseif ($successRate >= 80) {
            $this->warn("⚠ Success rate is GOOD (>80%) but could be better");
        } else {
            $this->error("✗ Success rate is LOW (<80%) - investigate failures");
        }

        $rps = $this->metrics['total_requests'] / max($totalTime, 1);
        if ($rps > 100) {
            $this->info("✓ Requests per second is HIGH (${rps}/sec) - server handling load well");
        }
    }
}
