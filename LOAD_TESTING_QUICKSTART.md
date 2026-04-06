# 🚀 Load Testing - Simulasi 1000 Users

Panduan lengkap untuk menjalankan load testing dan memverifikasi bahwa aplikasi dapat menangani 1000+ concurrent users.

## Ringkasan Cepat

Aplikasi Anda sudah dioptimasi untuk menangani heavy load. Berikut adalah cara mensimulasikan 1000 users:

### 1️⃣ Simulasi Cepat (50 users - 30 detik)
```bash
php artisan load-test:simulate --users=50 --heartbeats=3 --concurrent=10
```

### 2️⃣ Simulasi Besaran Production (1000 users - 2 menit)
```bash
bash run-load-test.sh http://localhost:8000 1000 120
```

### 3️⃣ Extended Soak Test (700 users - 30 menit)
```bash
bash http-load-test.sh http://localhost:8000 700 1800
```

---

## 📋 Komponen Load Testing

### A. PHP Direct Load Test (Recommended untuk testing pertama)
Melakukan simulation langsung ke database tanpa HTTP overhead.

**Command:**
```bash
php artisan load-test:simulate --users=<num> --heartbeats=<num> --concurrent=<num>
```

**Contoh: Test 1000 users**
```bash
php artisan load-test:simulate --users=1000 --heartbeats=5 --concurrent=50
```

**Hasil yang diharapkan:**
```
✅ Load Test Complete
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
| Total Requests        | 5000    |
| Successful            | 4950    |
| Success Rate          | 99%     |
| Requests/sec          | 1200    |
| Auth Startup Time     | 0.2s    |
```

---

### B. HTTP-Based Load Test (Realistic)
Menjalankan HTTP requests actual ke API endpoints.

**Command:**
```bash
bash http-load-test.sh http://localhost:8000/api <num_users> <duration_seconds>
```

**Contoh: Test 500 users untuk 2 menit**
```bash
bash http-load-test.sh http://localhost:8000/api 500 120
```

---

### C. System Monitoring (Real-time)
Monitor CPU, Memory, Database saat test berjalan.

**Jalankan di terminal terpisah:**
```bash
bash monitor-system.sh 2 300
```

**Output:**
```
Timestamp            | CPU: 45.2% | MEM: 3.2GB | DB:  24 | Active:  500 | QPS: 1250
2026-04-06 13:30:00 | CPU: 46.1% | MEM: 3.3GB | DB:  25 | Active:  500 | QPS: 1280
```

---

### D. Full Comprehensive Test (Recommended)
Menjalankan semua test + monitoring + report generation.

**Command:**
```bash
bash run-load-test.sh http://localhost:8000 1000 120
```

**Output:**
```
╔════════════════════════════════════════════════════════════╗
║                    Load Test Complete                      ║
╚════════════════════════════════════════════════════════════╝

📁 Results saved to: load_test_results_20260406_130000/
```

---

## 🎯 Test Scenarios yang Direkomendasikan

### Scenario 1: Quick Sanity Check ✨
```bash
# Cepat, 1 menit, ideal untuk development testing
php artisan load-test:simulate --users=100 --heartbeats=2 --concurrent=20
```

### Scenario 2: Replication of November 2025 (1000 users) 📈
```bash
# Replicate condition ketika 1000+ siswa mengakses
bash run-load-test.sh http://localhost:8000 1000 120
```

### Scenario 3: Replication of April 1-2 (700 users) 📊
```bash
# Replicate when 700 students accessed and the system was OK
bash run-load-test.sh http://localhost:8000 700 180
```

### Scenario 4: Extended Soak Test (Long Duration) 🐢
```bash
# Test stability over 1 hour
bash http-load-test.sh http://localhost:8000/api 500 3600
```

### Scenario 5: Stress Test (Peak Load) 💥
```bash
# Push to limits with 2000 concurrent users
bash http-load-test.sh http://localhost:8000/api 2000 300
```

---

## 📊 Interpreting Results

### Success Rate Interpretation
| Rate | Status | Action |
|------|--------|--------|
| > 99% | ✅ Excellent | No action needed |
| 95-99% | ✈️ Good | Monitor |
| 80-95% | ⚠️ acceptable | Investigate |
| < 80% | ❌ Poor | Fix required |

