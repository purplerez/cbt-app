<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class StudentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithCustomCsvSettings
{
    protected $headings = [
        'nis' => ['NIS *', 'NIS', 'nis'],
        'nama' => ['Nama Lengkap *', 'Nama', 'nama'],
        'kelas_id' => ['Kelas ID *', 'Kelas ID', 'kelas_id'],
        'jenis_kelamin' => ['Jenis Kelamin (L/P) *', 'Jenis Kelamin', 'jenis_kelamin'],
        'tempat_lahir' => ['Tempat Lahir *', 'Tempat Lahir', 'tempat_lahir'],
        'tanggal_lahir' => ['Tanggal Lahir (YYYY-MM-DD) *', 'Tanggal Lahir', 'tanggal_lahir'],
        'alamat' => ['Alamat *', 'Alamat', 'alamat']
    ];

    protected function getValueFromRow($row, $field)
    {
        foreach ($this->headings[$field] as $heading) {
            if (isset($row[strtolower($heading)])) {
                return $row[strtolower($heading)];
            }
        }
        return null;
    }

    public function collection(Collection $rows)
    {
        Log::info('Starting import with rows:', ['count' => count($rows)]);

        foreach ($rows as $index => $row) {
            Log::info('Raw row data', [
                'row_number' => $index + 2,
                'data' => $row->toArray()
            ]);

            // Convert row to array and normalize keys
            $rowArray = [];
            foreach ($row->toArray() as $key => $value) {
                // Remove any BOM characters and normalize whitespace
                $key = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $key);
                $key = trim($key);
                $rowArray[$key] = $value;

                Log::info('Column mapping', [
                    'row' => $index + 2,
                    'original_key' => $key,
                    'value' => $value
                ]);
            }

            // Debug log
            Log::info('Processing row ' . ($index + 2), [
                'original' => $rowArray,
                'found_keys' => array_keys($rowArray)
            ]);

            // Skip empty rows
            if (empty(array_filter($rowArray))) {
                continue;
            }

            // Map the values using our heading aliases
            $mappedRow = [];
            $mappedRow = [];
            foreach ($this->headings as $key => $aliases) {
                $value = null;

                // Debug log for this field
                Log::info('Looking for field', [
                    'key' => $key,
                    'aliases' => $aliases,
                    'available_keys' => array_keys($rowArray)
                ]);

                // Try each alias
                foreach ($aliases as $alias) {
                    // Try direct lookup
                    if (isset($rowArray[$alias])) {
                        $value = $rowArray[$alias];
                        Log::info('Found direct match', [
                            'key' => $key,
                            'alias' => $alias,
                            'value' => $value
                        ]);
                        break;
                    }

                    // Try case-insensitive lookup
                    foreach ($rowArray as $rowKey => $rowValue) {
                        if (strcasecmp($rowKey, $alias) === 0) {
                            $value = $rowValue;
                            Log::info('Found case-insensitive match', [
                                'key' => $key,
                                'alias' => $alias,
                                'actual_key' => $rowKey,
                                'value' => $value
                            ]);
                            break 2;
                        }
                    }
                }

                // If still no value found, try without asterisk
                if ($value === null) {
                    foreach ($rowArray as $rowKey => $rowValue) {
                        $cleanKey = str_replace([' *', '(L/P)', '(YYYY-MM-DD)'], '', $rowKey);
                        $cleanKey = trim($cleanKey);
                        foreach ($aliases as $alias) {
                            $cleanAlias = str_replace([' *', '(L/P)', '(YYYY-MM-DD)'], '', $alias);
                            $cleanAlias = trim($cleanAlias);
                            if (strcasecmp($cleanKey, $cleanAlias) === 0) {
                                $value = $rowValue;
                                Log::info('Found match after cleaning', [
                                    'key' => $key,
                                    'clean_key' => $cleanKey,
                                    'clean_alias' => $cleanAlias,
                                    'value' => $value
                                ]);
                                break 2;
                            }
                        }
                    }
                }

                $mappedRow[$key] = $value;
                Log::info("Final mapping for {$key}", ['value' => $value]);
            }            DB::transaction(function() use ($mappedRow) {
                // Convert and clean all values
                $nis = trim(strval($mappedRow['nis'] ?? ''));
                $nama = trim(strval($mappedRow['nama'] ?? ''));
                $kelasId = !empty($mappedRow['kelas_id']) ? intval($mappedRow['kelas_id']) : null;
                $jenisKelamin = strtoupper(trim(strval($mappedRow['jenis_kelamin'] ?? '')));
                $tempatLahir = trim(strval($mappedRow['tempat_lahir'] ?? ''));
                $alamat = trim(strval($mappedRow['alamat'] ?? ''));

                // Handle date conversion
                $tanggalLahir = $mappedRow['tanggal_lahir'] ?? null;
                if (!empty($tanggalLahir)) {
                    if (is_numeric($tanggalLahir)) {
                        try {
                            $tanggalLahir = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggalLahir)->format('Y-m-d');
                        } catch (\Exception $e) {
                            Log::warning('Failed to convert Excel date', ['value' => $tanggalLahir, 'error' => $e->getMessage()]);
                            $tanggalLahir = null;
                        }
                    } else {
                        $tanggalLahir = trim(strval($tanggalLahir));
                        if (strpos($tanggalLahir, '/') !== false) {
                            // Convert DD/MM/YYYY to YYYY-MM-DD
                            $parts = explode('/', $tanggalLahir);
                            if (count($parts) === 3) {
                                $tanggalLahir = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
                            }
                        }

                        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalLahir)) {
                            try {
                                $date = new \DateTime($tanggalLahir);
                                $tanggalLahir = $date->format('Y-m-d');
                            } catch (\Exception $e) {
                                Log::warning('Failed to parse date string', ['value' => $tanggalLahir, 'error' => $e->getMessage()]);
                                $tanggalLahir = null;
                            }
                        }
                    }
                }

                Log::info('Processed values', [
                    'nis' => $nis,
                    'nama' => $nama,
                    'kelas_id' => $kelasId,
                    'jenis_kelamin' => $jenisKelamin,
                    'tanggal_lahir' => $tanggalLahir
                ]);

                // Create user account
                $user = User::create([
                    'name' => $nama,
                    'email' => $nis . '@student.test',
                    'password' => Hash::make($nis),
                    'email_verified_at' => now(),
                ]);

                $user->assignRole('siswa');

                // Create student record
                Student::create([
                    'user_id' => $user->id,
                    'school_id' => session('school_id'),
                    'nis' => $nis,
                    'name' => $nama,
                    'grade_id' => $kelasId,
                    'gender' => $jenisKelamin,
                    'p_birth' => $tempatLahir,
                    'd_birth' => $tanggalLahir,
                    'address' => $alamat,
                    'photo' => 'assets/images/students/default.jpg'
                ]);
            });
        }
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'input_encoding' => 'UTF-8'
        ];
    }

    public function rules(): array
    {
        return [
            'nis' => [
                'required',
                'unique:students,nis',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('NIS tidak boleh kosong');
                    }
                }
            ],
            'nama' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (empty(trim($value))) {
                        $fail('Nama tidak boleh kosong');
                    }
                }
            ],
            'kelas_id' => [
                'required',
                'exists:grades,id',
                function ($attribute, $value, $fail) {
                    if (!is_numeric($value)) {
                        $fail('Kelas ID harus berupa angka');
                    }
                }
            ],
            'jenis_kelamin' => [
                'required',
                'in:L,P,l,p',
                function ($attribute, $value, $fail) {
                    if (!in_array(strtoupper(trim($value)), ['L', 'P'])) {
                        $fail('Jenis kelamin harus L atau P');
                    }
                }
            ],
            'tempat_lahir' => ['required'],
            'tanggal_lahir' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        if (is_numeric($value)) {
                            \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                        } else {
                            $date = new \DateTime($value);
                            if ($date->format('Y-m-d') !== trim($value)) {
                                $fail('Format tanggal lahir harus YYYY-MM-DD');
                            }
                        }
                    } catch (\Exception $e) {
                        $fail('Format tanggal lahir tidak valid');
                    }
                }
            ],
            'alamat' => ['required'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.nis.required' => 'NIS wajib diisi',
            '*.nis.unique' => 'NIS sudah terdaftar',
            '*.nama.required' => 'Nama wajib diisi',
            '*.kelas_id.required' => 'Kelas wajib diisi',
            '*.kelas_id.exists' => 'ID Kelas tidak ditemukan dalam database',
            '*.jenis_kelamin.required' => 'Jenis kelamin wajib diisi',
            '*.jenis_kelamin.in' => 'Jenis kelamin harus L atau P (huruf besar atau kecil)',
            '*.tempat_lahir.required' => 'Tempat lahir wajib diisi',
            '*.tanggal_lahir.required' => 'Tanggal lahir wajib diisi (format: YYYY-MM-DD)',
            '*.alamat.required' => 'Alamat wajib diisi',
        ];
    }
}
