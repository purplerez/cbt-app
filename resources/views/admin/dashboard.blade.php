<x-app-layout>
    {{-- <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard Admin</h2>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span id="last-updated">—</span>
                <button onclick="refreshAll()" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </button>
            </div>
        </div>
    </x-slot> --}}

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- ── STAT CARDS ── --}}
            {{-- <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Sekolah</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-schools">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-active-schools">— aktif</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-students">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-online-pct">— sedang ujian</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ujian Berlangsung</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-active-exams">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-in-exam">— siswa aktif</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Submit Hari Ini</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-submitted-today">—</p>
                    <p class="text-xs text-gray-400 mt-1">sesi selesai hari ini</p>
                </div>
            </div> --}}

            {{-- ── LIVE MONITORING PANEL ── --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <h3 class="text-base font-semibold text-gray-800">Monitoring Ujian Live</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <select id="exam-select" onchange="onExamSelectChange()" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 flex-1 max-w-[200px]">
                            <option value="">— Pilih Ujian —</option>
                        </select>
                        <select id="school-select" onchange="loadMonitor()" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50 flex-1 max-w-[200px]" disabled>
                            <option value="">— Semua Sekolah —</option>
                        </select>

                        <span class="hidden sm:flex items-center gap-2 text-xs" id="monitor-counts">
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium" id="cnt-progress">0 Live</span>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium" id="cnt-submited">0 Submit</span>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" id="cnt-not-started">0 Belum</span>
                        </span>
                    </div>
                </div> --}}

                {{-- <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sekolah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sisa Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nilai</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="monitor-body" class="bg-white divide-y divide-gray-50">
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Pilih ujian untuk memulai monitoring</td>
                            </tr>
                        </tbody>
                    </table>
                </div> --}}
            </div>

            {{-- ── RECENT ACTIVITY LOG ── --}}
            {{-- <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Log Aktivitas Terbaru</h3>
                </div>
                <div class="divide-y divide-gray-50" id="activity-log">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">Memuat log...</div>
                </div>
            </div> --}}

        </div>
    </div>

    {{-- ── RESET LOGIN MODAL ── --}}
    <div id="reset-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h4 class="text-base font-semibold text-gray-800 mb-2">Reset Login Siswa</h4>
            <p class="text-sm text-gray-600 mb-1">Yakin ingin mereset login untuk:</p>
            <p class="text-sm font-semibold text-gray-900 mb-1" id="modal-student-name">—</p>
            <p class="text-xs text-gray-500 mb-4">Semua token login siswa akan dihapus. Siswa harus login ulang, namun jawaban ujian tetap tersimpan.</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeResetModal()" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button onclick="confirmReset()" id="confirm-reset-btn" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Reset Login</button>
            </div>
        </div>
    </div>

    {{-- ── FORCE SUBMIT MODAL ── --}}
    <div id="force-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h4 class="text-base font-semibold text-gray-800 mb-2">Force Submit Ujian</h4>
            <p class="text-sm text-gray-600 mb-1">Yakin ingin paksa submit ujian untuk:</p>
            <p class="text-sm font-semibold text-gray-900 mb-4" id="modal-force-name">—</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeForceModal()" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button onclick="confirmForce()" id="confirm-force-btn" class="px-4 py-2 text-sm bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">Force Submit</button>
            </div>
        </div>
    </div>

    {{-- ── DELETE SESSION MODAL ── --}}
    <div id="delete-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h4 class="text-base font-semibold text-gray-800 mb-2">Hapus Sesi Ujian</h4>
            <p class="text-sm text-gray-600 mb-1">Yakin ingin menghapus sesi ujian untuk:</p>
            <p class="text-sm font-semibold text-gray-900 mb-4" id="modal-delete-name">—</p>
            <p class="text-xs text-red-500 mb-4 bg-red-50 p-2 rounded border border-red-100">Peringatan: Seluruh data jawaban siswa pada sesi ini akan ikut terhapus.</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeDeleteModal()" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button onclick="confirmDelete()" id="confirm-delete-btn" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Hapus Sesi</button>
            </div>
        </div>
    </div>