### CPU Usage Interpretation
| Usage | Status | Action |
|-------|--------|--------|
| < 40% | ✅ Excellent | No action |
| 40-60% | ✈️ Good | Monitor |
| 60-80% | ⚠️ Acceptable | Watch carefully |
| > 80% | ❌ Critical | Optimize |

### Response Time Interpretation
| Time | Status |
|------|--------|
| < 100ms | ✅ Excellent |
| 100-500ms | ✈️ Good |
| 500-1000ms | ⚠️ Acceptable |
| > 1000ms | ❌ Poor |

---

## 🔍 What to Monitor During Testing

**Terminal 1: Run Load Test**
```bash
bash run-load-test.sh http://localhost:8000 1000 120
```

**Terminal 2: Monitor System**
```bash
watch -n 1 'top -l 1 | grep "CPU usage"'
```

**Terminal 3: Monitor Database**
```bash
watch -n 1 'mysql -u root cbtdb -e "SHOW PROCESSLIST;" | head -10'
```

**Terminal 4: Monitor Active Students**
```bash
watch -n 2 'mysql -u root cbtdb -e "SELECT COUNT(*) as active_siswa FROM users WHERE is_active=1 AND roles LIKE '\''%siswa%'\'';"'
```

---

## 📈 Expected Performance After Optimization

### Before Heartbeat Fix:
- 700 students = 16,800+ DB writes/hour
- CPU: 100% (stuck)
- Success Rate: 40-60%

### After Heartbeat Optimization:
- 700 students = ~280 DB writes/hour (98% reduction! ⚡)
- CPU: < 30%
- Success Rate: > 99%

---

## 🔧 Troubleshooting

### "Connection refused" Error
```bash
# Ensure app is running
php artisan serve

# Or check if port is in use
lsof -i :8000
```

### "MySQL connection failed"
```bash
# Check MySQL status
mysql -u root -e "SELECT 1"

# Check credentials in .env
cat .env | grep DB_
```

### "No siswa users found"
```bash
# Check how many siswa users exist
mysql -u root cbtdb -e "SELECT COUNT(*) FROM users WHERE roles LIKE '%siswa%';"

# Create test users if needed
php artisan db:seed
```

### Low Success Rate
```bash
# Check error logs
tail -f storage/logs/laravel.log

# Try reducing concurrent rate
php artisan load-test:simulate --users=100 --heartbeats=2 --concurrent=5
```

---

## 📁 Understanding Test Results

Results directory structure:
```
load_test_results_20260406_130000/
├── REPORT.md                 # Human-readable summary
├── load_test.log            # Detailed test output
├── monitoring.log           # System metrics CSV
│   └── CPU, Memory, DB stats
```

**To view report:**
```bash
cat load_test_results_20260406_130000/REPORT.md
```

---

## ✅ Verification Checklist

After running load test for 1000 users, verify:

- [ ] Success rate > 95%
- [ ] CPU usage stayed < 80% throughout
- [ ] Memory didn't run out
- [ ] Database connections < 100
- [ ] Response time < 500ms average
- [ ] No application errors in logs
- [ ] Application still accessible after test
- [ ] Dashboard loads normally
- [ ] Student exam page works

---

## 🚀 Next Steps

1. **Run baseline test:**
   ```bash
   bash run-load-test.sh http://localhost:8000 1000 120
   ```

2. **Review the report:**
   ```bash
   cat load_test_results_*/REPORT.md
   ```

3. **Monitor metrics from report** and compare with benchmarks

4. **If everything passes:** Deploy to production with confidence! ✅

5. **If issues found:** Review bottleneck and apply additional optimizations

---

## 📞 Support

For issues or questions about load testing:

1. Check `LOAD_TESTING_GUIDE.md` for detailed documentation
2. Review application logs: `tail -f storage/logs/laravel.log`
3. Check database performance: `SHOW FULL PROCESSLIST;`
4. Profile slow queries: `SHOW SLOW_LOG;`

---

**Last Updated:** April 6, 2026  
**Status:** Ready for Production Testing  
**Load Test Suite Version:** 1.0
