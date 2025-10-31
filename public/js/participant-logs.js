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

    // Add event listener for detail buttons
    document.addEventListener('click', function(event) {
        if (event.target.matches('button[data-tab="detaillog"]')) {
            const sessionId = event.target.getAttribute('data-id');
            window.location.href = `/admin/exam-session/${sessionId}`;
        }
    });

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
                    <button type="button" data-tab="detaillog" data-id="${participant.exam_session_id}"
                        class="px-3 py-1 text-sm text-blue-600 hover:text-blue-900 hover:underline focus:outline-none">
                        Lihat Detail
                    </button>
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
});
