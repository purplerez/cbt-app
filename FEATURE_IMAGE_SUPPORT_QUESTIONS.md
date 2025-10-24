# Fitur Upload Gambar untuk Soal dan Pilihan Jawaban

## Ringkasan
Fitur ini menambahkan kemampuan untuk mengunggah gambar pada soal ujian dan pilihan jawaban di halaman `manageperexam.blade.php`.

## Perubahan yang Dilakukan

### 1. Migrasi Database

**File**: `database/migrations/2025_10_23_000001_add_image_support_to_exam_questions_table.php`

Menambahkan dua kolom baru ke tabel `exam_questions`:
- `question_image` (string, nullable) - Menyimpan path gambar untuk soal
- `choices_images` (json, nullable) - Menyimpan path gambar untuk setiap pilihan jawaban dalam format JSON

**Cara menjalankan migrasi**:
```bash
php artisan migrate
```

**Rollback** (jika diperlukan):
```bash
php artisan migrate:rollback
```

### 2. Model Question

**File**: `app/Models/Question.php`

Menambahkan field baru ke dalam `$fillable`:
- `question_image`
- `choices_images`

### 3. QuestionController

**File**: `app/Http/Controllers/QuestionController.php`

#### Perubahan pada method `store()`:
- Menambahkan validasi untuk upload gambar:
  - `question_image`: image|mimes:jpeg,png,jpg,gif,svg|max:2048
  - `choice_images.*`: image|mimes:jpeg,png,jpg,gif,svg|max:2048
- Menyimpan gambar soal ke `storage/app/public/question_images/`
- Menyimpan gambar pilihan jawaban ke `storage/app/public/choice_images/`
- Menyimpan path gambar ke database dalam format JSON untuk `choices_images`

#### Perubahan pada method `update()`:
- Menangani upload gambar baru
- Menghapus gambar lama saat ada upload gambar baru
- Menangani penghapusan gambar (melalui checkbox "Hapus gambar")
- Mempertahankan gambar yang sudah ada jika tidak ada perubahan

#### Perubahan pada method `destroy()`:
- Menghapus semua gambar terkait (gambar soal dan gambar pilihan) dari storage saat soal dihapus

### 4. View (manageperexam.blade.php)

**File**: `resources/views/admin/manageperexam.blade.php`

#### Form Tambah Soal:
- Menambahkan `enctype="multipart/form-data"` pada form
- Menambahkan input file untuk gambar soal dengan preview
- Menambahkan input file untuk gambar pada setiap pilihan jawaban dengan preview
- Menambahkan fungsi JavaScript untuk preview gambar sebelum upload

#### Form Edit Soal:
- Menambahkan `enctype="multipart/form-data"` pada form
- Menampilkan gambar yang sudah ada (jika ada)
- Menambahkan checkbox untuk menghapus gambar
- Menambahkan input file untuk mengganti gambar
- Preview untuk gambar baru

#### Tabel Daftar Soal:
- Menampilkan gambar soal (jika ada) di kolom soal

#### JavaScript Functions:
- `previewQuestionImage()` - Preview gambar soal saat menambah
- `previewChoiceImage()` - Preview gambar pilihan saat menambah
- `previewEditQuestionImage()` - Preview gambar soal saat edit
- `previewEditChoiceImage()` - Preview gambar pilihan saat edit
- Update pada fungsi add choice untuk mendukung upload gambar

## Cara Penggunaan

### Menambah Soal dengan Gambar

1. Klik tombol **"+ Tambah Soal"**
2. Isi pertanyaan di textarea "Pertanyaan"
3. (Opsional) Klik **"Choose File"** di bagian "Gambar Soal" untuk mengunggah gambar soal
4. Isi pilihan jawaban di textarea yang tersedia
5. (Opsional) Untuk setiap pilihan, klik **"Choose File"** di bagian "Gambar Pilihan" untuk mengunggah gambar
6. Pilih kunci jawaban
7. Isi poin/bobot soal
8. Klik **"Simpan"**

### Mengedit Soal dengan Gambar

