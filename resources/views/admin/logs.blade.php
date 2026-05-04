<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Log Aktivitas</h2>
            <div class="flex items-center gap-3">
                <button onclick="refreshLogs()" class="flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-lg text-xs hover:bg-indigo-700 transition">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Refresh
                </button>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span>Real-time</span>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                {{-- FILTERS --}}
                <div class="p-5 border-b border-gray-100 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label for="search-input" class="block text-xs font-medium text-gray-700 mb-2">Cari Log</label>
                            <input
                                type="text"
                                id="search-input"
                                placeholder="Cari deskripsi atau IP..."
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            >
                        </div>
                        <div>
                            <label for="per-page" class="block text-xs font-medium text-gray-700 mb-2">Per Halaman</label>
                            <select id="per-page" onchange="changePage(1)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <div class="flex items-end gap-2">
                            <button onclick="refreshLogs()" class="flex-1 px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                                Cari
                            </button>
                            <button onclick="resetFilters()" class="flex-1 px-3 py-2 border border-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                                Reset
                            </button>
                        </div>
                    </div>
                </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">IP Address</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Device</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User Agent</th>
                            </tr>
                        </thead>
                        <tbody id="logs-body" class="bg-white divide-y divide-gray-50">
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Memuat data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                    <div class="text-xs text-gray-500">
                        Menampilkan <span id="showing-start">0</span> hingga <span id="showing-end">0</span> dari <span id="total-count">0</span> log
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="previousPage()" id="prev-btn" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            ← Sebelumnya
                        </button>
                        <div id="page-info" class="text-xs text-gray-600 px-3">Halaman 1</div>
                        <button onclick="nextPage()" id="next-btn" class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            Berikutnya →
                        </button>
                    </div>
                </div>
            </div>

            {{-- LEGEND --}}
            <div class="mt-6 bg-white rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Informasi Kolom</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
                    <div>
                        <p><strong>Waktu:</strong> Waktu saat aktivitas terjadi (diupdate setiap 5 detik)</p>
                        <p><strong>User:</strong> Nama pengguna yang melakukan aktivitas</p>
                        <p><strong>Deskripsi:</strong> Detail aktivitas yang dilakukan</p>
                    </div>
                    <div>
                        <p><strong>IP Address:</strong> Alamat IP pengguna saat melakukan aktivitas</p>
                        <p><strong>Device:</strong> Tipe perangkat yang digunakan</p>
                        <p><strong>User Agent:</strong> Browser dan sistem operasi pengguna</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
const API = window.apiToken;
const headers = { 'Authorization': 'Bearer ' + API, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };

let currentPage = 1;
let lastRefreshTime = null;
let autoRefreshInterval = null;

// ── LOAD LOGS ──────────────────────────────────────────────────
async function loadLogs(page = 1) {
    try {
        const perPage = document.getElementById('per-page').value;
        const search = document.getElementById('search-input').value;

        const params = new URLSearchParams({
            page: page,
            per_page: perPage,
            search: search,
        });

        const response = await fetch(`/api/admin/logs?${params}`, { headers });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Gagal memuat log');
        }

        const logs = result.data;
        const pagination = result.pagination;

        // Update table
        const tbody = document.getElementById('logs-body');
        if (!logs || logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 text-sm">Tidak ada log yang ditemukan</td></tr>';
        } else {
            tbody.innerHTML = logs.map(log => `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                        <span title="${log.created_at}">${log.created_at_human || log.created_at}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                        ${escHtml(log.user_name)}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <span class="inline-block max-w-md truncate" title="${log.log_desc}">
                            ${escHtml(log.log_desc)}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600 font-mono">
                        ${escHtml(log.ip_address) || '—'}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        ${escHtml(log.device) || '—'}
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-sm">
                        <span class="inline-block truncate max-w-xs" title="${log.user_agent}">
                            ${escHtml(log.user_agent) ? escHtml(log.user_agent).substring(0, 50) + '...' : '—'}
                        </span>
                    </td>
                </tr>
            `).join('');
        }

        // Update pagination info
        currentPage = pagination.current_page;
        const start = (pagination.current_page - 1) * pagination.per_page + 1;
        const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);

        document.getElementById('showing-start').textContent = pagination.total > 0 ? start : 0;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-count').textContent = pagination.total;
        document.getElementById('page-info').textContent = `Halaman ${pagination.current_page} dari ${pagination.last_page}`;

        // Update button states
        document.getElementById('prev-btn').disabled = pagination.current_page <= 1;
        document.getElementById('next-btn').disabled = pagination.current_page >= pagination.last_page;

        // Update last refresh time
        const now = new Date();
        lastRefreshTime = now.toLocaleTimeString('id-ID');

    } catch (error) {
        console.error('Error loading logs:', error);
        document.getElementById('logs-body').innerHTML = `
            <tr><td colspan="6" class="px-4 py-8 text-center text-red-500 text-sm">
                Gagal memuat log: ${error.message}
            </td></tr>
        `;
    }
}

// ── PAGINATION ─────────────────────────────────────────────────
function changePage(page) {
    currentPage = page;
    loadLogs(page);
}

function nextPage() {
    changePage(currentPage + 1);
}

function previousPage() {
    changePage(currentPage - 1);
}

function refreshLogs() {
    changePage(1);
}

function resetFilters() {
    document.getElementById('search-input').value = '';
    document.getElementById('per-page').value = '10';
    currentPage = 1;
    loadLogs(1);
}

// ── HELPERS ────────────────────────────────────────────────────
function escHtml(str) {
    if (!str) return '—';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

// ── AUTO REFRESH (Real-time) ───────────────────────────────────
function startAutoRefresh() {
    // Refresh every 5 seconds
    autoRefreshInterval = setInterval(() => {
        loadLogs(currentPage);
    }, 5000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}

// ── INIT ───────────────────────────────────────────────────────
loadLogs(1);
startAutoRefresh();

// Stop auto-refresh when user leaves the page
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopAutoRefresh();
    } else {
        startAutoRefresh();
    }
});

// Cleanup on page unload
window.addEventListener('beforeunload', stopAutoRefresh);
</script>
@endpush
</x-app-layout>
