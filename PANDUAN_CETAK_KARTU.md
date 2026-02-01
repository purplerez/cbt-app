# Panduan Visual - Fitur Cetak Kartu Peserta

## 1. Tombol "Cetak Kartu Peserta" di Halaman Master Ujian

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Master Data Ujian Antar Sekolah                                        │
├─────────────────────────────────────────────────────────────────────────┤
│  No │ Nama Ujian    │ Status     │ Aksi                                 │
├─────┼───────────────┼────────────┼──────────────────────────────────────┤
│ 1   │ Ujian Akhir   │ ✓ Aktif    │ [Daftarkan Siswa] [Ruang]           │
│     │               │            │ [Cetak Kartu Peserta] [Rekam Ujian] │
├─────┼───────────────┼────────────┼──────────────────────────────────────┤
│ 2   │ Ujian Tengah  │ ✓ Aktif    │ [Daftarkan Siswa] [Ruang]           │
│     │               │            │ [Cetak Kartu Peserta] [Rekam Ujian] │
└─────┴───────────────┴────────────┴──────────────────────────────────────┘

Tombol "Cetak Kartu Peserta" berwarna UNGU, letaknya diantara tombol "Ruang" 
dan tombol "Rekam Ujian"
```

---

## 2. Desain Kartu Peserta (KTP Format)

Setiap kartu berukuran: **85.6mm × 53.98mm** (standar KTP)

### Layout Kartu:

```
┌─────────────────────────────────┐
│  ╔═════════════════════════════╗ │
│  ║                             ║ │ 50% - BAGIAN ATAS
│  ║   NAMA SEKOLAH              ║ │ Background: Gradient Ungu
│  ║   JUDUL UJIAN               ║ │
│  ║                             ║ │
│  ╚═════════════════════════════╝ │
│  ┌─────────────────────────────┐ │
│  │ Ahmad Reza Wijaya           │ │ 50% - BAGIAN BAWAH
│  │                             │ │ Background: Putih
│  │ Kelas: XII IPA 1            │ │
│  │ Email: 123456@student.test  │ │
│  │ Pass: 123456                │ │
│  └─────────────────────────────┘ │
└─────────────────────────────────┘

Layout: 2 kartu per baris (A4 Portrait)

Contoh Page PDF dengan 6 siswa:

┌─────────────────────┬─────────────────────┐
│   Kartu 1           │   Kartu 2           │
├─────────────────────┼─────────────────────┤
│   Kartu 3           │   Kartu 4           │
├─────────────────────┼─────────────────────┤
│   Kartu 5           │   Kartu 6           │
└─────────────────────┴─────────────────────┘
```

---

## 3. Data Pada Setiap Kartu

### Bagian Atas (Header - Ungu):
- **Nama Sekolah** (7pt, bold, uppercase)
  Contoh: "SMA NEGERI 1 JAKARTA"
  
- **Judul Ujian** (6pt, normal)
  Contoh: "UJIAN AKHIR SEMESTER GASAL TAHUN 2025"

### Bagian Bawah (Data Siswa - Putih):
- **Nama Siswa** (7pt, bold)
  Contoh: "Ahmad Reza Wijaya"
  
- **Kelas** (6pt, label "Kelas: ")
  Contoh: "XII IPA 1"
  
- **Email** (5pt, label "Email: ")
  Contoh: "123456@student.test"
  
- **Password/NIS** (5.5pt, bold, warna merah, label "Pass: ")
  Contoh: "123456"

---

## 4. Flow Diagram

```
┌─────────────────────────────────────────────────────────────────┐
│                    HALAMAN MASTER UJIAN                          │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
                 ┌──────────────────────┐
                 │ Klik "Cetak Kartu    │
                 │ Peserta"             │
                 └──────────┬───────────┘
                           │
                           ▼
          ┌────────────────────────────────────┐
          │ printParticipantCards() Method      │
          │                                    │
          │ 1. Get exam type info             │
          │ 2. Get school info from session   │
          │ 3. Get preassigned students       │
          │ 4. Map student data               │
          │ 5. Prepare PDF data               │
          └────────────┬───────────────────────┘
                       │
                       ▼
          ┌────────────────────────────────────┐
          │ Load Blade View: participant-cards │
          │                                    │
          │ Render kartu untuk setiap siswa   │
          │ Layout: 2 kolom per baris         │
          │ Paper: A4 Portrait                │
          └────────────┬───────────────────────┘
                       │
                       ▼
          ┌────────────────────────────────────┐
          │ Generate PDF (DOMPDF)              │
          │                                    │
          │ File: kartu-peserta-[exam].pdf   │
          └────────────┬───────────────────────┘
                       │
                       ▼
          ┌────────────────────────────────────┐
          │ Download File to User              │
          │                                    │
          │ Browser akan meminta save/open    │
          └────────────────────────────────────┘
```

---

## 5. Contoh Hasil PDF

### Halaman 1 dari PDF (2 kartu):

```
══════════════════════════════════════════════════════════════════════

