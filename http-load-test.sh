#!/bin/bash

# HTTP-based Load Testing Script
# This script simulates actual HTTP requests to the API

APP_URL="${1:-http://localhost:8000/api}"
NUM_USERS="${2:-100}"
DURATION="${3:-60}"

echo "🌐 HTTP Load Test - CBT App"
echo "══════════════════════════════════════════"
echo "App URL: $APP_URL"
echo "Simulated Users: $NUM_USERS"
echo "Duration: $DURATION seconds"
echo ""

# Test data
declare -a EMAILS
declare -a PASSWORDS
declare -a TOKENS

# Function to generate test data
generate_test_users() {
    echo "📋 Generating test user credentials..."

    # Get real users from database
    mysql -h 127.0.0.1 -u root cbtdb -e 'SELECT email, password FROM users WHERE roles LIKE "%siswa%" LIMIT '"$NUM_USERS"';' > /tmp/users.txt 2>/dev/null

    if [ ! -f /tmp/users.txt ] || [ ! -s /tmp/users.txt ]; then
        echo "⚠️  Could not read from database. Using demo mode."
        # Generate demo users
        for i in $(seq 1 $NUM_USERS); do
            EMAILS[$i]="student$i@test.com"
            PASSWORDS[$i]="password123"
        done
    else
        # Parse user data
        local i=0
        while IFS=$'\t' read -r email password; do
            EMAILS[$i]="$email"
            PASSWORDS[$i]="$password"
            ((i++))
        done < /tmp/users.txt
    fi

    echo "✓ Generated ${#EMAILS[@]} test users"
}

# Function to login user
login_user() {
    local email=$1
    local password=$2

    local response=$(curl -s -X POST "$APP_URL/login" \
        -H "Content-Type: application/json" \
        -d "{\"email\": \"$email\", \"password\": \"$password\"}")

    echo "$response" | grep -o '"token":"[^"]*' | cut -d'"' -f4
}

# Function to heartbeat
send_heartbeat() {
    local token=$1

    curl -s -X POST "$APP_URL/siswa/heartbeat" \
        -H "Authorization: Bearer $token" \
        -H "Content-Type: application/json" | grep -c "OK"
}

# Function to get dashboard stats
fetch_dashboard_stats() {
    local token=$1

    curl -s -X GET "$APP_URL/kepala/dashboard/stats" \
        -H "Authorization: Bearer $token" \
        -H "Content-Type: application/json" | grep -c "success"
}

# Performance tracking
TOTAL_REQUESTS=0
SUCCESSFUL_REQUESTS=0
FAILED_REQUESTS=0
START_TIME=$(date +%s)

# Authenticate users
echo "🔐 Authenticating users..."
for i in $(seq 1 $NUM_USERS); do
    token=$(login_user "${EMAILS[$i]}" "${PASSWORDS[$i]}")
    if [ ! -z "$token" ]; then
        TOKENS[$i]="$token"
        ((SUCCESSFUL_REQUESTS++))
    else
        ((FAILED_REQUESTS++))
    fi

    # Show progress every 50 users
    if [ $((i % 50)) -eq 0 ]; then
        echo "  ✓ Authenticated $i users..."
    fi

    ((TOTAL_REQUESTS++))
done

echo "✓ Authentication complete: ${TOKENS[@]}"
echo ""

# Simulate traffic
echo "💓 Simulating heartbeat traffic for $DURATION seconds..."
HEARTBEAT_COUNT=0
END_TIME=$((START_TIME + DURATION))

while [ $(date +%s) -lt $END_TIME ]; do
    # Randomly select users to send heartbeats
    for i in $(seq 1 $((NUM_USERS > 20 ? 20 : NUM_USERS))); do
        user_idx=$((RANDOM % NUM_USERS + 1))

        if [ ! -z "${TOKENS[$user_idx]}" ]; then
            result=$(send_heartbeat "${TOKENS[$user_idx]}")
            if [ "$result" = "1" ]; then
                ((SUCCESSFUL_REQUESTS++))
            else
                ((FAILED_REQUESTS++))
            fi
            ((HEARTBEAT_COUNT++))
            ((TOTAL_REQUESTS++))
        fi
    done

    # Dashboard stats requests (from some users)
    for i in $(seq 1 5); do
        user_idx=$((RANDOM % NUM_USERS + 1))

        if [ ! -z "${TOKENS[$user_idx]}" ]; then
            result=$(fetch_dashboard_stats "${TOKENS[$user_idx]}")
            if [ "$result" = "1" ]; then
                ((SUCCESSFUL_REQUESTS++))
            else
                ((FAILED_REQUESTS++))
            fi
            ((TOTAL_REQUESTS++))
        fi
    done

    sleep 1
done

CURRENT_TIME=$(date +%s)
ACTUAL_DURATION=$((CURRENT_TIME - START_TIME))

echo "✓ Traffic simulation complete"
echo ""

# Print results
echo "📊 Load Test Results"
echo "══════════════════════════════════════════"
echo "Total Requests: $TOTAL_REQUESTS"
echo "Successful: $SUCCESSFUL_REQUESTS"
echo "Failed: $FAILED_REQUESTS"
success_rate=$(echo "scale=2; $SUCCESSFUL_REQUESTS * 100 / $TOTAL_REQUESTS" | bc)
echo "Success Rate: $success_rate%"
echo "Duration: ${ACTUAL_DURATION}s"
echo "Requests/sec: $(echo "scale=2; $TOTAL_REQUESTS / $ACTUAL_DURATION" | bc)"
echo "Heartbeats sent: $HEARTBEAT_COUNT"
echo ""

# Check system health
echo "💻 System Health Check"
echo "══════════════════════════════════════════"

CPU_USAGE=$(top -l 1 | grep "CPU usage" | awk '{print $3}' | cut -d'%' -f1)
echo "CPU Usage: ${CPU_USAGE}%"

MEMORY=$(top -l 1 | grep "Mem:" | awk '{print $2}' | cut -d'G' -f1)
echo "Memory: ${MEMORY}G"

ACTIVE_USERS=$(mysql -h 127.0.0.1 -u root cbtdb -se "SELECT COUNT(*) FROM users WHERE is_active = 1 AND roles LIKE '%siswa%';" 2>/dev/null || echo "N/A")
echo "Active Users in DB: $ACTIVE_USERS"

QUERIES=$(mysql -h 127.0.0.1 -u root cbtdb -se "SHOW STATUS LIKE 'Questions';" 2>/dev/null | grep -o '[0-9]*$' || echo "N/A")
echo "Database Queries: $QUERIES"

echo ""
if [ $(echo "$success_rate > 95" | bc) -eq 1 ]; then
    echo "✅ Load test PASSED: Success rate > 95%"
else
    echo "⚠️  Load test WARNING: Success rate < 95%"
fi

if [ $(echo "$CPU_USAGE < 80" | bc) -eq 1 ]; then
    echo "✅ CPU usage is acceptable: < 80%"
else
    echo "⚠️  CPU usage is HIGH: > 80%"
fi
