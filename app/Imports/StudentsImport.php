<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    public function collection(Collection $rows)
    {
        Log::info('===== STARTING IMPORT =====', ['total_rows' => count($rows)]);

        $errorRows = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                Log::info("Processing row {$rowNumber}", $row->toArray());

                // Normalize and extract data
                $rowData = $row->toArray();

                // Log the raw keys to debug
                Log::info("Row {$rowNumber} keys:", ['keys' => array_keys($rowData)]);

                // Extract values with flexible key matching
                $nis = $this->extractValue($rowData, ['nis *', 'nis']);
                $nama = $this->extractValue($rowData, ['nama lengkap *', 'nama']);
                $kelasId = $this->extractValue($rowData, ['kelas id *', 'kelas id']);
                $jenisKelamin = $this->extractValue($rowData, ['jenis kelamin (l/p) *', 'jenis kelamin']);
                $tempatLahir = $this->extractValue($rowData, ['tempat lahir *', 'tempat lahir']);
                $tanggalLahir = $this->extractValue($rowData, ['tanggal lahir (yyyy-mm-dd) *', 'tanggal lahir']);
                $alamat = $this->extractValue($rowData, ['alamat *', 'alamat']);

                // Trim all values
                $nis = trim((string)$nis);
                $nama = trim((string)$nama);
                $jenisKelamin = strtoupper(trim((string)$jenisKelamin));
                $tempatLahir = trim((string)$tempatLahir);
                $tanggalLahir = trim((string)$tanggalLahir);
                $alamat = trim((string)$alamat);
                $kelasId = !empty($kelasId) ? intval($kelasId) : null;

                Log::info("Row {$rowNumber} extracted data", [
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas_id' => $kelasId,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $alamat,
                ]);

                // Validate required fields
                $errors = [];
                if (empty($nis)) {
                    $errors[] = 'NIS wajib diisi';
                }
                if (empty($nama)) {
                    $errors[] = 'Nama wajib diisi';
                }
                if (empty($kelasId)) {
                    $errors[] = 'Kelas ID wajib diisi';
                }
                if (empty($jenisKelamin)) {
                    $errors[] = 'Jenis kelamin wajib diisi';
                } elseif (!in_array($jenisKelamin, ['L', 'P'])) {
                    $errors[] = 'Jenis kelamin harus L atau P';
                }
                if (empty($tempatLahir)) {
                    $errors[] = 'Tempat lahir wajib diisi';
                }
                if (empty($tanggalLahir)) {
                    $errors[] = 'Tanggal lahir wajib diisi';
                }
                if (empty($alamat)) {
                    $errors[] = 'Alamat wajib diisi';
                }

                if (!empty($errors)) {
                    $errorRows[] = "Baris {$rowNumber}: " . implode(', ', $errors);
                    Log::warning("Row {$rowNumber} validation errors", ['errors' => $errors]);
                    continue;
                }

                // Validate kelas exists
                $gradeExists = Grade::where('id', $kelasId)->exists();
                if (!$gradeExists) {
                    $errorRows[] = "Baris {$rowNumber}: Kelas ID {$kelasId} tidak ditemukan";
                    Log::warning("Row {$rowNumber} kelas not found", ['kelas_id' => $kelasId]);
                    continue;
                }

                // Validate NIS is unique
                $nisExists = Student::where('nis', $nis)->exists();
                if ($nisExists) {
                    $errorRows[] = "Baris {$rowNumber}: NIS {$nis} sudah terdaftar";
                    Log::warning("Row {$rowNumber} NIS already exists", ['nis' => $nis]);
                    continue;
                }

                // Parse date
                $parsedDate = $this->parseDate($tanggalLahir);
                if (!$parsedDate) {
                    $errorRows[] = "Baris {$rowNumber}: Format tanggal tidak valid. Gunakan YYYY-MM-DD";
                    Log::warning("Row {$rowNumber} invalid date", ['tanggal_lahir' => $tanggalLahir]);
                    continue;
                }

                // Create user and student
                DB::transaction(function() use ($nis, $nama, $kelasId, $jenisKelamin, $tempatLahir, $parsedDate, $alamat) {
                    $user = User::create([
                        'name' => $nama,
                        'email' => $nis,
                        'password' => Hash::make($nis),
                        'email_verified_at' => now(),
                    ]);

                    $user->assignRole('siswa');

                    Student::create([
                        'user_id' => $user->id,
                        'school_id' => session('school_id'),
                        'nis' => $nis,
                        'name' => $nama,
                        'grade_id' => $kelasId,
                        'gender' => $jenisKelamin,
                        'p_birth' => $tempatLahir,
                        'd_birth' => $parsedDate,
                        'address' => $alamat,
                        'photo' => 'assets/images/students/default.jpg'
                    ]);
                });

                Log::info("Row {$rowNumber} imported successfully");

            } catch (\Exception $e) {
                $errorRows[] = "Baris {$rowNumber}: " . $e->getMessage();
                Log::error("Row {$rowNumber} import failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('===== IMPORT COMPLETED =====', ['error_count' => count($errorRows)]);

        if (!empty($errorRows)) {
            throw new \Exception('Import gagal: ' . implode(', ', $errorRows));
        }
    }

    /**
     * Extract value from row using multiple key attempts
     */
    private function extractValue($row, $keyOptions)
    {
        foreach ($keyOptions as $key) {
            // Try exact match (lowercase)
            $lowerKey = strtolower($key);
            if (isset($row[$lowerKey])) {
                return $row[$lowerKey];
            }

            // Try fuzzy match (remove special chars and spaces)
            $fuzzyKey = preg_replace('/[^a-z0-9]/', '', $lowerKey);
            foreach ($row as $rowKey => $rowValue) {
                $fuzzyRowKey = preg_replace('/[^a-z0-9]/', '', strtolower($rowKey));
                if ($fuzzyRowKey === $fuzzyKey) {
                    return $rowValue;
                }
            }
        }
        return null;
    }

    /**
     * Parse date in various formats
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Handle Excel numeric dates
        if (is_numeric($dateString)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Failed to convert Excel date', ['value' => $dateString]);
                return null;
            }
        }

        $dateString = trim((string)$dateString);

        // Try YYYY-MM-DD format first
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            try {
                new \DateTime($dateString);
                return $dateString;
            } catch (\Exception $e) {
                return null;
            }
        }

        // Try DD/MM/YYYY format
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            $parts = explode('/', $dateString);
            return $parts[2] . '-' . $parts[1] . '-' . $parts[0];
        }

        // Try to parse with DateTime
        try {
            $date = new \DateTime($dateString);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function rules(): array
    {
        return [];
    }
}
