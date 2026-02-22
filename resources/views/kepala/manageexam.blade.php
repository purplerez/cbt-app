<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Manajemen Ujian - ') . session('exam_name') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex">
                        <!-- Sidebar Menu -->
                        <div class="w-1/4 pr-6">
                            <div class="p-4 bg-white rounded-lg shadow">
                                <div class="flex items-center mb-4 space-x-4">
                                    <div>
                                        <h3 class="text-lg font-medium">MATA PELAJARAN</h3>
                                    </div>
                                </div>

                                <nav class="space-y-2">
                                    @forelse ($mapels as $mapel)
                                        <button type="button" data-exam-id="{{ $mapel->id }}" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md mapel-btn hover:bg-gray-100">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2 4 4 8-8 4 4v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V12z"/>
                                            </svg>
                                            {{ $mapel->title }}
                                        </button>
                                    @empty
                                        <div class="px-4 py-2 text-sm text-gray-500">Tidak ada mata pelajaran.</div>
                                    @endforelse
                                </nav>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="w-3/4">
                            <div class="bg-white rounded-lg shadow">
                                <div class="flex items-center justify-between p-4 border-b">
                                    <h3 class="text-lg font-medium">Daftar Nilai Siswa</h3>
                                    <div class="flex items-center space-x-4">
                                        <select id="gradeFilter" class="block w-48 px-3 py-2 text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                            <option value="">Semua Kelas</option>
                                            @foreach($grades as $grade)
                                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                        @endforeach
                                        </select>
                                        <button id="exportPdfBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                            Export PDF
                                        </button>
                                    </div>
                                </div>

                                <div class="p-4">
                                    <div class="overflow-x-auto">
                                        <table id="scoresTable" class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">No</th>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">NIS</th>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Siswa</th>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nilai</th>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Total Skor</th>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Persentase</th>
                                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="scoresBody" class="bg-white divide-y divide-gray-200">
                                                <tr>
                                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">Pilih mata pelajaran di sebelah kiri untuk menampilkan daftar nilai.</td>
                                                </tr>
                                            </tbody>
                                            {{-- untuk detail button:
                                            <button onclick="showDetail('${s.id}')" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                                                Detail
                                            </button --}}
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    {{-- END OF MAIN CONTENT --}}
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
let currentExamId = null;
let currentSessions = [];
const schoolId = '{{ session("school_id") }}'; // Get school_id from PHP session

document.addEventListener('DOMContentLoaded', function() {
    // Add grade filter change handler
    const gradeFilter = document.getElementById('gradeFilter');
    if (gradeFilter) {
        gradeFilter.addEventListener('change', function() {
            if (currentExamId) {
                fetchScores(currentExamId);
            }
        });
    }

    // Add export PDF button handler
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function() {
            if (!currentExamId) {
                alert('Pilih mata pelajaran terlebih dahulu');
                return;
            }
            const gradeId = document.getElementById('gradeFilter').value;
            const url = `/kepala/exams/${currentExamId}/export-scores?grade_id=${gradeId || ''}`;
            window.location.href = url;
        });
    }

    // Attach click handlers for mapel buttons
    document.querySelectorAll('.mapel-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const examId = this.dataset.examId;

            // mark active
            document.querySelectorAll('.mapel-btn').forEach(b => b.classList.remove('bg-gray-100'));
            this.classList.add('bg-gray-100');

            if (examId) {
                fetchScores(examId);
            }
        });
    });

    // Show first exam automatically if exists
    const firstBtn = document.querySelector('.mapel-btn');
    if (firstBtn) {
        firstBtn.click();
    }

    function fetchScores(examId) {
        currentExamId = examId;
        const gradeId = document.getElementById('gradeFilter').value;
        const url = `/kepala/exams/${examId}/scores${gradeId ? `?grade_id=${gradeId}` : ''}`;
        const scoresBody = document.getElementById('scoresBody');
        if (!scoresBody) return;
        scoresBody.innerHTML = `<tr><td colspan="8" class="px-6 py-4 text-center text-gray-500">Memuat...</td></tr>`;

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(resp => resp.json())
            .then(data => {
                if (data.error) {
                    scoresBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">${data.error}</td></tr>`;
                    return;
                }
                const sessions = data.sessions || [];
                currentSessions = sessions; // Store for export
                if (!sessions.length) {
                    scoresBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada sesi untuk mata pelajaran ini.</td></tr>`;
                    return;
                }
                scoresBody.innerHTML = '';
                sessions.forEach((s, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">${idx+1}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">${s.nis ?? '-'}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">${s.student_name ?? '-'}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">${s.total_score ?? '-'}</td>
                        <td class="px-6 py-4 text-sm text-center text-gray-900 whitespace-nowrap">${s.total_score ?? '-'}/${s.total_possible ?? '-'}</td>
                        <td class="px-6 py-4 text-sm text-right text-gray-900 whitespace-nowrap">${s.percentage ?? '-'}%</td>
                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                            <button onclick="showDetail('${s.id}')" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
                                Detail
                            </button>
                            ${(s.is_active === 0)
                                    ? `<button onclick="resetLogin('${s.id}')" class="px-3 py-1 text-sm text-white bg-red-600 rounded hover:bg-red-700">Reset Login</button>`
                                    : ''}
                        </td>
                    `;
                    scoresBody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error(err);
                scoresBody.innerHTML = `<tr><td colspan="7" class="px-6 py-4 text-center text-red-500">Terjadi kesalahan saat memuat data.</td></tr>`;
            });
    }

});

// Define showDetail function globally
function showDetail(sessionId) {
    window.location.href = `/kepala/exam-sessions/${sessionId}/detail`;
}
</script>
@endpush

</x-app-layout>

