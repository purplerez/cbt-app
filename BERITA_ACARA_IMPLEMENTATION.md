# 📋 Berita Acara Feature Implementation

## Overview

Fitur **Berita Acara** (Official Exam Report) telah berhasil ditambahkan ke sistem ujian. Fitur ini memungkinkan dokumentasi resmi pelaksanaan ujian dengan informasi lengkap tentang peserta, pengawas, kondisi pelaksanaan, dan dapat diekspor ke PDF.

---

## 🎯 **Features Implemented**

### ✅ **Core Features:**
1. **CRUD Berita Acara** - Create, Read, Update, Delete
2. **Auto-generate Nomor BA** - Format: `BA/SCHOOL_CODE/EXAM/YYYY/MM/XXXX`
3. **Multi-level Status Workflow:**
   - `draft` - Berita Acara sedang dibuat
   - `finalized` - Selesai dan siap untuk approval
   - `approved` - Sudah disetujui Kepala Sekolah/Admin
   - `archived` - Diarsipkan untuk histori
4. **Auto-fill Data** - Mengambil data kehadiran dari exam sessions
5. **PDF Export** - Export ke format PDF resmi
6. **Role-based Access:**
   - Admin & Super Admin: Full access
   - Kepala Sekolah: Access untuk sekolahnya sendiri
   - Guru: (Future) View only
7. **Comprehensive Filtering** - By status, exam type, school, date range

### ✅ **Data yang Dicatat:**
- Informasi ujian (tipe, mata pelajaran, tanggal, waktu)
- Data kehadiran peserta (terdaftar, hadir, tidak hadir)
- Daftar pengawas ujian
- Kondisi pelaksanaan (lancar/ada kendala/terganggu)
- Kondisi ruangan dan peralatan
- Kendala/masalah yang terjadi
- Catatan khusus
- Approval history (siapa, kapan)

---

## 📦 **Files Created**

### **Database:**
```
database/migrations/2025_10_10_100000_create_berita_acara_table.php
```

### **Model:**
```
app/Models/BeritaAcara.php
```

### **Controller:**
```
app/Http/Controllers/BeritaAcaraController.php
```

### **Views:**
```
resources/views/berita-acara/
├── index.blade.php    # List view
├── create.blade.php   # Create form
├── edit.blade.php     # Edit form
├── show.blade.php     # Detail view
└── pdf.blade.php      # PDF export template
```

### **Routes:**
Routes ditambahkan di `routes/web.php` untuk 3 role groups:
- `admin.berita-acara.*`
- `kepala.berita-acara.*`
- `super.berita-acara.*`

---

## 🚀 **Installation Steps**

### **Step 1: Install PDF Library**

```bash
composer require barryvdh/laravel-dompdf
```

### **Step 2: Run Database Migration**

```bash
php artisan migrate
```

### **Step 3: Clear Cache (Optional)**

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### **Step 4: Verify Routes**

```bash
php artisan route:list | grep berita-acara
```

Expected output:
```
GET|HEAD   admin/berita-acara .................. admin.berita-acara.index
POST       admin/berita-acara .................. admin.berita-acara.store
GET|HEAD   admin/berita-acara/create ........... admin.berita-acara.create
GET|HEAD   admin/berita-acara/{beritaAcara} .... admin.berita-acara.show
PUT|PATCH  admin/berita-acara/{beritaAcara} .... admin.berita-acara.update
DELETE     admin/berita-acara/{beritaAcara} .... admin.berita-acara.destroy
...
```

---

## 📖 **Usage Guide**

### **1. Create New Berita Acara**

**URL:** `/admin/berita-acara/create` (atau `/kepala/berita-acara/create`)

**Steps:**
1. Pilih **Tipe Ujian** dan **Mata Pelajaran**
2. Pilih **Sekolah** dan **Ruangan** (optional)
3. Isi **Tanggal** dan **Waktu** pelaksanaan
4. Klik **"Isi Otomatis dari Data Ujian"** untuk auto-fill kehadiran
5. Atau isi manual **Jumlah Peserta** (Terdaftar, Hadir, Tidak Hadir)
6. Pilih **Kondisi Pelaksanaan**, **Ruangan**, dan **Peralatan**
7. Isi **Kendala** jika ada
8. Pilih **Pengawas** ujian (multi-select)
9. Tambahkan **Catatan Khusus** jika perlu
10. Klik **"Simpan Berita Acara"**

**Result:** Berita Acara dibuat dengan status `draft` dan nomor BA auto-generated

---

### **2. Edit Berita Acara**

**Catatan:** Hanya Berita Acara dengan status `draft` atau `finalized` yang dapat diedit.

**Steps:**
1. Buka detail Berita Acara
2. Klik tombol **"Edit"**
3. Ubah data yang diperlukan
4. Klik **"Update Berita Acara"**

---

### **3. Workflow Approval**

#### **A. Finalize (Selesaikan BA)**
**Status:** `draft` → `finalized`