1. Klik tombol **"Ubah"** pada soal yang ingin diedit
2. Untuk mengubah gambar soal:
   - Jika sudah ada gambar, centang **"Hapus gambar"** untuk menghapus
   - Pilih file gambar baru untuk mengganti
3. Untuk mengubah gambar pilihan:
   - Centang **"Hapus gambar"** pada pilihan yang ingin dihapus gambarnya
   - Pilih file gambar baru untuk mengganti atau menambah
4. Klik **"Update Soal"**

## Format Data

### Struktur Database

#### Tabel: exam_questions
| Field | Type | Description |
|-------|------|-------------|
| question_image | VARCHAR(255) NULL | Path ke file gambar soal |
| choices_images | JSON NULL | Object JSON berisi path gambar untuk setiap pilihan |

#### Contoh Data choices_images:
```json
{
  "1": "choice_images/abc123.jpg",
  "2": "choice_images/def456.png",
  "3": "choice_images/ghi789.jpg"
}
```

Key adalah ID pilihan (sesuai dengan key di field `choices`), value adalah path file gambar.

## Batasan dan Spesifikasi

- **Format gambar yang didukung**: JPEG, PNG, JPG, GIF, SVG
- **Ukuran maksimal**: 2048 KB (2 MB) per gambar
- **Lokasi penyimpanan**: 
  - Gambar soal: `storage/app/public/question_images/`
  - Gambar pilihan: `storage/app/public/choice_images/`
- **URL akses gambar**: `storage/question_images/` atau `storage/choice_images/`

## Catatan Penting

1. **Storage Link**: Pastikan symbolic link sudah dibuat untuk mengakses file dari browser:
   ```bash
   php artisan storage:link
   ```

2. **Permissions**: Pastikan folder `storage/app/public/` memiliki permission yang sesuai (755 atau 775)

3. **Backup**: Gambar disimpan di server, pastikan melakukan backup berkala

4. **Penghapusan**: Saat soal dihapus, semua gambar terkait juga akan dihapus dari storage

## Testing

### Test Case 1: Menambah Soal dengan Gambar
- [ ] Buat soal baru dengan gambar soal
- [ ] Buat soal baru dengan gambar pada pilihan
- [ ] Buat soal baru dengan gambar soal dan pilihan
- [ ] Verifikasi gambar tersimpan di database dan storage
- [ ] Verifikasi gambar ditampilkan di tabel

### Test Case 2: Edit Soal dengan Gambar
- [ ] Edit soal, tambah gambar baru
- [ ] Edit soal, ganti gambar yang sudah ada
- [ ] Edit soal, hapus gambar yang sudah ada
- [ ] Verifikasi gambar lama terhapus dari storage
- [ ] Verifikasi gambar baru tersimpan dengan benar

### Test Case 3: Hapus Soal
- [ ] Hapus soal yang memiliki gambar
- [ ] Verifikasi gambar terhapus dari storage
- [ ] Verifikasi record terhapus dari database

### Test Case 4: Validasi
- [ ] Upload file dengan format tidak valid (misal: .txt)
- [ ] Upload file dengan ukuran > 2MB
- [ ] Verifikasi error message ditampilkan

## Troubleshooting

### Gambar tidak muncul di browser
- Pastikan `php artisan storage:link` sudah dijalankan
- Cek permission folder storage
- Cek path gambar di database

### Error saat upload
- Cek `upload_max_filesize` dan `post_max_size` di php.ini
- Cek permission folder storage/app/public/
- Cek log Laravel di `storage/logs/laravel.log`

### Gambar tidak terhapus saat update/delete
- Cek permission folder storage
- Cek apakah path di database benar
- Cek log error di Laravel

## Pengembangan Selanjutnya (Opsional)

1. Kompresi gambar otomatis untuk menghemat storage
2. Crop/resize gambar sebelum disimpan
3. Lazy loading untuk gambar
4. Thumbnail untuk preview yang lebih cepat
5. Dukungan untuk drag-and-drop upload
6. Multiple image upload sekaligus
7. Image editor built-in (crop, rotate, etc.)
