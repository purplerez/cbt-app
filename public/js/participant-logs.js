// IMMEDIATE DEBUG - run before anything else
console.log('📌 participant-logs.js file loaded!');

document.addEventListener('DOMContentLoaded', function () {
    console.log('=== PARTICIPANT LOGS SCRIPT STARTED ===');

    const schoolDropdown = document.getElementById('school_filter_logs');
    const examIdElement = document.getElementById('current-exam-id');

    console.log('✅ schoolDropdown found:', !!schoolDropdown, schoolDropdown);
    console.log('✅ examIdElement found:', !!examIdElement, examIdElement);

    if (!examIdElement) {
        console.error('❌ Exam ID element not found - stopping script');
        return;
    }

    const examId = examIdElement.value;
    console.log('✅ Exam ID value:', examId);

    if (!examId) {
        console.error('❌ Exam ID is empty - stopping script');
        return;
    }

    // Initialize the view
    console.log('🔄 Starting to fetch data...');
    if (schoolDropdown) {
        console.log('✅ School dropdown found, fetching data...');
        fetchStats();
        fetchParticipants();

        // Add event listener for school filter
        schoolDropdown.addEventListener('change', function () {
            console.log('📌 School filter changed');
            fetchStats();
            fetchParticipants();
        });
    } else {
        console.log('⚠️ School dropdown NOT found, but fetching data anyway...');
        // still attempt to load even if dropdown is missing
        fetchStats();
        fetchParticipants();
    }

    // Add event listener for detail buttons
    document.addEventListener('click', function (event) {
        if (event.target.matches('button[data-tab="detaillog"]')) {
            const sessionId = event.target.getAttribute('data-id');
            const url = window.examSessionDetailUrl.replace(':id', sessionId);
            window.location.href = url;
        }
    });

    function getApiToken() {
        const tokenMeta = document.querySelector('meta[name="api_token"]');
        if (tokenMeta) {
            const token = tokenMeta.getAttribute('content');
            if (token && token.trim() !== '') {
                return token;
            }
        }

        if (window.apiToken && window.apiToken.trim() !== '') {
            return window.apiToken;
        }

        return null;
    }

    function buildFetchOptions(token) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        // Attach CSRF if present
        const csrf = document.querySelector('meta[name="csrf-token"]');
        if (csrf && csrf.content) headers['X-CSRF-TOKEN'] = csrf.content;

        if (token) headers['Authorization'] = `Bearer ${token}`;

        return {
            method: 'GET',
            headers: headers,
            credentials: 'same-origin' // allow session cookies
        };
    }

    function fetchStats() {
        const schoolId = schoolDropdown ? schoolDropdown.value : '';
        const token = getApiToken();
        const statsUrl = `/api/admin/exam/${examId}/stats${schoolId ? `?school_id=${schoolId}` : ''}`;

        console.debug('Fetching stats', statsUrl, { tokenPresent: !!token });
        fetch(statsUrl, buildFetchOptions(token))
            .then(response => response.json())
            .then(response => {
                if (response && response.success) {
                    updateStats(response.data);
                } else {
                    console.warn('Unexpected stats response', response);
                }
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
            });
    }

    function fetchParticipants() {
        const schoolId = schoolDropdown ? schoolDropdown.value : '';
        const token = getApiToken();
        const url = `/api/admin/exam/${examId}/participants` + (schoolId ? `?school_id=${schoolId}` : '');

        console.log('🔄 Fetching participants from:', url);
        fetch(url, buildFetchOptions(token))
            .then(response => {
                console.log('📡 API Response status:', response.status);
                return response.json();
            })
            .then(response => {
                console.log('✅ API Response:', response);
                if (response && response.success) {
                    // response.data may be a paginator or array
                    let participants = [];
                    if (Array.isArray(response.data)) participants = response.data;
                    else if (response.data && Array.isArray(response.data.data)) participants = response.data.data;
                    else if (Array.isArray(response)) participants = response;

                    console.log('📊 Participants count:', participants.length);
                    updateParticipantTable(participants);
                } else {
                    console.warn('❌ API response not success, trying fallback', response);
                    fetchParticipantsFallback(schoolId);
                }
            })
            .catch(error => {
                console.error('❌ API fetch error, trying fallback:', error);
                fetchParticipantsFallback(schoolId);
            });
    }

    function fetchParticipantsFallback(schoolId) {
        const fallbackUrl = `/admin/exam/${examId}/participants-json` + (schoolId ? `?school_id=${schoolId}` : '');
        console.debug('Fetching participants fallback (web) ', fallbackUrl);
        fetch(fallbackUrl, { method: 'GET', credentials: 'same-origin', headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    const participants = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
                    updateParticipantTable(participants);
                } else {
                    console.warn('Fallback participants response not usable', data);
                    const tbody = document.getElementById('participant-logs-body');
                    if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data peserta</td></tr>';
                }
            })
            .catch(err => {
                console.error('Error fetching fallback participants:', err);
                const tbody = document.getElementById('participant-logs-body');
                if (tbody) tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data peserta</td></tr>';
            });
    }

    function updateStats(stats) {
        if (!stats) return;
        const totalEl = document.getElementById('total-participants');
        const activeEl = document.getElementById('active-participants');
        const submittedEl = document.getElementById('submitted-participants');
        if (totalEl) totalEl.textContent = stats.total_participants ?? 0;
        if (activeEl) activeEl.textContent = stats.active_participants ?? 0;
        if (submittedEl) submittedEl.textContent = stats.submitted_participants ?? 0;
    }

    function updateParticipantTable(participants) {
        const tbody = document.getElementById('participant-logs-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!participants || !participants.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada data peserta</td></tr>';
            return;
        }

        console.log('Rendering participants:', participants);

        participants.forEach((participant, index) => {
            const lastActivity = participant.last_activity || {};
            const row = document.createElement('tr');

            // Log participant data for debugging
            console.log(`Participant ${index}:`, {
                name: participant.name,
                status: lastActivity.status,
                sessionId: participant.exam_session_id,
                lastActivity: lastActivity
            });

            // Determine if force submit button should be shown
            // Show button if status exists AND is not 'submited' AND sessionId exists
            const hasSession = participant.exam_session_id && participant.exam_session_id !== '';
            const isNotSubmitted = !lastActivity.status || lastActivity.status !== 'submited';
            const showForceSubmit = hasSession && isNotSubmitted;

            let actionButtons = '';

            if (showForceSubmit) {
                // Show Force Submit + Detail for incomplete sessions
                const sessionId = participant.exam_session_id;
                actionButtons = `
                    <div class="flex items-center gap-2">
                        <button type="button" class="force-submit-btn px-3 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded transition" onclick="forceSubmitExam(${sessionId})" title="Paksa submit ujian ini">
                            Force Submit
                        </button>
                        <button type="button" data-tab="detaillog" data-id="${sessionId}" class="px-3 py-2 text-sm font-medium text-blue-600 border border-blue-300 hover:border-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">
                            Detail
                        </button>
                    </div>
                `;
            } else {
                // Show Detail only for completed sessions
                const sessionId = participant.exam_session_id || '';
                actionButtons = `<button type="button" data-tab="detaillog" data-id="${sessionId}" class="px-3 py-2 text-sm font-medium text-blue-600 border border-blue-300 hover:border-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200">Detail</button>`;
            }

            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-900">${participant.nis ?? '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${participant.name ?? '-'}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${participant.grade ?? '-'}</td>
                <td class="px-6 py-4 text-sm">${getStatusBadge(lastActivity.status)}</td>
                <td class="px-6 py-4 text-sm text-gray-900">
                    ${lastActivity.submit_time ? new Date(lastActivity.submit_time).toLocaleString('id-ID') : (lastActivity.start_time ? new Date(lastActivity.start_time).toLocaleString('id-ID') : '-')}
                </td>
                <td class="px-6 py-4 text-sm">
                    ${actionButtons}
                </td>
            `;

            tbody.appendChild(row);
        });

        // Attach force submit button handlers after rendering
        attachForceSubmitHandlers();
    }

    function getStatusBadge(status) {
        if (!status) return '<span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">Belum Mulai</span>';

        const badges = {
            'active': '<span class="px-2 py-1 text-xs text-green-600 bg-green-100 rounded">Aktif</span>',
            'submited': '<span class="px-2 py-1 text-xs text-blue-600 bg-blue-100 rounded">Selesai</span>',
            'timeout': '<span class="px-2 py-1 text-xs text-red-600 bg-red-100 rounded">Timeout</span>'
        };

        return badges[status] || '<span class="px-2 py-1 text-xs text-gray-600 bg-gray-100 rounded">Unknown</span>';
    }

    function forceSubmitExam(sessionId) {
        if (!confirm('Apakah Anda yakin ingin force submit ujian peserta ini?\n\nSistem akan menghitung skor berdasarkan jawaban yang sudah diisi.')) {
            return;
        }
        performForceSubmit(sessionId);
    }

    function performForceSubmit(sessionId) {
        const token = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = token ? token.content : '';

        fetch(`/admin/exam-sessions/${sessionId}/force-submit`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                exam_session_id: sessionId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Ujian berhasil di-force submit!\n\nSkor: ' + data.score + ' / ' + data.max_score + ' (' + data.percentage.toFixed(1) + '%)');
                fetchStats();
                fetchParticipants();
            } else {
                alert('✗ Gagal force submit:\n\n' + (data.message || 'Terjadi kesalahan'));
            }
        })
        .catch(error => {
            console.error('Force submit error:', error);
            alert('✗ Terjadi kesalahan: ' + error.message);
        });
    }
});

