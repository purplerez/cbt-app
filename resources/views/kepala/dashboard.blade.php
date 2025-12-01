<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard Kepala Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Informasi Sekolah -->
            <div class="p-6 mb-6 bg-white rounded-lg shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Sekolah Anda</h3>
                <p class="text-2xl font-semibold text-gray-900" id="schoolName">-</p>
                <div class="flex items-center mt-2 text-sm">
                    <span class="text-gray-600" id="schoolInfo">-</span>
                </div>
            </div>

            <!-- Statistik Overview -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Siswa</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="totalStudents">-</p>
                    <div class="flex items-center mt-2 text-sm">
                        <span class="text-gray-600" id="totalGrades">- Kelas</span>
                    </div>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Siswa Online</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="onlineStudents">-</p>
                    <div class="flex items-center mt-2 text-sm">
                        <span class="text-gray-600" id="onlinePercentage">-%</span>
                        <span class="ml-2 text-xs text-gray-500">dari total siswa</span>
                    </div>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Ujian Aktif</h3>
                    <div class="flex items-center justify-between">
                        <p class="text-2xl font-semibold text-gray-900" id="activeExams">-</p>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full" id="examStatus">-</span>
                    </div>
                    <div class="flex items-center mt-2 text-sm">
                        <span class="text-gray-600" id="participantCount">- peserta</span>
                    </div>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Ujian</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="totalExams">-</p>
                    <div class="flex items-center mt-2 text-sm">
                        <span class="text-gray-600" id="examTypes">- jenis ujian</span>
                    </div>
                </div>
            </div>

            <!-- Statistik per Kelas -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Statistik Siswa per Kelas</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Total Siswa</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Siswa Online</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Persentase</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="gradeStats">
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ujian Aktif di Sekolah -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Ujian Sedang Berlangsung</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="activeExamsTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Ujian</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Peserta</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Progress</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Sisa Waktu</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="activeExamsBody">
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Nilai Ujian Terbaru -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Ringkasan Nilai Ujian Terbaru</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="recentScoresTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Ujian</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Rata-rata Nilai</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Peserta</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="recentScoresBody">
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Memuat data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Log Aktivitas -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Aktivitas Terbaru</h2>
                    <div class="space-y-4" id="activityLogs">
                        <div class="text-center text-gray-500">Memuat log aktivitas...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script src="{{ asset('js/kepala-dashboard.js') }}"></script>
    <script>
        // Initialize dashboard setelah DOM siap
        document.addEventListener('DOMContentLoaded', function() {
            window.kephalaDashboard = new KephalaDashboardManager();
        });
    </script>
@endpush