@push('scripts')
<script>
const API = window.apiToken;
const headers = { 'Authorization': 'Bearer ' + API, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

let currentStudentNis = null;
let currentSessionId  = null;
let currentStudentName = '';
let monitorInterval = null;

// ── STATS ──────────────────────────────────────────────────────────
async function fetchStats() {
    try {
        const r = await fetch('/api/admin/dashboard/stats', { headers });
        const d = await r.json();
        if (d.error) return;
        document.getElementById('stat-schools').textContent       = d.total_schools ?? '—';
        document.getElementById('stat-active-schools').textContent = (d.active_school_count ?? '—') + ' aktif';
        document.getElementById('stat-students').textContent      = (d.total_students ?? '—').toLocaleString();
        document.getElementById('stat-online-pct').textContent    = (d.students_in_exam ?? '—') + ' sedang ujian';
        document.getElementById('stat-active-exams').textContent  = d.active_exams ?? '—';
        document.getElementById('stat-in-exam').textContent       = (d.students_in_exam ?? '—') + ' siswa aktif';
        document.getElementById('stat-submitted-today').textContent = d.submitted_today ?? '—';
        document.getElementById('last-updated').textContent       = 'Update: ' + new Date().toLocaleTimeString('id-ID');
    } catch(e) { console.error('Stats error:', e); }
}

// ── EXAM DROPDOWN ──────────────────────────────────────────────────
async function fetchActiveExams() {
    try {
        const r = await fetch('/api/admin/dashboard/active-exams', { headers });
        const text = await r.text();
        let list;
        try { list = JSON.parse(text); } catch(e) { console.error('Active Exams JSON Error:', text.substring(0, 150)); return; }
        if (list && list.error) return;
        if (!Array.isArray(list)) list = Object.values(list || {});

        const sel = document.getElementById('exam-select');
        const cur = sel.value;
        sel.innerHTML = '<option value="">— Pilih Ujian —</option>';
        (list || []).forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.exam_id;
            opt.textContent = e.name;
            sel.appendChild(opt);
        });
        if (cur) sel.value = cur;
    } catch(e) { console.error('Exams error:', e); }
}

function onExamSelectChange() {
    const examId = document.getElementById('exam-select').value;
    const schoolSel = document.getElementById('school-select');

    // Reset school select content & load new
    schoolSel.innerHTML = '<option value="">— Semua Sekolah —</option>';
    if (!examId) {
        schoolSel.disabled = true;
        loadMonitor();
        return;
    }
    schoolSel.disabled = false;
    fetchSchoolsForExam(examId);
    loadMonitor();
}

async function fetchSchoolsForExam(examId) {
    try {
        const r = await fetch(`/api/admin/dashboard/exams/${examId}/schools`, { headers });
        const text = await r.text();
        let list;
        try { list = JSON.parse(text); } catch(e) { console.error('Schools JSON Error:', text.substring(0, 150)); return; }
        if (list && list.error) return;
        if (!Array.isArray(list)) list = [];

        const sel = document.getElementById('school-select');
        (list || []).forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name;
            sel.appendChild(opt);
        });
    } catch(e) { console.error('Schools fetch error:', e); }
}

function loadMonitor() {
    const examId = document.getElementById('exam-select').value;
    if (!examId) {
        document.getElementById('monitor-body').innerHTML =
            '<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Pilih ujian untuk memulai monitoring</td></tr>';
        return;
    }
    clearInterval(monitorInterval);
    fetchMonitor(examId);
    monitorInterval = setInterval(() => fetchMonitor(examId), 30000);
}

