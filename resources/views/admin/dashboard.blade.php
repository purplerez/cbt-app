<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Statistik Overview -->
            <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Siswa</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="totalStudents">-</p>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Ujian Aktif</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="activeExams">-</p>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Siswa Online</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="onlineStudents">-</p>
                </div>
                <div class="p-6 bg-white rounded-lg shadow-sm">
                    <h3 class="text-sm font-medium text-gray-500">Total Ujian Hari Ini</h3>
                    <p class="text-2xl font-semibold text-gray-900" id="todayExams">-</p>
                </div>
            </div>

            <!-- Ujian Aktif -->
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
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Sisa Waktu</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
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

            <!-- Log Aktivitas -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900">Log Aktivitas Terbaru</h2>
                    <div class="space-y-4" id="activityLogs">
                        <div class="text-center text-gray-500">Memuat log aktivitas...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@push('scripts')
    <script>
        function fetchDashboardStats() {
            if (!window.apiToken) {
                console.error("API Token not found");
                return;
            }

            // Fetch Overview Statistics
            fetch("/api/admin/dashboard/stats", {
                headers: {
                    "Authorization": "Bearer " + window.apiToken,
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('totalStudents').textContent = data.total_students;
                document.getElementById('activeExams').textContent = data.active_exams;
                document.getElementById('onlineStudents').textContent = data.online_students;
                document.getElementById('todayExams').textContent = data.today_exams;
            })
            .catch(error => {
                console.error("Stats API Error:", error);
            });

            // Fetch Active Exams
            fetch("/api/admin/dashboard/active-exams", {
                headers: {
                    "Authorization": "Bearer " + window.apiToken,
                    "Accept": "application/json"
                }
            })
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('activeExamsBody');
                tbody.innerHTML = '';

                if (data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada ujian yang sedang berlangsung
                            </td>
                        </tr>
                    `;
                    return;
                }

                data.forEach(exam => {
                    const row = `
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">${exam.name}</td>
                            <td class="px-6 py-4 whitespace-nowrap">${exam.grade}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                ${exam.active_participants}/${exam.total_participants}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">${exam.remaining_time} menit</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    ${exam.status === 'in_progress' ? 'bg-green-100 text-green-800' : 
                                      exam.status === 'waiting' ? 'bg-yellow-100 text-yellow-800' : 
                                      'bg-gray-100 text-gray-800'}">
                                    ${exam.status === 'in_progress' ? 'Sedang Berlangsung' :
                                      exam.status === 'waiting' ? 'Menunggu' : 'Selesai'}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="stopExam(${exam.exam_id})" 
                                        class="text-red-600 hover:text-red-900">
                                    Stop Ujian
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            })
            .catch(error => {
                console.error("Active Exams API Error:", error);
                document.getElementById('activeExamsBody').innerHTML = `
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-red-500">
                            Gagal memuat data ujian aktif
                        </td>
                    </tr>
                `;
            });
        }

        // Refresh data every 30 seconds
        fetchDashboardStats();
        setInterval(fetchDashboardStats, 30000);

        // Function to stop exam (to be implemented)
        function stopExam(examId) {
            if (confirm('Apakah Anda yakin ingin menghentikan ujian ini?')) {
                // TODO: Implement exam stop functionality
                console.log('Stopping exam:', examId);
            }
        }
    </script>
@endpush
