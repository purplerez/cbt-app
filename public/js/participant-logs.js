document.addEventListener('DOMContentLoaded', function () {
    const schoolDropdown = document.getElementById('school_filter_logs');
    const examIdElement = document.getElementById('current-exam-id');

    if (!examIdElement) {
        console.error('Exam ID element not found');
        return;
    }

    const examId = examIdElement.value;
    console.log('Initializing participant logs with examId:', examId);

    // Initialize the view
    if (schoolDropdown) {
        fetchStats();
        fetchParticipants();

        // Add event listener for school filter
        schoolDropdown.addEventListener('change', function () {
            fetchStats();
            fetchParticipants();
        });
    }

    function getApiToken(){
        const tokenMeta = document.querySelector('meta[name="api_token"]');
        if (tokenMeta) {
            const token = tokenMeta.getAttribute('content');
            if (token && token.trim() !== '') {
                return token;
            }
        }

        if(window.apiToken && window.apiToken.trim() !== '') {
            return window.apiToken;
        }

        return null;
    }

    function fetchStats() {
        const schoolId = schoolDropdown.value;
        const token = getApiToken();

        if(!token) {
            console.error('No API token found');
            alert('Error: No API token found. Please login again.');
            return;
        }
        fetch(`/api/admin/exam/${examId}/stats${schoolId ? `?school_id=${schoolId}` : ''}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    updateStats(response.data);
                }
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
            });
    }

    function fetchParticipants() {
        const schoolId = schoolDropdown.value;
        const token = getApiToken();

        if(!token) {
            console.error('No API token found');
            alert('Error: No API token found. Please login again.');
            return;
        }

        fetch(`/api/admin/exam/${examId}/participants${schoolId ? `?school_id=${schoolId}` : ''}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    updateParticipantTable(response.data);
                }
            })
            .catch(error => {
                console.error('Error fetching participants:', error);
            });
    }

    function showDetailLog(userId) {
        const token = getApiToken();

        if(!token) {
            console.error('No API token found');
            alert('Error: No API token found. Please login again.');
            return;
        }

        // Show loading state
        const detailContainer = document.querySelector('#detaillog .p-4 .overflow-x-auto');
        if (detailContainer) {
            detailContainer.innerHTML = '<div class="text-center py-4">Memuat data...</div>';
        }

        fetch(`/api/admin/exam/${examId}/participant/${userId}/logs`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    renderDetailLog(response.data);
                } else {
                    throw new Error(response.message || 'Failed to fetch detail logs');
                }
            })
            .catch(error => {
                console.error('Error fetching detail logs:', error);
                if (detailContainer) {
                    detailContainer.innerHTML = `
                        <div class="text-center py-4 text-red-600">
                            Error: ${error.message || 'Gagal memuat data detail log'}
                        </div>
                    `;
                }
            });
    }

    function renderDetailLog(data) {
        const detailContainer = document.querySelector('#detaillog .p-4 .overflow-x-auto');
        if (!detailContainer) return;

        const participant = data.participant;
        const logs = data.logs;
        const answers = data.answers || [];

        let html = `
            <div class="mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="text-lg font-semibold text-blue-900 mb-2">Informasi Peserta</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-600">NIS:</span>
                            <p class="text-sm text-gray-900">${participant.nis}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Nama:</span>
                            <p class="text-sm text-gray-900">${participant.name}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Kelas:</span>
                            <p class="text-sm text-gray-900">${participant.grade}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Status:</span>
                            <p class="text-sm">${getStatusBadge(participant.status)}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Progress:</span>
                            <p class="text-sm text-gray-900">${participant.progress || 0}%</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-600">Nilai:</span>
                            <p class="text-sm text-gray-900">${participant.score || '-'}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Activity Logs -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Log Aktivitas</h4>
                    <div class="bg-white border rounded-lg overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Waktu
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aktivitas
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
        `;

        if (logs.length > 0) {
            logs.forEach(log => {
                html += `
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 whitespace-nowrap">
                            ${formatDateTime(log.created_at)}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            ${getActivityDescription(log)}
                        </td>
                    </tr>
                `;
            });
        } else {
            html += `
                <tr>
                    <td colspan="2" class="px-4 py-4 text-center text-gray-500">
                        Tidak ada log aktivitas
                    </td>
                </tr>
            `;
        }

        html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Answers Summary -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Jawaban</h4>
                    <div class="bg-white border rounded-lg overflow-hidden">
                        <div class="max-h-96 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            No. Soal
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jawaban
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
        `;

        if (answers.length > 0) {
            answers.forEach((answer, index) => {
                html += `
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900 whitespace-nowrap">
                            ${index + 1}
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-900">
                            ${answer.answer_text || '-'}
                        </td>
                        <td class="px-4 py-2 text-sm">
                            ${getAnswerStatusBadge(answer.is_correct)}
                        </td>
                    </tr>
                `;
            });
        } else {
            html += `
                <tr>
                    <td colspan="3" class="px-4 py-4 text-center text-gray-500">
                        Belum ada jawaban
                    </td>
                </tr>
            `;
        }

        html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-start">
                <button type="button"
                        onclick="showTab('logujian')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Kembali ke Log Ujian
                </button>
            </div>
        `;

        detailContainer.innerHTML = html;
    }

    function getActivityDescription(log) {
        const activityMap = {
            'exam_started': 'Memulai ujian',
            'question_viewed': `Melihat soal nomor ${log.question_number || ''}`,
            'answer_saved': `Menjawab soal nomor ${log.question_number || ''}`,
            'answer_changed': `Mengubah jawaban soal nomor ${log.question_number || ''}`,
            'exam_submitted': 'Menyelesaikan ujian',
            'exam_timeout': 'Waktu ujian habis',
            'browser_focus_lost': 'Kehilangan fokus browser (Alt+Tab)',
            'browser_focus_gained': 'Mendapatkan fokus browser kembali',
            'full_screen_exit': 'Keluar dari mode full screen',
            'full_screen_enter': 'Masuk ke mode full screen'
        };

        return activityMap[log.activity_type] || log.activity_type;
    }

    function getAnswerStatusBadge(isCorrect) {
        if (isCorrect === null) {
            return '<span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">Belum Dinilai</span>';
        }
        return isCorrect
            ? '<span class="px-2 py-1 text-xs text-green-600 bg-green-100 rounded">Benar</span>'
            : '<span class="px-2 py-1 text-xs text-red-600 bg-red-100 rounded">Salah</span>';
    }

    function updateStats(stats) {
        document.getElementById('total-participants').textContent = stats.total_participants;
        document.getElementById('active-participants').textContent = stats.active_participants;
        document.getElementById('submitted-participants').textContent = stats.submitted_participants;
    }

    function updateParticipantTable(participants) {
        const tbody = document.getElementById('participant-logs-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!participants.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data peserta</td></tr>';
            return;
        }

        participants.forEach(participant => {
            const lastActivity = participant.last_activity;
            const row = document.createElement('tr');

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-900">${participant.nis}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${participant.name}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${participant.grade}</td>
                <td class="px-6 py-4 text-sm">
                    ${getStatusBadge(lastActivity?.status)}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    ${lastActivity?.progress ? lastActivity.progress + '%' : '-'}
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                   <button type="button" data-tab="detaillog" data-id="${participant.user_id}" class="text-blue-600 hover:text-blue-900"> Lihat Detail</button>
                </td>
            `;

            tbody.appendChild(row);
        });
    }

    function getStatusBadge(status) {
        if (!status) return '<span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">Belum Mulai</span>';

        const badges = {
            'active': '<span class="px-2 py-1 text-xs text-green-600 bg-green-100 rounded">Aktif</span>',
            'submitted': '<span class="px-2 py-1 text-xs text-blue-600 bg-blue-100 rounded">Selesai</span>',
            'timeout': '<span class="px-2 py-1 text-xs text-red-600 bg-red-100 rounded">Timeout</span>'
        };

        return badges[status] || '<span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">Unknown</span>';
    }

    function formatDateTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // show tab
    window.showTab = function(tabId) {
        // Hide all tabs and remove active classes
        document.querySelectorAll('.tab-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        document.querySelectorAll('[data-tab]').forEach(tab => {
            tab.classList.remove('bg-gray-100');
            tab.classList.add('hover:bg-gray-200');
        });

        // Show selected tab and add active class
        const selectedTab = document.getElementById(tabId);
        const tabButton = document.querySelector(`[data-tab="${tabId}"]`);

        if (selectedTab && tabButton) {
            selectedTab.classList.remove('hidden');
            tabButton.classList.add('bg-gray-100');
            tabButton.classList.remove('hover:bg-gray-200');
        }
    }
});
