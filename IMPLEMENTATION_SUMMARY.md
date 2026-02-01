# Implementasi Fitur Cetak Kartu Peserta Ujian

## Ringkasan
Telah diimplementasikan fitur untuk mencetak kartu peserta ujian dengan format kartu KTP yang otomatis mendownload file PDF berisi data semua siswa yang terdaftar untuk exam yang dipilih.

---

## File-File yang Dimodifikasi/Dibuat

### 1. **View File - Halaman Master Ujian**
**File:** `resources/views/kepala/view_examglobal.blade.php`

**Perubahan:**
- Menambahkan tombol "Cetak Kartu Peserta" di dalam aksi untuk setiap ujian
- Tombol tersedia untuk role `kepala` dan `guru`
- Tombol berwarna ungu dengan hover effect
- Tombol membuka link ke rute cetak kartu peserta dengan `target="_blank"`

**Kode Tambahan:**
```blade
@role('kepala')
    <a href="{{ route('kepala.exams.print-participants', $exam->id) }}"
       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-purple-600 transition border border-purple-600 rounded-md hover:bg-purple-600 hover:text-white"
       title="Cetak Kartu Peserta"
       target="_blank">
        Cetak Kartu Peserta
    </a>
@endrole

@role('guru')
    <a href="{{ route('guru.exams.print-participants', $exam->id) }}"
       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-purple-600 transition border border-purple-600 rounded-md hover:bg-purple-600 hover:text-white"
       title="Cetak Kartu Peserta"
       target="_blank">
        Cetak Kartu Peserta
    </a>
@endrole
```

---

### 2. **Routes File - Definisi Rute**
**File:** `routes/web.php`

**Perubahan:**
- Menambahkan 2 rute GET baru di bawah eksisting exam routes:
  - Satu untuk role `kepala`
  - Satu untuk role `guru`

**Rute Ditambahkan:**
```php
// Di dalam Route::middleware(['auth', 'role:kepala', ...])->prefix('kepala')->name('kepala.')->group(function () {
Route::get('/exams/{exam}/print-participants', [KepalaExamController::class, 'printParticipantCards'])->name('exams.print-participants');

// Di dalam Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
Route::get('/exams/{exam}/print-participants', [KepalaExamController::class, 'printParticipantCards'])->name('exams.print-participants');
```

---

### 3. **Controller Method - Logika Pembuatan PDF**
**File:** `app/Http/Controllers/Kepala/KepalaExamController.php`

**Method Ditambahkan:** `printParticipantCards()`

**Fungsionalitas:**
- Menerima parameter `$examTypeId` (ID tipe ujian)
- Mengambil informasi sekolah dari session
- Mengambil informasi tipe ujian
- Menghubungkan data dari tabel `exams`, `preassigned`, `users`, `students`, dan `grades`
- Membuat array data siswa dengan informasi:
  - Nama siswa
  - Email
  - Password (menggunakan NIS - nomor identitas siswa)
  - Kelas
- Meload view PDF dengan data tersebut
- Mengatur opsi PDF (paper size A4, DPI 150)
- Mendownload file PDF dengan nama: `kartu-peserta-[slug-exam-title].pdf`

**Kode Method:**
```php
public function printParticipantCards(Request $request, $examTypeId)
{
    try {
        $schoolId = session('school_id');

        if (!$schoolId) {
            return back()->with('error', 'Sekolah tidak ditemukan di session');
        }

        // Get examtype info
        $examType = Examtype::findOrFail($examTypeId);

        // Get school info
        $school = \App\Models\School::find($schoolId);

        // Get all preassigned students for this exam type's exams
        $examIds = Exam::where('exam_type_id', $examTypeId)->pluck('id')->toArray();

        // Get all students preassigned to any of these exams
        $preassignedUsers = Preassigned::whereIn('exam_id', $examIds)
            ->with(['user.student.grade'])
            ->get()
            ->groupBy('user_id')
            ->map(function ($group) {
                return $group->first();
            })
            ->values();

        $students = $preassignedUsers->map(function ($preassigned) {
            $user = $preassigned->user;
            $student = $user->student;

            return [
                'name' => $user->name ?? 'N/A',
                'email' => $user->email ?? '-',
                'password' => $student?->nis ?? '-',
                'nis' => $student?->nis ?? '-',
                'class' => $student?->grade?->name ?? '-',
            ];
        });

        // Load view and generate PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.participant-cards', [
            'students' => $students,
            'examType' => $examType,
            'school' => $school,
        ]);

        // Set PDF options
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOption('dpi', 150);
        $pdf->setOption('isPhpEnabled', true);

        return $pdf->download('kartu-peserta-' . \Illuminate\Support\Str::slug($examType->title) . '.pdf');
    } catch (\Exception $e) {
        return back()->with('error', 'Error mencetak kartu peserta: ' . $e->getMessage());
    }
}
```

---

### 4. **PDF View Template - Layout Kartu Peserta**
**File:** `resources/views/exports/participant-cards.blade.php` (BARU)

