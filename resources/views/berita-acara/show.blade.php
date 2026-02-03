<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Detail Berita Acara') }}
            </h2>
            <div class="flex space-x-2">
                @if($beritaAcara->canBeEdited() && auth()->user()->hasRole('kepala'))
                    <a href="{{ route(request()->route()->getPrefix() . '.berita-acara.edit', $beritaAcara) }}"
                       class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                @endif
                <a href="{{ route(request()->route()->getPrefix() . '.berita-acara.pdf', $beritaAcara) }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </a>
                <a href="{{ route(request()->route()->getPrefix() . '.berita-acara.student-list', $beritaAcara) }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Daftar Hadir Siswa
                </a>
                <a href="{{ route(request()->route()->getPrefix() . '.berita-acara.index') }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Header Info -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Berita Acara</h3>
                            <dl class="space-y-2">
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Nomor BA:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900 font-semibold">{{ $beritaAcara->nomor_ba }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Status:</dt>
                                    <dd class="w-2/3">
                                        <span class="inline-flex px-2 text-xs font-semibold leading-5 rounded-full {{ $beritaAcara->status_badge }}">
                                            {{ $beritaAcara->status_label }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Dibuat oleh:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->creator->name ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Dibuat pada:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->created_at->format('d/m/Y H:i') }}</dd>
                                </div>
                                @if($beritaAcara->status === 'approved')
                                    <div class="flex">
                                        <dt class="w-1/3 text-sm font-medium text-gray-500">Disetujui oleh:</dt>
                                        <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->approver->name ?? '-' }}</dd>
                                    </div>
                                    <div class="flex">
                                        <dt class="w-1/3 text-sm font-medium text-gray-500">Disetujui pada:</dt>
                                        <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->approved_at?->format('d/m/Y H:i') }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Ujian</h3>
                            <dl class="space-y-2">
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Tipe Ujian:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->examType->title ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Mata Pelajaran:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->exam->title ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Sekolah:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->school->name ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Ruangan:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->room->nama_ruangan ?? '-' }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Tanggal:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->tanggal_pelaksanaan->format('d F Y') }}</dd>
                                </div>
                                <div class="flex">
                                    <dt class="w-1/3 text-sm font-medium text-gray-500">Waktu:</dt>
                                    <dd class="w-2/3 text-sm text-gray-900">{{ $beritaAcara->waktu_mulai }} - {{ $beritaAcara->waktu_selesai }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Info -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Kehadiran Peserta</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <div class="text-sm text-blue-600 font-medium">Terdaftar</div>
                            <div class="text-2xl font-bold text-blue-900">{{ $beritaAcara->jumlah_peserta_terdaftar }}</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <div class="text-sm text-green-600 font-medium">Hadir</div>
                            <div class="text-2xl font-bold text-green-900">{{ $beritaAcara->jumlah_peserta_hadir }}</div>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <div class="text-sm text-red-600 font-medium">Tidak Hadir</div>
                            <div class="text-2xl font-bold text-red-900">{{ $beritaAcara->jumlah_peserta_tidak_hadir }}</div>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <div class="text-sm text-purple-600 font-medium">Persentase Kehadiran</div>
                            <div class="text-2xl font-bold text-purple-900">{{ $beritaAcara->persentase_kehadiran }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Conditions -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Kondisi Pelaksanaan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Kondisi Pelaksanaan</div>
                            <div class="text-base text-gray-900 capitalize">{{ str_replace('_', ' ', $beritaAcara->kondisi_pelaksanaan) }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Kondisi Ruangan</div>
                            <div class="text-base text-gray-900 capitalize">{{ $beritaAcara->kondisi_ruangan }}</div>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-1">Kondisi Peralatan</div>
                            <div class="text-base text-gray-900 capitalize">{{ $beritaAcara->kondisi_peralatan }}</div>
                        </div>
                    </div>

                    @if($beritaAcara->kendala)
                        <div class="mt-4 p-4 bg-yellow-50 rounded-lg">
                            <div class="text-sm font-medium text-yellow-800 mb-2">Kendala/Masalah:</div>
                            <div class="text-sm text-yellow-900">{{ $beritaAcara->kendala }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Proctors -->
            @if($beritaAcara->pengawas_users->isNotEmpty())
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pengawas dan Proktor</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($beritaAcara->pengawas_users as $pengawas)
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($pengawas->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $pengawas->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $pengawas->email }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Notes -->
            @if($beritaAcara->catatan_khusus)
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan Khusus</h3>
                        <div class="prose max-w-none text-gray-700">
                            {{ $beritaAcara->catatan_khusus }}
                        </div>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h3>
                    <div class="flex flex-wrap gap-2">
                        @if($beritaAcara->status === 'draft')
                            <form action="{{ route(request()->route()->getPrefix() . '.berita-acara.finalize', $beritaAcara) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Selesaikan Berita Acara ini? Setelah diselesaikan, Berita Acara akan siap untuk disetujui.')"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                                    Selesaikan BA
                                </button>
                            </form>
                        @endif

                        @if($beritaAcara->canBeApproved() && (auth()->user()->hasRole('kepala') || auth()->user()->hasRole('admin') || auth()->user()->hasRole('super')))
                            <form action="{{ route(request()->route()->getPrefix() . '.berita-acara.approve', $beritaAcara) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Setujui Berita Acara ini?')"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                    Setujui BA
                                </button>
                            </form>
                        @endif

                        @if($beritaAcara->status === 'approved')
                            <form action="{{ route(request()->route()->getPrefix() . '.berita-acara.archive', $beritaAcara) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" onclick="return confirm('Arsipkan Berita Acara ini?')"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-yellow-600 border border-transparent rounded-md hover:bg-yellow-700">
                                    Arsipkan BA
                                </button>
                            </form>
                        @endif

                        @if($beritaAcara->status === 'draft')
                            <form action="{{ route(request()->route()->getPrefix() . '.berita-acara.destroy', $beritaAcara) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus Berita Acara ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-red-600 border border-transparent rounded-md hover:bg-red-700">
                                    Hapus BA
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