┌──────────────────────────┐  ┌──────────────────────────┐
│ ╔════════════════════╗   │  │ ╔════════════════════╗   │
│ ║ SMA NEGERI 1       ║   │  │ ║ SMA NEGERI 1       ║   │
│ ║ JAKARTA            ║   │  │ ║ JAKARTA            ║   │
│ ║                    ║   │  │ ║                    ║   │
│ ║ UJIAN AKHIR        ║   │  │ ║ UJIAN AKHIR        ║   │
│ ║ SEMESTER GASAL     ║   │  │ ║ SEMESTER GASAL     ║   │
│ ║ 2025               ║   │  │ ║ 2025               ║   │
│ ╚════════════════════╝   │  │ ╚════════════════════╝   │
│ ┌──────────────────────┐ │  │ ┌──────────────────────┐ │
│ │ Ahmad Reza Wijaya    │ │  │ │ Siti Nurhaliza       │ │
│ │ Kelas: XII IPA 1     │ │  │ │ Kelas: XII IPA 1     │ │
│ │ Email:               │ │  │ │ Email:               │ │
│ │ 123456@student.test  │ │  │ │ 123457@student.test  │ │
│ │ Pass: 123456         │ │  │ │ Pass: 123457         │ │
│ └──────────────────────┘ │  │ └──────────────────────┘ │
└──────────────────────────┘  └──────────────────────────┘

Spasi vertikal untuk memotong

┌──────────────────────────┐  ┌──────────────────────────┐
│ ╔════════════════════╗   │  │ ╔════════════════════╗   │
│ ║ SMA NEGERI 1       ║   │  │ ║ SMA NEGERI 1       ║   │
│ ║ JAKARTA            ║   │  │ ║ JAKARTA            ║   │
│ ║                    ║   │  │ ║                    ║   │
│ ║ UJIAN AKHIR        ║   │  │ ║ UJIAN AKHIR        ║   │
│ ║ SEMESTER GASAL     ║   │  │ ║ SEMESTER GASAL     ║   │
│ ║ 2025               ║   │  │ ║ 2025               ║   │
│ ╚════════════════════╝   │  │ ╚════════════════════╝   │
│ ┌──────────────────────┐ │  │ ┌──────────────────────┐ │
│ │ Budi Santoso         │ │  │ │ Ratih Kusuma         │ │
│ │ Kelas: XII IPS 2     │ │  │ │ Kelas: XII IPS 2     │ │
│ │ Email:               │ │  │ │ Email:               │ │
│ │ 123458@student.test  │ │  │ │ 123459@student.test  │ │
│ │ Pass: 123458         │ │  │ │ Pass: 123459         │ │
│ └──────────────────────┘ │  │ └──────────────────────┘ │
└──────────────────────────┘  └──────────────────────────┘

... dan seterusnya untuk kartu-kartu lainnya

══════════════════════════════════════════════════════════════════════
```

---

## 6. Instruksi Pencetakan

### Setup Printer:
1. **Paper Size:** A4 (210mm × 297mm)
2. **Orientation:** Portrait
3. **Margins:** Minimal (10mm dari tepi)
4. **Quality:** Tinggi (untuk hasil gambar yang baik)

### Cara Mencetak:
1. Download PDF dari sistem
2. Buka file PDF dengan Adobe Reader atau browser
3. Buka menu Print (Ctrl+P atau Cmd+P)
4. Pilih printer
5. Setup paper size: A4 Portrait
6. Klik Print

### Cara Memotong:
1. Setelah dicetak, persiapkan gunting atau cutter
2. Garis putus-putus pada PDF menunjukkan tempat pemotongan
3. Potong kartu sesuai ukuran standar KTP (85.6mm × 53.98mm)
4. Kartu siap digunakan

---

## 7. File-File yang Terlibat

### Core Files:
- `routes/web.php` - Definisi route
- `app/Http/Controllers/Kepala/KepalaExamController.php` - Controller logic
- `resources/views/exports/participant-cards.blade.php` - PDF template (BARU)
- `resources/views/kepala/view_examglobal.blade.php` - Tombol UI

### Dependencies:
- `barryvdh/laravel-dompdf` - Library PDF generation

---

## 8. Error Handling

### Pesan Error yang Mungkin:

| Error | Penyebab | Solusi |
|-------|---------|--------|
| "Sekolah tidak ditemukan di session" | Logout dan login kembali | Pastikan login dengan role yang tepat |
| PDF tidak terdownload | Browser block pop-up | Allow pop-up untuk domain ini |
| Kartu kosong/data tidak muncul | Tidak ada siswa terdaftar | Register siswa ke exam terlebih dahulu |

---

## 9. Tips & Trik

### Menghemat Kertas:
- Cetak 2 halaman per kertas (gunakan scaling di print settings)
- Pilih "Print in Grayscale" untuk menghemat tinta warna

### Kualitas Terbaik:
- Gunakan printer laser untuk hasil sharp
- Gunakan kertas glossy atau matte card stock
- Set DPI minimum 300 untuk hasil optimal

### Batch Processing:
- Jika banyak ujian, cetak semua dalam satu session
- Organize file berdasarkan nama ujian

---

**Version:** 1.0  
**Last Updated:** January 17, 2026  
**Status:** ✅ PRODUCTION READY