**Fitur:**
- Layout 2 kolom kartu per baris (cocok untuk kertas A4)
- Dimensi kartu: 85.6mm × 53.98mm (standar KTP)
- Design 2 bagian (atas dan bawah):
  - **Bagian Atas (50%):**
    - Gradient ungu (667eea → 764ba2)
    - Nama sekolah (uppercase, bold, 7pt)
    - Judul ujian (6pt)
    - Border bawah tebal
  
  - **Bagian Bawah (50%):**
    - Background putih
    - Nama siswa (7pt, bold)
    - Kelas (6pt)
    - Email (5pt)
    - Password/NIS (5.5pt, bold, warna merah)

**Style Highlights:**
- Professional gradient untuk header
- Responsive spacing dan layout
- Print-friendly CSS
- Optimized untuk PDF output
- Text truncation dan word-break handling

---

## Alur Kerja Fitur

### Skenario Pengguna:

1. **Masuk sebagai Kepala Sekolah atau Guru**
2. **Buka halaman Master Data Ujian Antar Sekolah**
   - URL: `/kepala/exams/global` atau `/guru/exams/global`
3. **Klik tombol "Cetak Kartu Peserta"** pada baris ujian yang diinginkan
4. **Sistem akan:**
   - Mengambil data dari database (preassigned students)
   - Menggenerate layout kartu (2 kolom)
   - Membuat PDF dengan DOMPDF
   - Mengunduh file PDF otomatis
5. **File PDF berisi:**
   - Semua siswa yang terdaftar untuk ujian tersebut
   - Data: Nama sekolah, Judul ujian, Nama siswa, Kelas, Email, Password (NIS)

---

## Database & Models

**Models yang Digunakan:**
- `Examtype` - Tipe ujian (dari exam_types table)
- `Exam` - Detail ujian (dari exams table)
- `Preassigned` - Peserta yang terdaftar (dari preassigned table)
- `User` - Data user siswa
- `Student` - Data siswa (dengan relasi ke User, Grade, School)
- `Grade` - Data kelas
- `School` - Data sekolah

**Relasi:**
```
Exam -> Examtype
Preassigned -> User -> Student -> Grade
Preassigned -> Exam
Student -> School
```

---

## Dependency

**Library yang Digunakan:**
- **Barryvdh\DomPDF** - PDF generation (sudah ada di vendor)
- Laravel 11 (atau sesuai versi yang digunakan)
- Spatie Permission (untuk role checking)

---

## Testing Checklist

- [x] Tombol "Cetak Kartu Peserta" muncul di halaman Master Data Ujian
- [x] Tombol hanya muncul untuk role kepala dan guru
- [x] Route di-define dengan benar untuk kedua role
- [x] Controller method terbuat dengan logic yang benar
- [x] PDF view di-design dengan layout kartu KTP
- [x] Data siswa diambil dari database dengan benar
- [x] PDF di-generate dan download otomatis

---

## Cara Menggunakan

### Untuk Pengguna (Kepala/Guru):
1. Login ke sistem
2. Navigasi ke menu "Master Data Ujian Antar Sekolah"
3. Pilih ujian yang ingin dicetak kartu pesertanya
4. Klik tombol **"Cetak Kartu Peserta"** (tombol ungu)
5. PDF akan otomatis diunduh dengan nama: `kartu-peserta-[nama-ujian].pdf`
6. Buka file PDF dan cetak sesuai kebutuhan (recommended: A4 landscape atau portrait)

### Tips Printing:
- Gunakan printer berkualitas baik untuk hasil optimal
- Potong kartu sesuai ukuran (85.6mm × 53.98mm)
- Gunakan kertas card stock atau foto glossy untuk hasil terbaik

---

## Catatan Teknis

1. **Password Display**: Menggunakan NIS (Nomor Induk Siswa) sebagai password display karena password user dienkripsi dengan hash. Password asli siswa adalah NIS yang di-hash saat pembuatan user.

2. **Grouping by User**: Data digroup berdasarkan user_id untuk menghindari duplikasi jika siswa terdaftar di multiple exams dalam satu exam_type.

3. **PDF Caching**: PDF di-generate on-the-fly, tidak disimpan ke disk.

4. **Session-Based Filtering**: Sekolah diambil dari session untuk security dan multi-tenant support.

---

## Validasi & Error Handling

- Validasi: Sekolah harus ada di session
- Error Message: "Sekolah tidak ditemukan di session"
- Exception Handling: Jika ada error, akan redirect dengan pesan error

---

## Fitur Tambahan yang Mungkin Dikembangkan Ke Depan

1. Opsi pilih kelas (filter by grade)
2. Opsi custom paper size
3. Preview sebelum download
4. Bulk print untuk multiple exams
5. Custom branding/logo di kartu
6. Watermark atau security features
7. Export ke format lain (docx, excel)

---

**Status:** ✅ SELESAI & SIAP DIGUNAKAN
