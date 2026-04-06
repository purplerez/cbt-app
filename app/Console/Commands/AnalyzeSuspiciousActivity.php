<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AnalyzeSuspiciousActivity extends Command
{
    protected $signature = 'security:analyze {--days=7 : Number of days to analyze}';
    protected $description = 'Analyze suspicious activities and generate security report';

    public function handle()
    {
        $days = $this->option('days');
        $startDate = now()->subDays($days);

        $this->info("🔐 Security Analysis Report");
        $this->info("Period: Last {$days} days");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Get security alerts
        $alerts = DB::table('security_alerts')
            ->where('created_at', '>=', $startDate)
            ->get();

        if ($alerts->isEmpty()) {
            $this->info("✅ No suspicious activities detected in the past {$days} days");
            return 0;
        }

        // Group by type
        $byType = $alerts->groupBy('alert_type');
        $this->info("\n📊 Alerts by Type:");
        foreach ($byType as $type => $items) {
            $this->line("  • {$type}: " . $items->count() . " alerts");
        }

        // Group by IP
        $byIp = $alerts->groupBy('ip_address');
        $this->info("\n🌐 Top IPs with Suspicious Activity:");
        $topIps = collect($byIp)
            ->map(fn($items) => ['ip' => $items->first()->ip_address, 'count' => $items->count()])
            ->sortByDesc('count')
            ->take(10);

        foreach ($topIps as $item) {
            $this->line("  • {$item['ip']}: {$item['count']} alerts");
        }

        // Group by endpoint
        $byEndpoint = $alerts->groupBy('endpoint');
        $this->info("\n📍 Most Targeted Endpoints:");
        $topEndpoints = collect($byEndpoint)
            ->map(fn($items) => ['endpoint' => $items->first()->endpoint, 'count' => $items->count()])
            ->sortByDesc('count')
            ->take(5);

        foreach ($topEndpoints as $item) {
            $this->line("  • {$item['endpoint']}: {$item['count']} alerts");
        }

        // Check for bulk request patterns
        $this->checkBulkRequestPatterns($startDate);

        // Severity summary
        $this->info("\n⚠️  Severity Summary:");
        $bySeverity = $alerts->groupBy('severity');
        foreach (['critical', 'high', 'medium', 'low'] as $level) {
            $count = $bySeverity->get($level, collect())->count();
            if ($count > 0) {
                $this->line("  • $level: $count alerts");
            }
        }

        // Unresolved alerts
        $unresolved = $alerts->where('is_resolved', false)->count();
        if ($unresolved > 0) {
            $this->warn("\n🚨 {$unresolved} unresolved alerts require attention");
        }

        $this->info("\n✓ Analysis complete");
        return 0;
    }

    protected function checkBulkRequestPatterns(Carbon $startDate)
    {
        // Find IPs with many alerts in short time
        $bulkIps = DB::table('security_alerts')
            ->where('created_at', '>=', $startDate)
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 50')
            ->select('ip_address', DB::raw('COUNT(*) as alert_count'))
            ->get();

        if ($bulkIps->isNotEmpty()) {
            $this->warn("\n⚡ BULK REQUEST PATTERNS DETECTED:");
            foreach ($bulkIps as $item) {
                $this->warn("  • IP {$item->ip_address}: {$item->alert_count} alerts in {$startDate->diffInDays(now())} days");
                $this->warn("    Average: " . round($item->alert_count / $startDate->diffInDays(now()), 1) . " alerts/day");
            }
        }
    }
}