Dilakukan oleh pembuat BA untuk menandakan bahwa BA sudah lengkap dan siap untuk approval.

```php
POST /admin/berita-acara/{id}/finalize
```

#### **B. Approve (Setujui BA)**
**Status:** `finalized` → `approved`

Dilakukan oleh Kepala Sekolah atau Admin untuk menyetujui BA secara resmi.

```php
POST /kepala/berita-acara/{id}/approve
```

**Data yang disimpan:**
- `approved_by` - User ID yang menyetujui
- `approved_at` - Timestamp approval

#### **C. Archive (Arsipkan BA)**
**Status:** `approved` → `archived`

Untuk memindahkan BA ke arsip setelah periode tertentu.

```php
POST /admin/berita-acara/{id}/archive
```

---

### **4. Export to PDF**

**URL:** `/admin/berita-acara/{id}/pdf`

**Output:** PDF file dengan format resmi berisi:
- Header sekolah
- Nomor BA
- Informasi ujian lengkap
- Tabel data peserta
- Daftar pengawas
- Kondisi pelaksanaan
- Kolom tanda tangan

**Filename Format:** `Berita_Acara_BA_SCHOOL_EXAM_2025_10_0001.pdf`

---

### **5. Auto-fill Functionality**

Fitur auto-fill mengambil data dari tabel `exam_sessions` dan `preassigned`:

**Endpoint:** `POST /admin/berita-acara/auto-fill`

**Request:**
```json
{
    "exam_type_id": 1,
    "exam_id": 5,
    "school_id": 2
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "jumlah_peserta_hadir": 45,
        "jumlah_peserta_tidak_hadir": 5,
        "jumlah_peserta_terdaftar": 50
    }
}
```

---

## 🔍 **Database Schema**

### **Table: `berita_acara`**

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| exam_type_id | bigint | FK to exam_types |
| exam_id | bigint | FK to exams (nullable) |
| school_id | bigint | FK to schools |
| room_id | bigint | FK to rooms (nullable) |
| nomor_ba | varchar | Auto-generated unique number |
| tanggal_pelaksanaan | date | Exam date |
| waktu_mulai | time | Start time |
| waktu_selesai | time | End time |
| jumlah_peserta_terdaftar | int | Total registered |
| jumlah_peserta_hadir | int | Total present |
| jumlah_peserta_tidak_hadir | int | Total absent |
| pengawas | json | Array of proctor user IDs |
| catatan_khusus | text | Special notes |
| kondisi_pelaksanaan | enum | lancar/ada_kendala/terganggu |
| kendala | text | Problem description |
| kondisi_ruangan | enum | baik/cukup/kurang |
| kondisi_peralatan | enum | baik/cukup/kurang |
| status | enum | draft/finalized/approved/archived |
| ttd_pengawas_1 | text | Signature 1 (future) |
| ttd_pengawas_2 | text | Signature 2 (future) |
| ttd_kepala_sekolah | text | Headmaster signature (future) |
| created_by | bigint | FK to users |
| approved_by | bigint | FK to users (nullable) |
| approved_at | timestamp | Approval timestamp |
| created_at | timestamp | Created timestamp |
| updated_at | timestamp | Updated timestamp |

**Indexes:**
- `nomor_ba` (unique)
- `exam_type_id, school_id, tanggal_pelaksanaan`
- `status, created_at`

---

## 🎨 **Model Methods & Features**

### **Auto-generated Nomor BA**

```php
// Format: BA/SCHOOL_CODE/EXAM/YYYY/MM/XXXX
// Example: BA/SMAN1/UTS/2025/10/0001
BeritaAcara::generateNomorBA($beritaAcara);
```

### **Scopes**

```php
// Filter by status
BeritaAcara::status('approved')->get();

// Filter by school
BeritaAcara::bySchool($schoolId)->get();

// Filter by exam type
BeritaAcara::byExamType($examTypeId)->get();

// Filter by date range
BeritaAcara::dateRange('2025-01-01', '2025-12-31')->get();
```

### **Relationships**

```php
$ba->examType;    // Belongs to ExamType
$ba->exam;        // Belongs to Exam
$ba->school;      // Belongs to School
$ba->room;        // Belongs to Room
$ba->creator;     // Belongs to User (created_by)
$ba->approver;    // Belongs to User (approved_by)
$ba->pengawas_users;  // Collection of User (pengawas)
```

### **Helper Attributes**

```php
$ba->status_badge;           // CSS class for status badge
$ba->status_label;           // Human-readable status
$ba->persentase_kehadiran;   // Attendance percentage
```

### **Permission Checks**

```php
$ba->canBeEdited();    // true if draft or finalized
$ba->canBeApproved();  // true if finalized
```

### **Actions**

```php
$ba->finalize();           // Change to finalized
$ba->approve($userId);     // Change to approved
$ba->archive();            // Change to archived
```

---

## 🔒 **Permissions & Access Control**

### **Role-based Access:**

