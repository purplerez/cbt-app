<x-app-layout>
    {{-- <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard Super</h2>
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
        {{-- <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">

            <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                Mode Supervisor — dapat memonitor ujian dan mereset login siswa.
            </div>

            <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-indigo-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Sekolah</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-schools">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-active-schools">— aktif</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Siswa</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-students">—</p>
                    <p class="text-xs text-gray-400 mt-1" id="stat-in-exam">— sedang ujian</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ujian Berlangsung</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-active-exams">—</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-5 border-l-4 border-orange-500">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Submit Hari Ini</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900" id="stat-submitted-today">—</p>
                </div>
            </div>

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
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sekolah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Sisa Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nilai</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="monitor-body" class="bg-white divide-y divide-gray-50">
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Pilih ujian untuk memulai monitoring</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div> --}}
    </div>

    <div id="reset-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/40">
        {{-- <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h4 class="text-base font-semibold text-gray-800 mb-2">Reset Login Siswa</h4>
            <p class="text-sm text-gray-600 mb-1">Yakin ingin mereset login untuk:</p>
            <p class="text-sm font-semibold text-gray-900 mb-4" id="modal-student-name">—</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeResetModal()" class="px-4 py-2 text-sm border border-gray-200 rounded-lg hover:bg-gray-50 transition">Batal</button>
                <button onclick="confirmReset()" id="confirm-reset-btn" class="px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Reset Login</button>
            </div>
        </div> --}}
    </div>

@push('scripts')
{{-- ALL JAVASCRIPT COMMENTED OUT TO REDUCE CPU USAGE --}}
<script>
/*
const API = window.apiToken;
const headers = { 'Authorization': 'Bearer ' + API, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
let currentStudentNis = null, monitorInterval = null;

async function fetchStats() {
    try {
        const r = await fetch('/api/admin/dashboard/stats', { headers });
        const d = await r.json();
        if (d.error) return;
        document.getElementById('stat-schools').textContent        = d.total_schools ?? '—';
        document.getElementById('stat-active-schools').textContent  = (d.active_school_count ?? '—') + ' aktif';
        document.getElementById('stat-students').textContent       = (d.total_students ?? '—').toLocaleString();
        document.getElementById('stat-in-exam').textContent        = (d.students_in_exam ?? '—') + ' sedang ujian';
        document.getElementById('stat-active-exams').textContent   = d.active_exams ?? '—';
        document.getElementById('stat-submitted-today').textContent = d.submitted_today ?? '—';
        document.getElementById('last-updated').textContent        = 'Update: ' + new Date().toLocaleTimeString('id-ID');
    } catch(e) {}
}

async function fetchActiveExams() {
    try {
        const r = await fetch('/api/admin/dashboard/active-exams', { headers });
        const list = await r.json();
        const sel = document.getElementById('exam-select'), cur = sel.value;
        sel.innerHTML = '<option value="">— Pilih Ujian —</option>';
        (list || []).forEach(e => { const o = document.createElement('option'); o.value = e.exam_id; o.textContent = e.name; sel.appendChild(o); });
        if (cur) sel.value = cur;
    } catch(e) {}
}

function loadMonitor() {
    const id = document.getElementById('exam-select').value;
    if (!id) { document.getElementById('monitor-body').innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Pilih ujian untuk memulai monitoring</td></tr>'; return; }
    clearInterval(monitorInterval); fetchMonitor(id); monitorInterval = setInterval(() => fetchMonitor(id), 30000);
}

async function fetchMonitor(examId) {
    try {
        document.getElementById('monitor-body').innerHTML = '<tr><td colspan="7" class="px-4 py-6 text-center text-gray-400 text-xs">Memuat data...</td></tr>';
        const r = await fetch(`/api/admin/monitor/exam/${examId}/participants`, { headers });
        const res = await r.json();
        if (!res.success) throw new Error(res.error);
        document.getElementById('cnt-progress').textContent    = (res.counts.progress ?? 0) + ' Live';
        document.getElementById('cnt-submited').textContent    = (res.counts.submited ?? 0) + ' Submit';
        document.getElementById('cnt-not-started').textContent = (res.counts.not_started ?? 0) + ' Belum';
        if (!res.data || !res.data.length) { document.getElementById('monitor-body').innerHTML = '<tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">Tidak ada peserta.</td></tr>'; return; }
        document.getElementById('monitor-body').innerHTML = res.data.map(p => {
            const badge = { 'progress': '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium"><span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>Live</span>', 'submited': '<span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">✓ Submit</span>', 'not_started': '<span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full text-xs font-medium">Belum</span>' }[p.status] ?? p.status;
            const timeD = p.status === 'progress' ? `<span class="font-mono text-sm text-orange-600" data-seconds="${p.time_remaining}">${formatTime(p.time_remaining)}</span>` : '<span class="text-gray-400 text-xs">—</span>';
            return `<tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3"><div class="font-medium text-gray-800 text-sm">${escHtml(p.name)}</div><div class="text-xs text-gray-400">${escHtml(p.nis)}</div></td>
                <td class="px-4 py-3 text-sm text-gray-600">${escHtml(p.grade)}</td>
                <td class="px-4 py-3 text-xs text-gray-500">${escHtml(p.school||'—')}</td>
                <td class="px-4 py-3">${badge}</td>
                <td class="px-4 py-3">${timeD}</td>
                <td class="px-4 py-3">
                    ${parseInt(p.is_active) === 0 ? `<button onclick="openResetModal('${p.nis}','${p.name.replace(/'/g,"\\'")}')" class="px-2 py-1 text-xs bg-yellow-50 text-yellow-700 rounded hover:bg-yellow-100 transition">Reset</button>` : ''}
                </td>
            </tr>`;
        }).join('');
        startTimerTick();
    } catch(e) { document.getElementById('monitor-body').innerHTML = `<tr><td colspan="7" class="px-4 py-6 text-center text-red-400 text-sm">${e.message}</td></tr>`; }
}

let timerTick = null;
function startTimerTick() { clearInterval(timerTick); timerTick = setInterval(() => { document.querySelectorAll('[data-seconds]').forEach(el => { let s = Math.max(0, parseInt(el.dataset.seconds)-1); el.dataset.seconds=s; el.textContent=formatTime(s); }); }, 1000); }
function formatTime(s) { return `${String(Math.floor(s/60)).padStart(2,'0')}:${String(s%60).padStart(2,'0')}`; }
function escHtml(str) { if(!str) return '—'; return str.toString().replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function openResetModal(nis, name) { currentStudentNis=nis; document.getElementById('modal-student-name').textContent=name+' (NIS: '+nis+')'; document.getElementById('reset-modal').classList.remove('hidden'); }
function closeResetModal() { document.getElementById('reset-modal').classList.add('hidden'); }
async function confirmReset() {
    const btn = document.getElementById('confirm-reset-btn'); btn.disabled=true; btn.textContent='Mereset...';
    try {
        const r = await fetch(`/kepala/exam-sessions/${currentStudentNis}/reset`, { method:'POST', headers:{...headers,'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json'} });
        const res = await r.json(); closeResetModal(); showToast(res.success?'success':'error', res.message);
    } catch(e) { showToast('error','Gagal mereset login.'); } finally { btn.disabled=false; btn.textContent='Reset Login'; }
}
function showToast(type,msg) { const t=document.createElement('div'); t.className=`fixed bottom-4 right-4 z-50 px-4 py-3 text-white rounded-lg shadow-lg text-sm ${type==='success'?'bg-green-500':'bg-red-500'}`; t.textContent=msg; document.body.appendChild(t); setTimeout(()=>{t.style.opacity='0'; setTimeout(()=>t.remove(),300);},3500); }
function refreshAll() { fetchStats(); fetchActiveExams(); const id=document.getElementById('exam-select').value; if(id) fetchMonitor(id); }
fetchStats(); fetchActiveExams();
setInterval(()=>{ fetchStats(); fetchActiveExams(); }, 30000);
*/
</script>
@endpush
</x-app-layout>
