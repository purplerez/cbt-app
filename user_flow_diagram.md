# 🔄 User Flow Diagram: Berita Acara System

## 📊 Current Flow (Problem)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                          FLOW YANG ADA SEKARANG                         │
└─────────────────────────────────────────────────────────────────────────┘

    START
      │
      ▼
┌─────────────────┐
│  Home / Login   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  Pilih Menu Ujian       │
│  (Navigation)           │
└───────────┬─────────────┘
            │
            ▼
┌──────────────────────────────┐
│  Master Data Ujian           │
│  ┌────────────────────────┐  │
│  │ 1. UTS                 │  │
│  │ 2. UAS                 │  │
│  │ 3. Ujian Nasional      │  │
│  └────────────────────────┘  │
└──────────────┬───────────────┘
               │
               ▼
┌────────────────────────────────┐
│  Klik "Berita Acara" di UTS    │
│  [Button di Action Column]     │
└────────────┬───────────────────┘
             │
             ▼
┌──────────────────────────────────────────┐
│  Halaman Berita Acara (?)                │
│  ❌ Tidak jelas struktur nya              │
│  ❌ Bagaimana cara pisah kelas X dan XI?  │
│  ❌ Apakah pilih ruang dulu?              │
└───────────────┬──────────────────────────┘
                │
                ▼
        ❓ CONFUSION ❓
                │
                ▼
          Download (?)

TOTAL: 5+ Clicks, Tidak Jelas, Prone to Error

────────────────────────────────────────────────────────────────────────────


PAIN POINTS:
❌ 1. Navigasi terlalu dalam (nested)
❌ 2. Berita acara terpisah dari data ruang
❌ 3. Tidak jelas cara memisahkan kelas
❌ 4. Tidak ada preview
❌ 5. User harus ingat-ingat flow
```

---

## ✅ Proposed Flow 1: Direct Download (Fast Path)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       FLOW BARU - QUICK ACTION                          │
└─────────────────────────────────────────────────────────────────────────┘

    START
      │
      ▼
┌─────────────────┐
│  Home / Login   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  Master Data Ruang      │  ◄─── LANGSUNG KE RUANG, bukan ke Ujian
└───────────┬─────────────┘
            │
            ▼
┌────────────────────────────────────────────────────────────────┐
│  Room Card View                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ 📍 Ruang 1                              Status: Penuh    │   │
│  │ Ujian: UTS                                               │   │
│  │ ─────────────────────────────────────────────────────   │   │
│  │ 👥 Total: 40 | Kelas XI: 20 | Kelas X: 20              │   │
│  │                                                          │   │
│  │ [Detail] [📄 Berita Acara ▼] [Edit] [Delete]           │   │
│  │            │                                             │   │
│  │            └──► HOVER/CLICK                             │   │
│  └─────────────────────────────────────────────────────────┘   │
└────────────────────────────────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────┐
│  Dropdown Menu Muncul                   │
│  ┌────────────────────────────────┐     │
│  │ 👁️  Pratinjau & Download       │     │
│  │ ─────────────────────────────  │     │
│  │ ⬇️  Download Gabungan          │  ◄──── USER KLIK INI
│  │ ⬇️  Download Kelas XI          │     │
│  │ ⬇️  Download Kelas X           │     │
│  └────────────────────────────────┘     │
└───────────────┬─────────────────────────┘
                │
                ▼
        ✅ DOWNLOAD PDF
        (Langsung dapat file)

TOTAL: 3 Clicks, Jelas, Efficient

────────────────────────────────────────────────────────────────────────────

BENEFITS:
✅ 1. Hanya 3 klik
✅ 2. Akses langsung dari ruang
✅ 3. Jelas ada 3 pilihan format
✅ 4. Quick action untuk expert users
✅ 5. Minimal cognitive load
```

---

## ✅ Proposed Flow 2: With Preview (Safe Path)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    FLOW BARU - WITH PREVIEW (SAFE)                      │
└─────────────────────────────────────────────────────────────────────────┘

    START
      │
      ▼
┌─────────────────┐
│  Home / Login   │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  Master Data Ruang      │
└───────────┬─────────────┘
            │
            ▼
┌────────────────────────────────────────────────────────────────┐
│  Room Card - Klik "Pratinjau & Download"                       │
│  [📄 Berita Acara ▼] → [👁️ Pratinjau & Download]             │
└────────────────────┬───────────────────────────────────────────┘
                     │
                     ▼
