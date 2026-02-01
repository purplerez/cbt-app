# QUICK START - Fitur Cetak Kartu Peserta

## ✅ Implementasi Selesai

Fitur untuk mencetak kartu peserta ujian telah berhasil diimplementasikan dengan spesifikasi berikut:

---

## 📋 Daftar Perubahan

### 1️⃣ **View Layer** 
- ✅ File: `resources/views/kepala/view_examglobal.blade.php`
- ✅ Tombol "Cetak Kartu Peserta" ditambahkan pada setiap baris ujian

### 2️⃣ **Routing** 
- ✅ File: `routes/web.php`
- ✅ Route untuk kepala: `kepala.exams.print-participants`
- ✅ Route untuk guru: `guru.exams.print-participants`

### 3️⃣ **Controller** 
- ✅ File: `app/Http/Controllers/Kepala/KepalaExamController.php`
- ✅ Method: `printParticipantCards()`

### 4️⃣ **PDF Template** ✨ NEW
- ✅ File: `resources/views/exports/participant-cards.blade.php`
- ✅ Layout: Kartu KTP (85.6mm × 53.98mm)
- ✅ Design: 2 bagian (atas/bawah) × 2 kolom per halaman

---

## 🎯 Fitur Utama

### Kartu Peserta:
- **Bagian Atas (Header):**
  - Background: Gradient ungu
  - Berisi: Nama sekolah & Judul ujian
  
- **Bagian Bawah (Data):**
  - Background: Putih
  - Berisi: Nama, Kelas, Email, Password/NIS

### Output PDF:
- Format: A4 Portrait
- Layout: 2 kartu per baris
- File name: `kartu-peserta-[nama-ujian].pdf`
- Auto-download saat tombol diklik

---

## 🚀 Cara Menggunakan

### Langkah 1: Login
```
URL: localhost/login
Login sebagai: Kepala Sekolah atau Guru
```

### Langkah 2: Akses Menu
```
Menu: Master Data Ujian Antar Sekolah
URL: /kepala/exams/global  (untuk kepala)
     /guru/exams/global    (untuk guru)
```

### Langkah 3: Cetak Kartu
```
1. Lihat daftar ujian
2. Cari ujian yang diinginkan
3. Klik tombol "Cetak Kartu Peserta" (warna ungu)
4. File PDF akan otomatis diunduh
5. Buka dan cetak file PDF
```

---

## 📊 Data Flow

```
User Login
    ↓
Akses Master Ujian
    ↓
Klik "Cetak Kartu Peserta"
    ↓
Controller: printParticipantCards()
    ├─ Get exam type info
    ├─ Get school info (dari session)
    ├─ Query preassigned students
    └─ Map student data
    ↓
Load PDF Template
    ├─ Render kartu (2 kolom)
    └─ Set styling & layout
    ↓
Generate PDF (DOMPDF)
    ↓
Download to Browser
```

---

## 🔧 Konfigurasi

### PDF Settings (di Controller):
```php
$pdf->setPaper('A4', 'portrait');      // Ukuran kertas
$pdf->setOption('dpi', 150);           // Resolusi
$pdf->setOption('isPhpEnabled', true); // Enable PHP
```

### Card Dimensions:
- Width: 85.6mm (standard KTP)
- Height: 53.98mm (standard KTP)
- Grid: 2 columns × auto rows

---

## 🎨 Styling Reference

### Warna Gradient Header:
```
Start: #667eea (Biru indigo)
End:   #764ba2 (Ungu)
```

### Font Sizes:
- School Name: 7pt (bold)
- Exam Title: 6pt (normal)
- Student Name: 7pt (bold)
- Class: 6pt
- Email: 5pt
- Password: 5.5pt (bold, red)

---

## 📱 Browser Compatibility

✅ Chrome  
✅ Firefox  
✅ Safari  
✅ Edge  
✅ Mobile Browsers  

**Note:** Untuk hasil terbaik, gunakan Chrome atau Firefox saat mencetak.

---

## ⚠️ Catatan Penting

1. **Sekolah di Session:**
   - Pastikan role yang login memiliki school_id di session
   - Jika tidak ada, muncul error: "Sekolah tidak ditemukan di session"

2. **Data Siswa:**
   - Hanya siswa yang sudah di-preassign untuk exam akan muncul
   - Register siswa ke exam sebelum mencetak kartu

3. **Password Display:**
   - Password yang ditampilkan adalah NIS (Nomor Induk Siswa)
   - Password asli tersimpan ter-hash di database

4. **PDF Generation:**
   - Proses on-the-fly (tidak disimpan ke disk)
   - Membutuhkan library DOMPDF (sudah installed)

---

## 🐛 Troubleshooting

### PDF tidak terunduh?
→ Check browser download settings  
→ Disable pop-up blocker  
→ Try dengan browser lain  

### Kartu kosong?
→ Pastikan ada siswa yang terdaftar untuk exam  
→ Check data di tabel `preassigned`  

### Error "Sekolah tidak ditemukan"?
→ Logout dan login kembali  
→ Check session data di file `.env`  

### Tampilan tidak sesuai?
→ Update browser ke versi terbaru  
→ Clear browser cache  
→ Coba dengan print preview dulu  

---

## 📚 Dokumentasi Lengkap

Untuk dokumentasi lebih detail, lihat file:
- `IMPLEMENTATION_SUMMARY.md` - Penjelasan teknis
- `PANDUAN_CETAK_KARTU.md` - Panduan lengkap dengan visualisasi

---

## 🎓 Database Relationships

```
Examtype (1) ←─ Exam (Many)
                 ↑
              Preassigned (Many) ─→ User (1) ─→ Student (1)
                                                    ↓
                                                  Grade (1)
```

---

## 📦 Dependencies

- Laravel 11+ (atau versi yang sesuai)
- Barryvdh\DomPDF
- Spatie Permission (untuk role)
- Blade Template Engine

---

## ✨ Future Enhancements

Fitur yang dapat dikembangkan:
- [ ] Filter by grade saat cetak
- [ ] Custom logo/branding
- [ ] Multiple export format (Excel, Word)
- [ ] Preview sebelum download
- [ ] Batch print multiple exams
- [ ] Security features (watermark, qrcode)

---

## 📞 Support

Jika ada masalah atau pertanyaan:
1. Check dokumentasi di `IMPLEMENTATION_SUMMARY.md`
2. Review controller method `printParticipantCards()`
3. Validate data di database
4. Check server logs untuk error details

---

**Version:** 1.0  
**Status:** ✅ PRODUCTION READY  
**Last Updated:** January 17, 2026  

🎉 **Fitur siap digunakan!**
