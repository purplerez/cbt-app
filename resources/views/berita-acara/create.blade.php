<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Buat Berita Acara Baru') }}
            </h2>
            <a href="{{ route('kepala.berita-acara.index') }}"
               class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('kepala.berita-acara.store') }}" id="beritaAcaraForm">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <!-- Exam Type -->
                            <div>
                                <label for="exam_type_id" class="block text-sm font-medium text-gray-700">
                                    Tipe Ujian <span class="text-red-500">*</span>
                                </label>
                                <select name="exam_type_id" id="exam_type_id" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Tipe Ujian</option>
                                    @foreach($examTypes as $type)
                                        <option value="{{ $type->id }}" {{ old('exam_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('exam_type_id')" class="mt-2" />
                            </div>

                            <!-- Exam Subject -->
                            <div>
                                <label for="exam_id" class="block text-sm font-medium text-gray-700">
                                    Mata Pelajaran Ujian
                                </label>
                                <select name="exam_id" id="exam_id"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Mata Pelajaran (Optional)</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" {{ old('exam_id') == $exam->id ? 'selected' : '' }}>
                                            {{ $exam->title }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('exam_id')" class="mt-2" />
                            </div>

                            <!-- School -->
                            <div>
                                <label for="school_id" class="block text-sm font-medium text-gray-700">
                                    Sekolah <span class="text-red-500">*</span>
                                </label>
                                <select name="school_id" id="school_id" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Sekolah</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}"
                                                {{ (old('school_id') ?? $defaultSchoolId) == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('school_id')" class="mt-2" />
                            </div>

                            <!-- Room -->
                            <div>
                                <label for="room_id" class="block text-sm font-medium text-gray-700">
                                    Ruangan
                                </label>
                                <select name="room_id" id="room_id"
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Ruangan (Optional)</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                                            {{ $room->name }} - {{ $room->id }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('room_id')" class="mt-2" />
                            </div>

                            <!-- Date -->
                            <div>
                                <label for="tanggal_pelaksanaan" class="block text-sm font-medium text-gray-700">
                                    Tanggal Pelaksanaan <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="tanggal_pelaksanaan" id="tanggal_pelaksanaan"
                                       value="{{ old('tanggal_pelaksanaan') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('tanggal_pelaksanaan')" class="mt-2" />
                            </div>

                            <!-- Time Start -->
                            <div>
                                <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">
                                    Waktu Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="waktu_mulai" id="waktu_mulai"
                                       value="{{ old('waktu_mulai', '08:00') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('waktu_mulai')" class="mt-2" />
                            </div>

                            <!-- Time End -->
                            <div>
                                <label for="waktu_selesai" class="block text-sm font-medium text-gray-700">
                                    Waktu Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="waktu_selesai" id="waktu_selesai"
                                       value="{{ old('waktu_selesai', '10:00') }}" required
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('waktu_selesai')" class="mt-2" />
                            </div>

                            <!-- Auto Fill Button -->
                            <div class="flex items-end">
                                <button type="button" id="autoFillBtn"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Isi Otomatis dari Data Ujian
                                </button>
                            </div>
                        </div>

                        <hr class="my-6">

                        <h3 class="mb-4 text-lg font-medium text-gray-900">Data Kehadiran</h3>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <!-- Registered -->
                            <div>
                                <label for="jumlah_peserta_terdaftar" class="block text-sm font-medium text-gray-700">
                                    Jumlah Peserta Terdaftar <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="jumlah_peserta_terdaftar" id="jumlah_peserta_terdaftar"
                                       value="{{ old('jumlah_peserta_terdaftar', 0) }}" required min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('jumlah_peserta_terdaftar')" class="mt-2" />
                            </div>

                            <!-- Present -->
                            <div>
                                <label for="jumlah_peserta_hadir" class="block text-sm font-medium text-gray-700">
                                    Jumlah Peserta Hadir <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="jumlah_peserta_hadir" id="jumlah_peserta_hadir"
                                       value="{{ old('jumlah_peserta_hadir', 0) }}" required min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('jumlah_peserta_hadir')" class="mt-2" />
                            </div>

                            <!-- Absent -->
                            <div>
                                <label for="jumlah_peserta_tidak_hadir" class="block text-sm font-medium text-gray-700">
                                    Jumlah Peserta Tidak Hadir <span class="text-red-500">*</span>
                                </label>
                                <input type="number" name="jumlah_peserta_tidak_hadir" id="jumlah_peserta_tidak_hadir"
                                       value="{{ old('jumlah_peserta_tidak_hadir', 0) }}" required min="0"
                                       class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <x-input-error :messages="$errors->get('jumlah_peserta_tidak_hadir')" class="mt-2" />
                            </div>
                        </div>

                        <hr class="my-6">

                        <h3 class="mb-4 text-lg font-medium text-gray-900">Kondisi Pelaksanaan</h3>

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                            <!-- Exam Condition -->
                            <div>
                                <label for="kondisi_pelaksanaan" class="block text-sm font-medium text-gray-700">
                                    Kondisi Pelaksanaan <span class="text-red-500">*</span>
                                </label>
                                <select name="kondisi_pelaksanaan" id="kondisi_pelaksanaan" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="lancar" {{ old('kondisi_pelaksanaan') == 'lancar' ? 'selected' : '' }}>Lancar</option>
                                    <option value="ada_kendala" {{ old('kondisi_pelaksanaan') == 'ada_kendala' ? 'selected' : '' }}>Ada Kendala</option>
                                    <option value="terganggu" {{ old('kondisi_pelaksanaan') == 'terganggu' ? 'selected' : '' }}>Terganggu</option>
                                </select>
                                <x-input-error :messages="$errors->get('kondisi_pelaksanaan')" class="mt-2" />
                            </div>

                            <!-- Room Condition -->
                            <div>
                                <label for="kondisi_ruangan" class="block text-sm font-medium text-gray-700">
                                    Kondisi Ruangan <span class="text-red-500">*</span>
                                </label>
                                <select name="kondisi_ruangan" id="kondisi_ruangan" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="baik" {{ old('kondisi_ruangan') == 'baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="cukup" {{ old('kondisi_ruangan') == 'cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="kurang" {{ old('kondisi_ruangan') == 'kurang' ? 'selected' : '' }}>Kurang</option>
                                </select>
                                <x-input-error :messages="$errors->get('kondisi_ruangan')" class="mt-2" />
                            </div>

                            <!-- Equipment Condition -->
                            <div>
                                <label for="kondisi_peralatan" class="block text-sm font-medium text-gray-700">
                                    Kondisi Peralatan <span class="text-red-500">*</span>
                                </label>
                                <select name="kondisi_peralatan" id="kondisi_peralatan" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="baik" {{ old('kondisi_peralatan') == 'baik' ? 'selected' : '' }}>Baik</option>
                                    <option value="cukup" {{ old('kondisi_peralatan') == 'cukup' ? 'selected' : '' }}>Cukup</option>
                                    <option value="kurang" {{ old('kondisi_peralatan') == 'kurang' ? 'selected' : '' }}>Kurang</option>
                                </select>
                                <x-input-error :messages="$errors->get('kondisi_peralatan')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="kendala" class="block text-sm font-medium text-gray-700">
                                Kendala/Masalah (jika ada)
                            </label>
                            <textarea name="kendala" id="kendala" rows="3"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('kendala') }}</textarea>
                            <x-input-error :messages="$errors->get('kendala')" class="mt-2" />
                        </div>

                        <hr class="my-6">

                        <h3 class="mb-4 text-lg font-medium text-gray-900">Pengawas</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                 Inputkan Pengawas
                            </label>
                            <div class="space-y-3">
                                <div>
                                    <label for="pengawas_1" class="block text-xs font-medium text-gray-600 mb-1">
                                        Pengawas 1
                                    </label>
                                    <input
                                        type="text"
                                        name="pengawas[]"
                                        id="pengawas_1"
                                        placeholder="Masukkan nama pengawas 1"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    >
                                </div>

                                <div>
                                    <label for="pengawas_2" class="block text-xs font-medium text-gray-600 mb-1">
                                        Pengawas 2
                                    </label>
                                    <input
                                        type="text"
                                        name="pengawas[]"
                                        id="pengawas_2"
                                        placeholder="Masukkan nama pengawas 2"
                                        class="block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                    >
                                </div>
                            <x-input-error :messages="$errors->get('pengawas')" class="mt-2" />
                        </div>

                        <hr class="my-6">

                        <div>
                            <label for="catatan_khusus" class="block text-sm font-medium text-gray-700">
                                Catatan Khusus
                            </label>
                            <textarea name="catatan_khusus" id="catatan_khusus" rows="4"
                                      class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('catatan_khusus') }}</textarea>
                            <x-input-error :messages="$errors->get('catatan_khusus')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <a href="{{ route('kepala.berita-acara.index') }}"
                               class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Batal
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                                Simpan Berita Acara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Auto-fill functionality
        document.getElementById('autoFillBtn').addEventListener('click', async function() {
            const examTypeId = document.getElementById('exam_type_id').value;
            const examId = document.getElementById('exam_id').value;
            const schoolId = document.getElementById('school_id').value;

            if (!examTypeId || !examId || !schoolId) {
                alert('Pilih Tipe Ujian, Mata Pelajaran, dan Sekolah terlebih dahulu!');
                return;
            }

            try {
                const response = await fetch('{{ route("kepala.berita-acara.autofill") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        exam_type_id: examTypeId,
                        exam_id: examId,
                        school_id: schoolId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('jumlah_peserta_hadir').value = result.data.jumlah_peserta_hadir;
                    document.getElementById('jumlah_peserta_tidak_hadir').value = result.data.jumlah_peserta_tidak_hadir;
                    document.getElementById('jumlah_peserta_terdaftar').value = result.data.jumlah_peserta_terdaftar;

                    alert('Data berhasil diisi otomatis!');
                } else {
                    alert('Gagal mengisi data otomatis');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengisi data otomatis');
            }
        });

        // Auto calculate absent
        ['jumlah_peserta_terdaftar', 'jumlah_peserta_hadir'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                const terdaftar = parseInt(document.getElementById('jumlah_peserta_terdaftar').value) || 0;
                const hadir = parseInt(document.getElementById('jumlah_peserta_hadir').value) || 0;
                document.getElementById('jumlah_peserta_tidak_hadir').value = Math.max(0, terdaftar - hadir);
            });
        });
    </script>
    @endpush
</x-app-layout>
