#!/bin/bash

# Load Testing Script for CBT App
# Simulates 1000 users with heartbeat calls

APP_URL="${1:-http://localhost:8000}"
NUM_USERS="${2:-1000}"
NUM_HEARTBEATS="${3:-5}"
CONCURRENT="${4:-50}"

echo "🚀 CBT App Load Test Suite"
echo "══════════════════════════════════════════"
echo "App URL: $APP_URL"
echo "Number of Users: $NUM_USERS"
echo "Heartbeats per User: $NUM_HEARTBEATS"
echo "Concurrent Requests: $CONCURRENT"
echo ""

# Function to get active user count
get_active_users() {
    mysql -h 127.0.0.1 -u root cbtdb -se "SELECT COUNT(*) FROM users WHERE is_active = 1 AND roles LIKE '%siswa%';" 2>/dev/null || echo "N/A"
}

# Function to get total database queries
get_db_queries() {
    mysql -h 127.0.0.1 -u root cbtdb -se "SHOW STATUS LIKE 'Questions';" | grep -o '[0-9]*$' || echo "N/A"
}

# Function to get CPU usage
get_cpu_usage() {
    top -l 1 | grep "CPU usage" | awk '{print $3}' | cut -d'%' -f1
}

# Function to get memory usage
get_memory_usage() {
    top -l 1 | grep "Mem:" | awk '{print $2}' | cut -d'G' -f1
}

# Create monitoring file
MONITOR_FILE="/tmp/cbt_load_test_monitor.txt"
> $MONITOR_FILE

# Start monitoring in background
monitor_system() {
    while true; do
        {
            echo "[$(date '+%Y-%m-%d %H:%M:%S')]"
            echo "Active Users: $(get_active_users)"
            echo "CPU Usage: $(get_cpu_usage)%"
            echo "Memory: $(get_memory_usage)G"
            echo "─────────────────────────────"
        } >> $MONITOR_FILE
        sleep 2
    done
}

# Start monitoring in background
monitor_system &
MONITOR_PID=$!

# Run PHP load test command
echo "📊 Starting load test simulation..."
php artisan load-test:simulate \
    --users=$NUM_USERS \
    --heartbeats=$NUM_HEARTBEATS \
    --concurrent=$CONCURRENT

# Stop monitoring
kill $MONITOR_PID 2>/dev/null

# Print monitoring results
echo ""
echo "📈 System Monitoring Results:"
echo "══════════════════════════════════════════"
cat $MONITOR_FILE

# Clean up
rm -f $MONITOR_FILE

echo ""
echo "✅ Load test complete!"