┌──────────────────────────────────────────────────────────────────────┐
│  MODAL PREVIEW                                                       │
│  ┌────────────────────────────────────────────────────────────────┐ │
│  │  Pratinjau Berita Acara - Ruang 1                      [✕]     │ │
│  ├────────────────────────────────────────────────────────────────┤ │
│  │                                                                 │ │
│  │  📄 Pilih Format:                                              │ │
│  │  ○ Gabungan (Kelas X & XI dalam 1 file)                       │ │
│  │  ○ Terpisah (2 file otomatis)                                 │ │
│  │  ● Per Kelas (pilih salah satu)                               │ │
│  │     ☑️ Kelas XI    ☑️ Kelas X                                 │ │
│  │                                                                 │ │
│  │  ┌─────────────────────────────────────────────────────────┐  │ │
│  │  │ 👁️ PREVIEW DOKUMEN                                      │  │ │
│  │  │                                                          │  │ │
│  │  │  BERITA ACARA PELAKSANAAN UJIAN                         │  │ │
│  │  │  Ruang: Ruang 1                                         │  │ │
│  │  │  Ujian: UTS                                             │  │ │
│  │  │                                                          │  │ │
│  │  │  DAFTAR SISWA KELAS XI                                  │  │ │
│  │  │  [Table preview...]                                     │  │ │
│  │  │                                                          │  │ │
│  │  │  DAFTAR SISWA KELAS X                                   │  │ │
│  │  │  [Table preview...]                                     │  │ │
│  │  └─────────────────────────────────────────────────────────┘  │ │
│  │                                                                 │ │
│  │  [📧 Email]  [🖨️ Print]  [⬇️ Download PDF]                   │ │
│  │                                  └──► USER KLIK INI            │ │
│  └─────────────────────────────────────────────────────────────────┘ │
└──────────────────────────┬───────────────────────────────────────────┘
                           │
                           ▼
                   ✅ DOWNLOAD PDF
                   (Sudah validate dulu)

TOTAL: 4 Clicks, Very Safe, Clear

────────────────────────────────────────────────────────────────────────────

BENEFITS:
✅ 1. User bisa cek dulu sebelum download
✅ 2. Error prevention (validate data)
✅ 3. Flexibility - bisa pilih format dulu
✅ 4. Multiple actions (email, print, download)
✅ 5. Better for first-time users
```

---

## 🔄 Flow Comparison Diagram

```
┌───────────────────────────────────────────────────────────────────────────┐
│                        PERBANDINGAN 3 FLOW                                │
└───────────────────────────────────────────────────────────────────────────┘

CURRENT FLOW (OLD):
┌──────┐   ┌──────┐   ┌─────────┐   ┌────────┐   ┌───────┐   ┌──────────┐
│ Home │──▶│ Ujian│──▶│ Berita  │──▶│ Pilih  │──▶│ ???   │──▶│ Download │
│      │   │      │   │ Acara   │   │ Ruang? │   │       │   │          │
└──────┘   └──────┘   └─────────┘   └────────┘   └───────┘   └──────────┘
  Step 1     Step 2      Step 3       Step 4       Step 5       Step 6+
  
  Issues: ❌ Too many steps ❌ Unclear ❌ No preview

────────────────────────────────────────────────────────────────────────────

PROPOSED FLOW 1 (FAST):
┌──────┐   ┌──────┐   ┌──────────┐   ┌──────────┐
│ Home │──▶│ Ruang│──▶│ Dropdown │──▶│ Download │
│      │   │      │   │ Menu     │   │ (direct) │
└──────┘   └──────┘   └──────────┘   └──────────┘
  Step 1     Step 2      Step 3        Complete!
  
  Benefits: ✅ Only 3 steps ✅ Clear ✅ Fast

────────────────────────────────────────────────────────────────────────────

PROPOSED FLOW 2 (SAFE):
┌──────┐   ┌──────┐   ┌─────────┐   ┌──────────┐   ┌──────────┐
│ Home │──▶│ Ruang│──▶│ Preview │──▶│ Validate │──▶│ Download │
│      │   │      │   │ Modal   │   │ & Choose │   │          │
└──────┘   └──────┘   └─────────┘   └──────────┘   └──────────┘
  Step 1     Step 2      Step 3        Step 4        Complete!
  
  Benefits: ✅ 4 steps ✅ Preview ✅ Safe ✅ Flexible

────────────────────────────────────────────────────────────────────────────

CONCLUSION:
- Current: 6+ steps, unclear, error-prone
- Proposed 1: 3 steps, clear, for expert users
- Proposed 2: 4 steps, very clear, for all users

RECOMMENDATION: Implement BOTH paths
- Power users use Flow 1 (dropdown direct download)
- Careful users use Flow 2 (preview first)
```

---

## 🎯 Decision Tree: Which Format to Download?

```
┌────────────────────────────────────────────────────────────┐
│         USER DECISION: Format Berita Acara                 │
└────────────────────────────────────────────────────────────┘

                    START
                      │
                      ▼
            ┌─────────────────────┐
            │  Apakah butuh       │
            │  keduanya (X & XI)? │
            └──────┬─────┬────────┘
                   │     │
            YA ◄───┘     └───► TIDAK
            │                  │
            ▼                  ▼
  ┌─────────────────────┐   ┌──────────────────────┐
  │ Apakah ingin dalam  │   │ Kelas mana?          │
  │ 1 file atau 2 file? │   │                      │
  └──────┬─────┬────────┘   └───┬──────────┬───────┘
         │     │                 │          │
    1 FILE   2 FILE         KELAS XI    KELAS X
         │     │                 │          │
         ▼     ▼                 ▼          ▼
  ┌─────────┐ ┌──────────┐  ┌────────┐ ┌────────┐
  │Download │ │Download  │  │Download│ │Download│
  │Gabungan │ │Terpisah  │  │Kelas XI│ │Kelas X │
  │         │ │(2 files) │  │        │ │        │
  └─────────┘ └──────────┘  └────────┘ └────────┘
       │            │             │          │
       └────────────┴─────────────┴──────────┘
                      │
                      ▼
                    DONE!

