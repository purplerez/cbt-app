<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard Kepala Madrasah</h2>
                <p class="text-sm text-gray-500 mt-0.5" id="school-subtitle">Memuat data sekolah...</p>
            </div>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span id="last-updated">—</span>
                <button onclick="refreshAll()" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            {{-- ── STAT CARDS ── --}}
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-students">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-grades">— kelas</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sedang Ujian</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-online">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-online-pct">— % dari total</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ujian Aktif</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-active-exams">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-participants">— peserta aktif</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Ujian</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-total-exams">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-preassigned">— terdaftar</p>
                </div>
            </div>

            {{-- ── LIVE MONITORING PANEL ── --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                        </span>
                        <h3 class="text-base font-semibold text-gray-800">Monitoring Ujian Live</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <select id="exam-select" onchange="loadMonitor()" class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-gray-50">
                            <option value="">— Pilih Ujian —</option>
                        </select>
                        <span class="hidden sm:flex items-center gap-2 text-xs">
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium" id="cnt-progress">0 Live</span>
                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-medium" id="cnt-submited">0 Submit</span>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" id="cnt-not-started">0 Belum</span>
                        </span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Siswa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sisa Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nilai</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="monitor-body" class="bg-white divide-y divide-gray-50">
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">Pilih ujian untuk memulai monitoring</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── RECENT SCORES ── --}}
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-800">Ringkasan Nilai Ujian Terbaru</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ujian</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rata-rata</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Peserta</th>
                            </tr>
                        </thead>
                        <tbody id="scores-body" class="bg-white divide-y divide-gray-50">
                            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Memuat data nilai...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

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



@push('scripts')
<script>
const API = window.apiToken;
const HEADERS = { 'Authorization': 'Bearer ' + API, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
let currentNis = null, currentSessionId = null, monitorInterval = null, timerTick = null;

// ── STATS ──────────────────────────────────────────────────────────
async function fetchStats() {
    try {
        const r = await fetch('/api/kepala/dashboard/stats', { headers: HEADERS });
        const text = await r.text();
        let d;
        try { d = JSON.parse(text); } catch(e) { console.error('Stats Error:', text.substring(0,100)); return; }
        if (d && d.error) return;
        document.getElementById('school-subtitle').textContent     = d.school_name ?? '—';

        document.getElementById('stat-students').textContent       = d.total_students ?? '—';
        document.getElementById('stat-grades').textContent         = (d.total_grades ?? '—') + ' kelas';
        document.getElementById('stat-online').textContent         = d.online_students ?? '—';
        document.getElementById('stat-online-pct').textContent     = (d.online_percentage ?? '—') + '% dari total';
        document.getElementById('stat-active-exams').textContent   = d.active_exams ?? '—';
        document.getElementById('stat-participants').textContent   = (d.participant_count ?? '—') + ' peserta aktif';
        document.getElementById('stat-total-exams').textContent    = d.total_exams ?? '—';
        document.getElementById('stat-preassigned').textContent    = (d.total_preassigned ?? '—') + ' terdaftar';
        document.getElementById('last-updated').textContent        = 'Update: ' + new Date().toLocaleTimeString('id-ID');
    } catch(e) {}
}

// ── EXAM DROPDOWN (school-scoped) ──────────────────────────────────
async function fetchActiveExams() {
    try {
        const r = await fetch('/api/kepala/monitor/active-exams', { headers: HEADERS });
        const text = await r.text();
        let d;
        try { d = JSON.parse(text); } catch(e) { console.error('Active Exams Error:', text.substring(0,100)); return; }
        const list = Array.isArray(d) ? d : (d.data ?? []);
        const sel = document.getElementById('exam-select'), cur = sel.value;
        sel.innerHTML = '<option value="">— Pilih Ujian —</option>';
        list.forEach(e => { const o = document.createElement('option'); o.value = e.exam_id; o.textContent = e.name; sel.appendChild(o); });
        if (cur) sel.value = cur;
    } catch(e) {}
}

// ── MONITOR ────────────────────────────────────────────────────────
function loadMonitor() {
    const id = document.getElementById('exam-select').value;
    if (!id) { document.getElementById('monitor-body').innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">Pilih ujian untuk memulai monitoring</td></tr>'; return; }
    clearInterval(monitorInterval); fetchMonitor(id); monitorInterval = setInterval(() => fetchMonitor(id), 30000);
}

async function fetchMonitor(examId) {
    try {
        document.getElementById('monitor-body').innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400 text-xs">Memuat data...</td></tr>';
        const r = await fetch(`/api/kepala/monitor/exam/${examId}/participants`, { headers: HEADERS });
        const text = await r.text();
        let res;
        try { res = JSON.parse(text); } catch(e) { throw new Error('Data server tidak valid (Bukan JSON).'); }
        if (!res.success) throw new Error(res.error);
        document.getElementById('cnt-progress').textContent    = (res.counts.progress ?? 0) + ' Live';

        document.getElementById('cnt-submited').textContent    = (res.counts.submited ?? 0) + ' Submit';
        document.getElementById('cnt-not-started').textContent = (res.counts.not_started ?? 0) + ' Belum';
        if (!res.data || !res.data.length) { document.getElementById('monitor-body').innerHTML = '<tr><td colspan="6" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada peserta terdaftar.</td></tr>'; return; }
        document.getElementById('monitor-body').innerHTML = res.data.map(p => {
            const badge = { 'progress': '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>Live</span>', 'submited': '<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">✓ Submit</span>', 'not_started': '<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-medium">Belum</span>' }[p.status] ?? p.status;
            const timeD = p.status === 'progress' ? `<span class="font-mono text-sm text-orange-600" data-seconds="${p.time_remaining}">${fmtTime(p.time_remaining)}</span>` : '<span class="text-gray-400 text-xs">—</span>';
            const score = p.score !== null ? `<span class="font-semibold text-green-700">${parseFloat(p.score).toFixed(2)}</span>` : '—';
            return `<tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3"><div class="font-medium text-gray-800 text-sm">${esc(p.name)}</div><div class="text-xs text-gray-400">${esc(p.nis)}</div></td>
                <td class="px-4 py-3 text-sm text-gray-600">${esc(p.grade)}</td>
                <td class="px-4 py-3">${badge}</td>
                <td class="px-4 py-3">${timeD}</td>
                <td class="px-4 py-3">${score}</td>
                <td class="px-4 py-3"><div class="flex gap-1.5 cursor-default">
                    ${parseInt(p.is_active) === 0 ? `<button onclick="openReset('${p.nis}','${p.name.replace(/'/g,"\\'")}')" class="px-2 py-1 text-xs bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition cursor-pointer">Reset</button>` : ''}
                </div></td>
            </tr>`;
        }).join('');
        startTick();
    } catch(e) { document.getElementById('monitor-body').innerHTML = `<tr><td colspan="6" class="px-4 py-6 text-center text-red-400 text-sm">${e.message}</td></tr>`; }
}

// ── RECENT SCORES ──────────────────────────────────────────────────
async function fetchRecentScores() {
    try {
        const r = await fetch('/api/kepala/dashboard/recent-scores', { headers: HEADERS });
        const text = await r.text();
        let d;
        try { d = JSON.parse(text); } catch(e) { console.error('Scores error:', text.substring(0,100)); return; }
        const list = Array.isArray(d) ? d : [];

        const tbody = document.getElementById('scores-body');
        if (!list.length) { tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada ujian yang selesai.</td></tr>'; return; }
        tbody.innerHTML = list.slice(0,8).map(s => `
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 text-sm font-medium text-gray-800">${esc(s.exam_name)}</td>
                <td class="px-4 py-3 text-sm text-gray-600">${esc(s.grade_name)}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-200 rounded-full h-1.5 flex-shrink-0">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width:${Math.min(100,Math.round(s.percentage))}%"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-800">${s.average_score}</span>
                        <span class="text-xs text-gray-400">(${s.percentage}%)</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${s.participant_count} siswa</td>
            </tr>`).join('');
    } catch(e) { document.getElementById('scores-body').innerHTML = '<tr><td colspan="4" class="px-4 py-6 text-center text-gray-400 text-sm">Gagal memuat nilai.</td></tr>'; }
}

// ── TIMERS ─────────────────────────────────────────────────────────
function startTick() { clearInterval(timerTick); timerTick = setInterval(() => { document.querySelectorAll('[data-seconds]').forEach(el => { let s = Math.max(0,parseInt(el.dataset.seconds)-1); el.dataset.seconds=s; el.textContent=fmtTime(s); }); }, 1000); }
function fmtTime(s) { return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`; }

// ── MODALS ─────────────────────────────────────────────────────────
function openReset(nis, name)      { currentNis=nis; document.getElementById('modal-student-name').textContent=name+' (NIS: '+nis+')'; document.getElementById('reset-modal').classList.remove('hidden'); }
function closeResetModal()          { document.getElementById('reset-modal').classList.add('hidden'); }

async function confirmReset() {
    const btn = document.getElementById('confirm-reset-btn'); btn.disabled=true; btn.textContent='Mereset...';
    try {
        const r = await fetch(`/kepala/exam-sessions/${currentNis}/reset`, { method:'POST', headers:{...HEADERS,'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'} });
        const res = await r.json(); closeResetModal(); toast(res.success?'success':'error', res.message);
    } catch(e) { toast('error','Gagal mereset login.'); } finally { btn.disabled=false; btn.textContent='Reset Login'; }
}



// ── HELPERS ────────────────────────────────────────────────────────
function esc(str) { if(!str) return '—'; return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function toast(type, msg) { const t=document.createElement('div'); t.className=`fixed bottom-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg text-sm ${type==='success'?'bg-green-500':'bg-red-500'}`; t.textContent=msg; document.body.appendChild(t); setTimeout(()=>{t.style.opacity='0'; setTimeout(()=>t.remove(),300);},3500); }

// ── INIT ───────────────────────────────────────────────────────────
function refreshAll() { fetchStats(); fetchActiveExams(); const id=document.getElementById('exam-select').value; if(id) fetchMonitor(id); fetchRecentScores(); }
fetchStats(); fetchActiveExams(); fetchRecentScores();
setInterval(()=>{ fetchStats(); fetchActiveExams(); }, 30000);
</script>
@endpush
</x-app-layout>
