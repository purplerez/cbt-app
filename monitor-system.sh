#!/bin/bash

# System Monitoring Script for Load Testing
# Monitors CPU, Memory, Database performance

INTERVAL="${1:-5}"  # Update interval in seconds
DURATION="${2:-300}" # Total duration in seconds
OUTPUT_FILE="/tmp/cbt_load_test_metrics_$(date +%s).csv"

echo "📊 System Monitoring Started"
echo "Interval: ${INTERVAL}s, Duration: ${DURATION}s"
echo "Output file: $OUTPUT_FILE"
echo ""

# Initialize CSV
echo "Timestamp,CPU_User%,CPU_Sys%,CPU_Idle%,Memory_Used_GB,Memory_Free_GB,DB_Connections,Active_Students,Queries_Per_Sec" > "$OUTPUT_FILE"

# Monitoring loop
ELAPSED=0
while [ $ELAPSED -lt $DURATION ]; do
    TIMESTAMP=$(date '+%Y-%m-%d %H:%M:%S')

    # Get CPU stats
    CPU_STATS=$(top -l 1 | grep "CPU usage" | sed 's/.*CPU usage: //' | sed 's/user, //' | sed 's/sys, //' | sed 's/idle//')
    CPU_USER=$(echo "$CPU_STATS" | awk '{print $1}')
    CPU_SYS=$(echo "$CPU_STATS" | awk '{print $2}')
    CPU_IDLE=$(echo "$CPU_STATS" | awk '{print $3}' | cut -d'%' -f1)

    # Get Memory stats
    MEM_STATS=$(vm_stat | grep -E "Pages free|Pages active" | awk '{print $3}' | tr -d '.')
    MEM_USED=$(echo "scale=2; $(vm_stat | grep 'Pages active' | awk '{print $3}' | tr -d '.') * 4096 / 1024 / 1024 / 1024" | bc)
    MEM_FREE=$(echo "scale=2; $(vm_stat | grep 'Pages free' | awk '{print $3}' | tr -d '.') * 4096 / 1024 / 1024 / 1024" | bc)

    # Get Database stats
    DB_CONNECTIONS=$(mysql -h 127.0.0.1 -u root -e "SHOW PROCESSLIST;" 2>/dev/null | wc -l || echo "N/A")
    ACTIVE_STUDENTS=$(mysql -h 127.0.0.1 -u root cbtdb -se "SELECT COUNT(*) FROM users WHERE is_active = 1 AND roles LIKE '%siswa%';" 2>/dev/null || echo "N/A")

    # Get queries
    QUERIES=$(mysql -h 127.0.0.1 -u root -e "SHOW STATUS LIKE 'Questions';" 2>/dev/null | tail -1 | awk '{print $2}' || echo "N/A")

    # Append to CSV
    echo "$TIMESTAMP,$CPU_USER,$CPU_SYS,$CPU_IDLE,$MEM_USED,$MEM_FREE,$DB_CONNECTIONS,$ACTIVE_STUDENTS,$QUERIES" >> "$OUTPUT_FILE"

    # Print to console
    printf "%-20s | CPU: %5.1f%% | MEM: %3.1fGB | DB: %3d | Active: %4s | QPS: %s\n" \
        "$TIMESTAMP" "$CPU_IDLE" "$MEM_USED" "$DB_CONNECTIONS" "$ACTIVE_STUDENTS" "$QUERIES"

    sleep $INTERVAL
    ELAPSED=$((ELAPSED + INTERVAL))
done

echo ""
echo "✅ Monitoring complete"
echo "Results saved to: $OUTPUT_FILE"
echo ""

# Print summary statistics
echo "📈 Summary Statistics"
echo "════════════════════════════════════"

# CPU Stats
echo "CPU Usage:"
awk -F',' 'NR>1 {
    cpu_user += $2; cpu_sys += $3; cpu_idle += $4; count++
}
END {
    if (count > 0) {
        printf "  Average User: %.1f%%\n", cpu_user/count
        printf "  Average System: %.1f%%\n", cpu_sys/count
        printf "  Average Idle: %.1f%%\n", cpu_idle/count
    }
}' "$OUTPUT_FILE"

# Memory Stats
echo "Memory Usage:"
awk -F',' 'NR>1 {
    mem_used += $5; mem_free += $6; count++
}
END {
    if (count > 0) {
        printf "  Average Used: %.2f GB\n", mem_used/count
        printf "  Average Free: %.2f GB\n", mem_free/count
    }
}' "$OUTPUT_FILE"

# Database Stats
echo "Database:"
awk -F',' 'NR>1 {
    if (NR == 2) min_conn = $7; max_conn = $7
    if ($7 != "N/A") {
        if ($7 < min_conn) min_conn = $7
        if ($7 > max_conn) max_conn = $7
        avg_conn += $7
    }
    count++
}
END {
    if (count > 0) {
        printf "  Min connections: %d\n", min_conn
        printf "  Max connections: %d\n", max_conn
        printf "  Avg connections: %.1f\n", avg_conn/count
    }
}' "$OUTPUT_FILE"

echo ""
echo "💡 Recommendations:"
echo "  - CPU < 60%: Good performance"
echo "  - CPU 60-80%: Acceptable"
echo "  - CPU > 80%: Performance degradation"
echo "  - Check database logs if queries spike"