────────────────────────────────────────────────────────────

USE CASES:

1. Download Gabungan:
   ✓ Untuk diprint sekaligus
   ✓ Untuk diarsipkan dalam 1 file
   ✓ Untuk keperluan internal sekolah

2. Download Terpisah:
   ✓ Untuk dibagikan ke wali kelas berbeda
   ✓ Untuk sistem filing terpisah
   ✓ Untuk distribusi via WhatsApp/Email

3. Download Kelas XI saja:
   ✓ Wali kelas XI butuh cepat
   ✓ Ada revisi hanya untuk kelas XI
   ✓ Kelas X belum final

4. Download Kelas X saja:
   ✓ Wali kelas X butuh cepat
   ✓ Ada revisi hanya untuk kelas X
   ✓ Kelas XI belum final
```

---

## 📱 Responsive Flow (Mobile)

```
┌────────────────────────────────────────────────┐
│         MOBILE USER FLOW                       │
└────────────────────────────────────────────────┘

DESKTOP:
┌─────────────────────────┐
│ Hover Button            │
│ ▼                       │
│ Dropdown Muncul         │
│ ▼                       │
│ Click Option            │
└─────────────────────────┘

MOBILE:
┌─────────────────────────┐
│ Tap Button              │
│ ▼                       │
│ Bottom Sheet Slide Up   │  ◄─── Lebih mobile-friendly
│ ▼                       │
│ Tap Option              │
└─────────────────────────┘

                    │
                    ▼
┌────────────────────────────────────┐
│  Bottom Sheet (Mobile)             │
│  ┌──────────────────────────────┐  │
│  │                              │  │
│  │  Berita Acara - Ruang 1      │  │
│  │  ━━━━━━━━━━━━━━━━━━━━━━━━━  │  │
│  │                              │  │
│  │  👁️ Pratinjau & Download     │  │
│  │                              │  │
│  │  ⬇️ Download Gabungan        │  │
│  │                              │  │
│  │  ⬇️ Download Kelas XI        │  │
│  │                              │  │
│  │  ⬇️ Download Kelas X         │  │
│  │                              │  │
│  │  [Cancel]                    │  │
│  │                              │  │
│  └──────────────────────────────┘  │
└────────────────────────────────────┘
```

---

## 🔐 Error Prevention Flow

```
┌────────────────────────────────────────────────┐
│         ERROR PREVENTION CHECKS                │
└────────────────────────────────────────────────┘

User: Klik "Berita Acara"
  │
  ▼
┌─────────────────────────────────────────────┐
│ CHECK 1: Apakah ruang sudah ada siswa?      │
└────────┬───────────────────────┬────────────┘
         │                       │
        TIDAK                   YA
         │                       │
         ▼                       ▼
┌──────────────────┐   ┌────────────────────────┐
│ ⚠️ Warning:      │   │ CHECK 2: Apakah data   │
│ "Ruang masih     │   │ siswa lengkap?         │
│ kosong"          │   └──────┬──────────┬──────┘
│                  │          │          │
│ [OK]             │        TIDAK       YA
└──────────────────┘          │          │
                              ▼          ▼
                    ┌──────────────┐  ┌──────────────┐
                    │ ⚠️ Warning:  │  │ ✅ PROCEED   │
                    │ "Ada siswa   │  │ Show options │
                    │ tanpa data"  │  └──────────────┘
                    │              │
                    │ [Continue]   │
                    │ [Cancel]     │
                    └──────────────┘

────────────────────────────────────────────────

ERROR MESSAGES:

❌ "Ruang masih kosong. Tambahkan siswa terlebih dahulu."
⚠️  "Ruang belum penuh (35/40). Download tetap dilanjutkan?"
⚠️  "Ada 2 siswa dengan data tidak lengkap. Lanjutkan?"
✅ "Data lengkap dan siap diunduh!"
```

---

## 📊 Analytics & Metrics

```
METRICS TO TRACK:

1. Success Rate:
   - % user yang berhasil download dalam < 5 detik
   - Target: > 95%

2. Error Rate:
   - % user yang confused atau cancel
   - Target: < 5%

3. Format Preference:
   - Gabungan: ___%
   - Terpisah: ___%
   - Per Kelas: ___%

4. Preview Usage:
   - % user yang pakai preview vs direct download
   - Insight: Seberapa confidence users dengan data

5. Time to Complete:
   - Average time dari landing → download
   - Target: < 30 seconds

6. User Satisfaction:
   - Post-download survey
   - Target: > 4.5/5.0
```

---

Created: 10 Oktober 2025
Version: 1.0
Last Updated: -
