# UX Design: Interface Berita Acara Ruang Ujian

## 📊 Analisis UI Saat Ini

### Pain Points yang Teridentifikasi:
1. **Navigasi Bertingkat**: User harus ke halaman Ujian → klik Berita Acara → pilih ruang → download
2. **Tidak Ada Pratinjau**: Tidak bisa melihat isi berita acara sebelum download
3. **Pemisahan Kelas Tidak Jelas**: Cara memisahkan kelas X dan XI dalam berita acara tidak eksplisit
4. **Akses Tidak Langsung**: Berita acara ada di level Ujian, padahal data utama ada di Ruang

---

## 🎯 Solusi UX yang Diusulkan

### Konsep Utama:
**"Ruang sebagai Entitas Utama" dengan akses langsung ke Berita Acara**

### Struktur Interface Baru:

```
┌─────────────────────────────────────────────────────────────────┐
│  Master Data Ruang                                    [+ Tambah] │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │  📊 Filter & Sorting                                      │  │
│  │  [Semua Ujian ▼]  [Semua Ruang ▼]  [🔍 Cari...]         │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Card View - Ruang 1                          Status: Penuh  ││
│  │ Ujian: Ujian Tengah Semester                                ││
│  │ ─────────────────────────────────────────────────────────── ││
│  │ 👥 Total Peserta: 40/40                                     ││
│  │ • Kelas XI: 20 siswa                                        ││
│  │ • Kelas X: 20 siswa                                         ││
│  │                                                              ││
│  │ [📋 Detail Peserta] [📄 Berita Acara ▼] [✏️ Edit] [🗑️]   ││
│  │                      └─ Download Gabungan                   ││
│  │                      └─ Download Kelas XI                   ││
│  │                      └─ Download Kelas X                    ││
│  └──────────────────────────────────────────────────────────────┘│
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │ Card View - Ruang 2                      Status: Terisi 35  ││
│  │ Ujian: Ujian Tengah Semester                                ││
│  │ ─────────────────────────────────────────────────────────── ││
│  │ 👥 Total Peserta: 35/40                                     ││
│  │ • Kelas XI: 18 siswa                                        ││
│  │ • Kelas X: 17 siswa                                         ││
│  │                                                              ││
│  │ [📋 Detail Peserta] [📄 Berita Acara ▼] [✏️ Edit] [🗑️]   ││
│  └──────────────────────────────────────────────────────────────┘│
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

---

## 📱 Detail Halaman: Berita Acara (Modal/Sidebar)

Ketika user klik "📄 Berita Acara":

```
┌─────────────────────────────────────────────────────────────────┐
│  Pratinjau Berita Acara - Ruang 1                      [✕ Close] │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ 📄 Pilih Format Berita Acara:                           │    │
│  │                                                          │    │
│  │ ○ Berita Acara Lengkap (Kelas X & XI digabung)         │    │
│  │ ○ Berita Acara Terpisah (2 file: Kelas X & Kelas XI)   │    │
│  │ ● Berita Acara Per Kelas (pilih salah satu)            │    │
│  │     [✓] Kelas XI    [✓] Kelas X                        │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │ 👁️ Pratinjau Dokumen                                    │    │
│  │ ─────────────────────────────────────────────────────────    │
│  │                                                          │    │
│  │  BERITA ACARA PELAKSANAAN UJIAN                         │    │
│  │  Ruang: Ruang 1                                         │    │
│  │  Ujian: Ujian Tengah Semester                           │    │
│  │  Tanggal: 10 Oktober 2025                               │    │
│  │                                                          │    │
│  │  DAFTAR HADIR SISWA KELAS XI                            │    │
│  │  ┌────┬──────────────┬───────┬────────────┐            │    │
│  │  │ No │ Nama Siswa   │ Kelas │ Tanda Tgn  │            │    │
│  │  ├────┼──────────────┼───────┼────────────┤            │    │
│  │  │ 1  │ Ahmad Zaki   │ XI A  │            │            │    │
│  │  │ 2  │ Bella Safira │ XI A  │            │            │    │
│  │  │... │ ...          │ ...   │            │            │    │
│  │  └────┴──────────────┴───────┴────────────┘            │    │
│  │                                                          │    │
│  │  DAFTAR HADIR SISWA KELAS X                             │    │
│  │  ┌────┬──────────────┬───────┬────────────┐            │    │
│  │  │ No │ Nama Siswa   │ Kelas │ Tanda Tgn  │            │    │
│  │  └────┴──────────────┴───────┴────────────┘            │    │
│  │                                                          │    │
│  │  Pengawas: ________________                             │    │
│  │  Kepala Sekolah: ________________                       │    │
│  │                                                          │    │
│  └─────────────────────────────────────────────────────────┘    │
│                                                                   │
│  [⬇️ Download PDF]  [🖨️ Print]  [📧 Email]                     │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

---

## 🔄 User Flow yang Disederhanakan

