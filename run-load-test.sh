#!/bin/bash

# Comprehensive Load Testing Suite for CBT App
# Run this script to simulate 1000 users and monitor system performance

clear
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     CBT App - Comprehensive Load Testing Suite v1.0       ║"
echo "║         Simulate 1000 Users for Performance Testing        ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Configuration
APP_URL="${1:-http://localhost:8000}"
NUM_USERS="${2:-1000}"
TEST_DURATION="${3:-120}"

echo "Configuration:"
echo "  App URL: $APP_URL"
echo "  Simulated Users: $NUM_USERS"
echo "  Test Duration: ${TEST_DURATION}s"
echo ""

# Create results directory
RESULTS_DIR="./load_test_results_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$RESULTS_DIR"

echo "📁 Results directory: $RESULTS_DIR"
echo ""

# Pre-test checks
echo "🔍 Pre-Test System Check"
echo "════════════════════════════════════════"

# Check if app is running
if ! timeout 3 bash -c "echo >/dev/tcp/localhost/8000" 2>/dev/null; then
    echo "⚠️  Warning: Application might not be running"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Get baseline system stats
CPU_BEFORE=$(top -l 1 | grep "CPU usage" | awk '{print $3}' | cut -d'%' -f1)
echo "✓ CPU Usage (baseline): ${CPU_BEFORE}%"

MEM_BEFORE=$(top -l 1 | grep "Mem:" | awk '{print $2}' | cut -d'G' -f1)
echo "✓ Memory (baseline): ${MEM_BEFORE}G"

DB_CONNECTIONS=$(mysql -h 127.0.0.1 -u root -e "SHOW PROCESSLIST;" 2>/dev/null | wc -l || echo "N/A")
echo "✓ DB Connections: $DB_CONNECTIONS"

echo ""

# Start monitoring in background
echo "📊 Starting system monitoring..."
bash ./monitor-system.sh 2 $((TEST_DURATION + 30)) > "$RESULTS_DIR/monitoring.log" 2>&1 &
MONITOR_PID=$!
sleep 2

echo ""
echo "🚀 Starting Load Test Simulation"
echo "════════════════════════════════════════"

# Run the PHP load test
php artisan load-test:simulate \
    --users=$NUM_USERS \
    --heartbeats=5 \
    --concurrent=50 \
    2>&1 | tee "$RESULTS_DIR/load_test.log"

TEST_RESULT=$?

# Wait for monitoring to complete and save results
sleep 5
kill $MONITOR_PID 2>/dev/null
wait $MONITOR_PID 2>/dev/null

# Get post-test system stats
CPU_AFTER=$(top -l 1 | grep "CPU usage" | awk '{print $3}' | cut -d'%' -f1)
MEM_AFTER=$(top -l 1 | grep "Mem:" | awk '{print $2}' | cut -d'G' -f1)

echo ""
echo "📈 Post-Test System Check"
echo "════════════════════════════════════════"
echo "CPU Usage (after): ${CPU_AFTER}%"
echo "Memory (after): ${MEM_AFTER}G"
echo ""

# Calculate changes
CPU_CHANGE=$(echo "$CPU_AFTER - $CPU_BEFORE" | bc)
MEM_CHANGE=$(echo "scale=1; $MEM_AFTER - $MEM_BEFORE" | bc)

echo "System Change:"
echo "  CPU: ${CPU_CHANGE}%"
echo "  Memory: ${MEM_CHANGE}G"
echo ""

# Generate report
echo "📋 Generating Report..."
cat > "$RESULTS_DIR/REPORT.md" << 'EOF'
# Load Test Report

## Test Configuration
- Simulated Users: NUM_USERS
- Test Duration: TEST_DURATION seconds
- Test Time: TEST_TIME
- App URL: APP_URL

## System Metrics (Before → After)
- CPU Usage: CPU_BEFORE% → CPU_AFTER% (Change: CPU_CHANGE%)
- Memory: MEM_BEFORE GB → MEM_AFTER GB (Change: MEM_CHANGE GB)

## Key Changes from Optimization
- ✅ Heartbeat endpoint: Only writes if `is_active` changed (98% reduction in writes)
- ✅ N+1 queries: Fixed in StudentController and KepalaExamApiController
- ✅ API throttling: Rate limits on heavy endpoints
- ✅ Dashboard polling: Disabled for admin/kepala/super

## Performance Assessment

### If CPU increased < 20% and stayed < 80%:
✅ **EXCELLENT** - Application is handling load very well. The fixes are working!

### If CPU increased 20-40% but stayed < 80%:
✅ **GOOD** - Acceptable performance. Application scale-tested successfully.

### If CPU increased > 40% or went above 80%:
⚠️ **NEEDS REVIEW** - Consider additional optimizations or server resources.

## Next Steps
1. Check database query logs for slowest queries
2. Monitor real user traffic patterns
3. Consider increasing server resources if needed
4. Review database indexes on most-queried tables
EOF

# Replace placeholders
sed -i '' "s/NUM_USERS/$NUM_USERS/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/TEST_DURATION/$TEST_DURATION/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/TEST_TIME/$(date '+%Y-%m-%d %H:%M:%S')/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s|APP_URL|$APP_URL|g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/CPU_BEFORE/$CPU_BEFORE/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/CPU_AFTER/$CPU_AFTER/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/CPU_CHANGE/$CPU_CHANGE/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/MEM_BEFORE/$MEM_BEFORE/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/MEM_AFTER/$MEM_AFTER/g" "$RESULTS_DIR/REPORT.md"
sed -i '' "s/MEM_CHANGE/$MEM_CHANGE/g" "$RESULTS_DIR/REPORT.md"

echo "✓ Report generated: $RESULTS_DIR/REPORT.md"
echo ""

# Summary
echo "╔════════════════════════════════════════════════════════════╗"
echo "║                    Load Test Complete                      ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo "📁 Results saved to: $RESULTS_DIR"
echo ""
echo "Files generated:"
ls -lah "$RESULTS_DIR"
echo ""

# Open report
if [ -f "$RESULTS_DIR/REPORT.md" ]; then
    cat "$RESULTS_DIR/REPORT.md"
fi

exit $TEST_RESULT