| Role | Create | Edit | View | Approve | Delete | Export PDF |
|------|--------|------|------|---------|--------|------------|
| Super Admin | ✅ | ✅ | ✅ | ✅ | ✅ (draft) | ✅ |
| Admin | ✅ | ✅ | ✅ | ✅ | ✅ (draft) | ✅ |
| Kepala Sekolah | ✅ | ✅ (own school) | ✅ (own school) | ✅ | ✅ (draft) | ✅ |
| Guru | ❌ | ❌ | ✅ (view only) | ❌ | ❌ | ✅ |
| Siswa | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

### **Middleware Applied:**

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('berita-acara', BeritaAcaraController::class);
});
```

---

## 📊 **Example Use Cases**

### **Use Case 1: Dokumentasi Ujian Tengah Semester**

1. Setelah ujian selesai, admin/kepala sekolah membuat BA
2. Auto-fill data kehadiran dari sistem
3. Tambahkan catatan khusus (misal: "Listrik mati 10 menit")
4. Finalize BA
5. Kepala Sekolah approve BA
6. Export PDF untuk arsip fisik

### **Use Case 2: Laporan Ujian dengan Kendala**

1. Buat BA dengan kondisi pelaksanaan: "Terganggu"
2. Isi kendala: "Koneksi internet terputus selama 30 menit"
3. Kondisi ruangan: "Cukup" (AC tidak berfungsi)
4. Dokumentasi lengkap untuk evaluasi

### **Use Case 3: Audit Trail Pelaksanaan Ujian**

1. Filter BA by date range untuk periode tertentu
2. Export multiple PDF untuk audit
3. Review persentase kehadiran per ujian
4. Identifikasi pattern kendala yang sering terjadi

---

## 🐛 **Troubleshooting**

### **Problem: PDF tidak generate**

**Solution:**
```bash
# Install dompdf
composer require barryvdh/laravel-dompdf

# Clear config
php artisan config:clear

# Check if facade is registered in config/app.php
'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
```

### **Problem: Nomor BA duplicate**

**Solution:**
```sql
-- Check for duplicates
SELECT nomor_ba, COUNT(*) 
FROM berita_acara 
GROUP BY nomor_ba 
HAVING COUNT(*) > 1;

-- Fix migration constraint if needed
ALTER TABLE berita_acara ADD UNIQUE INDEX (nomor_ba);
```

### **Problem: Auto-fill tidak bekerja**

**Solution:**
1. Pastikan exam sessions ada untuk exam tersebut
2. Check console untuk error JavaScript
3. Verify CSRF token valid
4. Check route `berita-acara.autofill` terdaftar

---

## 🔄 **Future Enhancements**

### **Phase 2 Features (Recommended):**

1. **Digital Signature Integration**
   - E-signature capture untuk pengawas
   - QR code verification
   - Blockchain-based authentication

2. **Advanced Reporting**
   - Dashboard statistik BA
   - Grafik trend kehadiran
   - Export ke Excel (multiple BA)

3. **Email Notifications**
   - Auto-send PDF ke kepala sekolah
   - Reminder untuk approval
   - Weekly report summary

4. **Integration with Exam System**
   - Auto-create BA setelah exam selesai
   - Link ke detailed exam results
   - Anomaly detection (unusual attendance patterns)

5. **Version Control**
   - Track changes history
   - Compare versions
   - Rollback capability

6. **Batch Operations**
   - Bulk create BA untuk multiple exams
   - Bulk approve
   - Bulk export

---

## 📝 **API Documentation (Optional)**

If you want to add API endpoints:

```php
// routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('berita-acara', BeritaAcaraController::class);
    Route::post('berita-acara/{id}/finalize', [BeritaAcaraController::class, 'finalize']);
    Route::post('berita-acara/{id}/approve', [BeritaAcaraController::class, 'approve']);
});
```

---

## ✅ **Testing Checklist**

- [ ] Create new Berita Acara
- [ ] Auto-fill functionality works
- [ ] Edit existing BA (draft/finalized)
- [ ] Cannot edit approved/archived BA
- [ ] Finalize workflow works
- [ ] Approve workflow works
- [ ] Archive workflow works
- [ ] Delete draft BA
- [ ] Cannot delete non-draft BA
- [ ] PDF export generates correctly
- [ ] PDF contains all data
- [ ] Filtering works (status, exam type, school, date)
- [ ] Pagination works
- [ ] Role-based access control works
- [ ] Kepala sekolah only sees their school
- [ ] Activity logging works
- [ ] Nomor BA generates uniquely
- [ ] Validation works for all fields

---

## 📄 **License & Credits**

Feature developed as part of the Laravel Exam Management System.

**Developer:** AI Assistant
**Date:** October 10, 2025
**Version:** 1.0.0

---

## 🆘 **Support**

For issues or questions:
1. Check this documentation first
2. Review Laravel logs: `storage/logs/laravel.log`
3. Check activity logs in database
4. Enable debug mode in `.env` for detailed errors

**Happy Coding! 🚀**