### Flow Lama:
```
Home → Master Data Ujian → Klik "Berita Acara" → 
Pilih Ruang → ??? (tidak jelas cara pisah kelas) → Download
```
**5 langkah, tidak intuitif**

### Flow Baru:
```
Home → Master Data Ruang → Dropdown "Berita Acara" → 
Pilih format → Pratinjau → Download
```
**3 langkah, jelas dan langsung**

---

## 💡 Rekomendasi UX Improvements

### 1. **Visual Hierarchy**
- ✅ Card-based layout untuk ruang (lebih scannable)
- ✅ Informasi penting di depan (jumlah siswa per kelas)
- ✅ Status visual (Penuh, Terisi sebagian, Kosong)

### 2. **Progressive Disclosure**
- ✅ Dropdown untuk berita acara (tidak perlu halaman baru)
- ✅ Modal pratinjau sebelum download
- ✅ Pilihan format yang jelas

### 3. **Efficiency**
- ✅ Batch actions: Download multiple berita acara sekaligus
- ✅ Quick actions di card
- ✅ Filter dan search yang powerful

### 4. **Error Prevention**
- ✅ Disable download jika ruang belum penuh (opsional)
- ✅ Warning jika ada siswa yang belum lengkap datanya
- ✅ Pratinjau sebelum download

### 5. **Flexibility**
- ✅ 3 pilihan format download:
  1. Gabungan (1 file, 2 bagian)
  2. Terpisah (2 file otomatis)
  3. Per kelas (pilih salah satu atau keduanya)

---

## 🎨 Alternative Design: Halaman Ujian dengan Tab

```
┌─────────────────────────────────────────────────────────────────┐
│  Ujian Tengah Semester                                           │
├─────────────────────────────────────────────────────────────────┤
│  [📊 Overview] [👥 Peserta] [🏢 Ruangan] [📄 Berita Acara]      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  Tab: Berita Acara                                               │
│                                                                   │
│  ┌─ Opsi Bulk Download ────────────────────────────────────┐    │
│  │ [✓] Semua Ruangan                                        │    │
│  │ Format: [Terpisah per Kelas ▼]                          │    │
│  │ [⬇️ Download Semua Berita Acara (ZIP)]                  │    │
│  └──────────────────────────────────────────────────────────┘    │
│                                                                   │
│  ┌─ Ruang 1 ────────────────────────────────────────────────┐   │
│  │ 40 peserta (20 Kelas XI, 20 Kelas X)                     │   │
│  │ [👁️ Pratinjau] [⬇️ Download Gabungan]                    │   │
│  │                [⬇️ Download Kelas XI] [⬇️ Download Kelas X] │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                   │
│  ┌─ Ruang 2 ────────────────────────────────────────────────┐   │
│  │ 35 peserta (18 Kelas XI, 17 Kelas X)                     │   │
│  │ [👁️ Pratinjau] [⬇️ Download Gabungan]                    │   │
│  │                [⬇️ Download Kelas XI] [⬇️ Download Kelas X] │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

---

## 🔧 Technical Implementation Notes

### Backend Considerations:
1. **API Endpoints:**
   - `GET /api/rooms/{id}/berita-acara/preview`
   - `GET /api/rooms/{id}/berita-acara/download?format={gabungan|terpisah|kelas-xi|kelas-x}`
   - `POST /api/exams/{id}/berita-acara/bulk-download` (untuk download semua ruang)

2. **PDF Generation:**
   - Template terpisah untuk setiap format
   - Watermark "DRAFT" jika data belum final
   - QR code untuk verifikasi dokumen

3. **Data Structure:**
```json
{
  "room_id": "R001",
  "exam_id": "E001",
  "students": {
    "grade_xi": [...],
    "grade_x": [...]
  },
  "metadata": {
    "exam_date": "2025-10-10",
    "supervisor": "",
    "principal": ""
  }
}
```

---

## ✅ Ringkasan Perbaikan Utama

| Aspek | Sebelum | Sesudah |
|-------|---------|---------|
| **Akses** | 5 klik | 3 klik |
| **Clarity** | Tidak jelas cara pisah kelas | Jelas dengan dropdown/pilihan |
| **Preview** | Tidak ada | Ada pratinjau sebelum download |
| **Flexibility** | Format tetap | 3 pilihan format |
| **Efficiency** | Satu per satu | Bulk download tersedia |
| **Feedback** | Langsung download | Preview → konfirmasi → download |

---

## 🎯 Prioritas Implementasi

### Phase 1 (MVP):
1. ✅ Card-based layout untuk ruang dengan info jumlah siswa per kelas
2. ✅ Dropdown berita acara dengan 3 pilihan format
3. ✅ Download langsung (tanpa preview)

### Phase 2 (Enhancement):
4. ✅ Modal preview sebelum download
5. ✅ Bulk download untuk multiple ruang
6. ✅ Email functionality

### Phase 3 (Advanced):
7. ✅ Template customization
8. ✅ Digital signature
9. ✅ Auto-send via WhatsApp/Email