async function fetchMonitor(examId) {
    try {
        document.getElementById('monitor-body').innerHTML =
            '<tr><td colspan="7" class="px-4 py-6 text-center text-gray-400 text-xs">Memuat data...</td></tr>';
        const schoolQuery = document.getElementById('school-select').value ? `?school_id=${document.getElementById('school-select').value}` : '';
        const r = await fetch(`/api/admin/monitor/exam/${examId}/participants${schoolQuery}`, { headers });
        const text = await r.text();
        let res;
        try { res = JSON.parse(text); } catch(e) { throw new Error('Data server tidak valid (Gagal parse JSON). Cek logs koneksi server.'); }


        if (!res.success) throw new Error(res.error);

        // Update count badges
        document.getElementById('cnt-progress').textContent    = (res.counts.progress ?? 0) + ' Live';
        document.getElementById('cnt-submited').textContent    = (res.counts.submited ?? 0) + ' Submit';
        document.getElementById('cnt-not-started').textContent = (res.counts.not_started ?? 0) + ' Belum';

        const tbody = document.getElementById('monitor-body');
        if (!res.data || res.data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada peserta terdaftar.</td></tr>';
            return;
        }

        tbody.innerHTML = res.data.map(p => {
            const statusBadge = {
                'progress':    '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>Live</span>',
                'submited':    '<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">✓ Submit</span>',
                'not_started': '<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-medium">Belum</span>',
            }[p.status] ?? `<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs">${p.status}</span>`;

            const timeDisplay = p.status === 'progress'
                ? `<span class="font-mono text-sm text-orange-600" data-seconds="${p.time_remaining}">${formatTime(p.time_remaining)}</span>`
                : (p.status === 'submited' ? '<span class="text-blue-500 text-xs">—</span>' : '<span class="text-gray-400 text-xs">Belum mulai</span>');

            const score = p.score !== null ? `<span class="font-semibold text-green-700">${parseFloat(p.score).toFixed(2)}</span>` : '—';

            const actions = `
                <div class="flex gap-1.5">
                    ${p.session_id ? `<a href="/admin/exam-sessions/${p.session_id}/detail" class="px-2 py-1 text-xs bg-indigo-50 text-indigo-600 rounded hover:bg-indigo-100 transition">Detail</a>` : ''}
                    ${parseInt(p.is_active) === 1 ? `<button onclick="openResetModal('${p.nis}','${p.name.replace(/'/g,"\\'")}','${p.user_id}')"
                        class="px-2 py-1 text-xs bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition">Reset</button>` : ''}
                    ${p.session_id && p.status === 'progress' ? `<button onclick="openForceModal(${p.session_id},'${p.name.replace(/'/g,"\\'")}')"
                        class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100 transition">Force</button>` : ''}
                    ${p.session_id ? `<button onclick="openDeleteModal(${p.session_id},'${p.name.replace(/'/g,"\\'")}')"
                        class="px-2 py-1 text-xs bg-red-50 text-red-600 rounded hover:bg-red-100 transition">Delete</button>` : ''}
                </div>`;

            return `<tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800 text-sm">${escHtml(p.name)}</div>
                    <div class="text-xs text-gray-400">${escHtml(p.nis)}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${escHtml(p.grade)}</td>
                <td class="px-4 py-3 text-xs text-gray-500">${escHtml(p.school || '—')}</td>
                <td class="px-4 py-3">${statusBadge}</td>
                <td class="px-4 py-3">${timeDisplay}</td>
                <td class="px-4 py-3">${score}</td>
                <td class="px-4 py-3">${actions}</td>
            </tr>`;
        }).join('');

        // Tick down timers every second
        startTimerTick();
    } catch(e) {
        document.getElementById('monitor-body').innerHTML =
            `<tr><td colspan="7" class="px-4 py-6 text-center text-red-400 text-sm">Gagal memuat: ${e.message}</td></tr>`;
    }
}

// ── TIMER TICK ─────────────────────────────────────────────────────
let timerTick = null;
function startTimerTick() {
    clearInterval(timerTick);
    timerTick = setInterval(() => {
        document.querySelectorAll('[data-seconds]').forEach(el => {
            let s = parseInt(el.dataset.seconds) - 1;
            if (s < 0) s = 0;
            el.dataset.seconds = s;
            el.textContent = formatTime(s);
            if (s === 0) el.classList.replace('text-orange-600', 'text-red-600');
        });
    }, 1000);
}

function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}

// ── ACTIVITY LOG ───────────────────────────────────────────────────
async function fetchActivityLog() {
    // Placeholder — show if ActivityLog model/table exists
    try {
        const r = await fetch('/api/admin/activity-log', { headers });
        if (!r.ok) throw new Error();
        const logs = await r.json();
        const el = document.getElementById('activity-log');
        if (!logs.length) {
            el.innerHTML = '<div class="px-5 py-8 text-center text-gray-400 text-sm">Belum ada aktivitas.</div>';
            return;
        }
        el.innerHTML = logs.slice(0,10).map(l => `
            <div class="px-5 py-3 flex items-start gap-3">
                <div class="w-2 h-2 mt-1.5 rounded-full bg-indigo-400 flex-shrink-0"></div>
                <div>
                    <p class="text-sm text-gray-700">${escHtml(l.activity)}</p>
                    <p class="text-xs text-gray-400 mt-0.5">${l.created_at ?? ''}</p>
                </div>
            </div>`).join('');
    } catch(e) {
        document.getElementById('activity-log').innerHTML =
            '<div class="px-5 py-8 text-center text-gray-400 text-sm">Log tidak tersedia.</div>';
    }
}

// ── RESET LOGIN ────────────────────────────────────────────────────
function openResetModal(nis, name) {
    currentStudentNis  = nis;
    currentStudentName = name;
    document.getElementById('modal-student-name').textContent = name + ' (NIS: ' + nis + ')';
    document.getElementById('reset-modal').classList.remove('hidden');
}
function closeResetModal() { document.getElementById('reset-modal').classList.add('hidden'); }

async function confirmReset() {
    const btn = document.getElementById('confirm-reset-btn');
    btn.disabled = true; btn.textContent = 'Mereset...';
    try {
        const r = await fetch(`/admin/exam-sessions/${currentStudentNis}/reset`, {
            method: 'POST',
            headers: { ...headers, 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        });
        const res = await r.json();
        closeResetModal();
        showToast(res.success ? 'success' : 'error', res.message);
    } catch(e) {
        showToast('error', 'Gagal mereset login.');
    } finally {
        btn.disabled = false; btn.textContent = 'Reset Login';
    }
}

// ── FORCE SUBMIT ───────────────────────────────────────────────────
function openForceModal(sessionId, name) {
    currentSessionId   = sessionId;
    currentStudentName = name;
    document.getElementById('modal-force-name').textContent = name;
    document.getElementById('force-modal').classList.remove('hidden');
}
function closeForceModal() { document.getElementById('force-modal').classList.add('hidden'); }

async function confirmForce() {
    const btn = document.getElementById('confirm-force-btn');
    btn.disabled = true; btn.textContent = 'Memproses...';
    try {
        const r = await fetch(`/admin/exam-sessions/${currentSessionId}/force-submit`, {
            method: 'POST',
            headers: { ...headers, 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        });
        const res = await r.json();
        closeForceModal();
        showToast(res.success ? 'success' : 'error', res.message);
        if (res.success) loadMonitor();
    } catch(e) {
        showToast('error', 'Gagal force submit.');
    } finally {
        btn.disabled = false; btn.textContent = 'Force Submit';
    }
}

// ── DELETE SESSION ─────────────────────────────────────────────────
function openDeleteModal(sessionId, name) {
    currentSessionId   = sessionId;
    currentStudentName = name;
    document.getElementById('modal-delete-name').textContent = name;
    document.getElementById('delete-modal').classList.remove('hidden');
}
function closeDeleteModal() { document.getElementById('delete-modal').classList.add('hidden'); }

async function confirmDelete() {
    const btn = document.getElementById('confirm-delete-btn');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    try {
        const r = await fetch(`/admin/exam-sessions/${currentSessionId}`, {
            method: 'DELETE',
            headers: { ...headers, 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json' }
        });
        const res = await r.json();
        closeDeleteModal();
        showToast(res.success ? 'success' : 'error', res.message);
        if (res.success) loadMonitor();
    } catch(e) {
        showToast('error', 'Gagal menghapus sesi.');
    } finally {
        btn.disabled = false; btn.textContent = 'Hapus Sesi';
    }
}

// ── HELPERS ────────────────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '—';
    return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(type, msg) {
    const colors = { success: 'bg-green-500', error: 'bg-red-500' };
    const t = document.createElement('div');
    t.className = `fixed bottom-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg text-sm ${colors[type] ?? 'bg-gray-700'} transition-opacity`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 3500);
}

function refreshAll() {
    fetchStats();
    fetchActiveExams();
    const examId = document.getElementById('exam-select').value;
    if (examId) fetchMonitor(examId);
    fetchActivityLog();
}

// ── INIT ───────────────────────────────────────────────────────────
fetchStats();
fetchActiveExams();
fetchActivityLog();
setInterval(() => { fetchStats(); fetchActiveExams(); }, 30000);
</script>
@endpush
</x-app-layout>
