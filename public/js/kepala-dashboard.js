// resources/js/kepala-dashboard.js

class KephalaDashboardManager {
    constructor() {
        this.apiToken = this.getApiToken();
        this.refreshInterval = 30000; // 30 seconds
        this.isLoading = false;

        if (!this.apiToken) {
            console.error("API Token not found");
            this.showError("Token autentikasi tidak ditemukan. Silakan login kembali.");
            return;
        }

        this.init();
    }

    /**
     * Get API token from window global (fallback from app.blade)
     */
    getApiToken() {
        // Check meta tag first
        const tokenMeta = document.querySelector('meta[name="api_token"]');
        if (tokenMeta) {
            const token = tokenMeta.getAttribute('content');
            if (token && token.trim() !== '') {
                return token;
            }
        }

        // Fallback to window global
        if (window.apiToken && window.apiToken.trim() !== '') {
            console.warn("Using window.apiToken - consider using meta tag instead");
            return window.apiToken;
        }

        return null;
    }

    init() {
        this.setupEventListeners();
        this.loadAllDashboardData();
        this.startAutoRefresh();
    }

    setupEventListeners() {
        // Add any click handlers or filters
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize tooltips, popovers, or other plugins if needed
        });
    }

    /**
     * Make API request with error handling
     */
    async apiRequest(endpoint, options = {}) {
        try {
            const response = await fetch(endpoint, {
                headers: {
                    "Authorization": `Bearer ${this.apiToken}`,
                    "Accept": "application/json",
                    "Content-Type": "application/json",
                    ...options.headers
                },
                ...options
            });

            if (response.status === 401) {
                this.handleUnauthorized();
                throw new Error("Sesi Anda telah berakhir. Silakan login kembali.");
            }

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error(`API Request Error [${endpoint}]:`, error);
            throw error;
        }
    }

    /**
     * Handle unauthorized access
     */
    handleUnauthorized() {
        console.warn("Unauthorized - redirecting to login");
        this.showError("Sesi anda telah berakhir. Silakan login kembali.");
        setTimeout(() => {
            window.location.href = '/login';
        }, 2000);
    }

    /**
     * Load all dashboard data
     */
    async loadAllDashboardData() {
        try {
            this.isLoading = true;
            await Promise.all([
                this.loadStats(),
                this.loadGradeStats(),
                this.loadActiveExams(),
                this.loadRecentScores()
            ]);
        } catch (error) {
            console.error("Error loading dashboard data:", error);
            this.showError("Gagal memuat data dashboard");
        } finally {
            this.isLoading = false;
        }
    }

    /**
     * Load overview statistics
     */
    async loadStats() {
        try {
            const data = await this.apiRequest("/api/kepala/dashboard/stats");

            // Update school info
            this.updateElement('schoolName', data.school_name || '-');
            this.updateElement('schoolInfo', data.school_info || '-');

            // Update statistics cards
            this.updateElement('totalStudents', this.formatNumber(data.total_students));
            this.updateElement('totalGrades', `${data.total_grades} Kelas`);
            this.updateElement('onlineStudents', this.formatNumber(data.online_students));
            this.updateElement('onlinePercentage', `${data.online_percentage}%`);
            this.updateElement('activeExams', data.active_exams);
            this.updateElement('participantCount', `${this.formatNumber(data.participant_count)} peserta`);
            this.updateElement('totalExams', data.total_exams);
            this.updateElement('examTypes', `${data.total_preassigned} terdaftar`);

            // Update exam status badge
            const examStatus = document.getElementById('examStatus');
            if (examStatus) {
                examStatus.textContent = data.exam_status.text;
                examStatus.className = `px-2 py-1 text-xs font-semibold rounded-full ${data.exam_status.color}`;
            }
        } catch (error) {
            this.showError("Gagal memuat statistik");
        }
    }

    /**
     * Load grade statistics
     */
    async loadGradeStats() {
        try {
            const data = await this.apiRequest("/api/kepala/dashboard/grade-stats");
            const tbody = document.getElementById('gradeStats');

            if (!tbody) return;

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Tidak ada data kelas
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = data.map(grade => {
                const statusColor = grade.percentage >= 80 ? 'bg-green-100 text-green-800' :
                                   grade.percentage >= 50 ? 'bg-yellow-100 text-yellow-800' :
                                   'bg-red-100 text-red-800';

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">${grade.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${grade.total_students}</td>
                        <td class="px-6 py-4 whitespace-nowrap">${grade.online_students}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-semibold">${grade.percentage}%</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusColor}">
                                ${grade.status}
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            this.showTableError('gradeStats', 5);
        }
    }

    /**
     * Load active exams
     */
    async loadActiveExams() {
        try {
            const data = await this.apiRequest("/api/kepala/dashboard/active-exams");
            const tbody = document.getElementById('activeExamsBody');

            if (!tbody) return;

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

            tbody.innerHTML = data.map(exam => {
                const progressPercentage = exam.total_participants > 0
                    ? Math.round((exam.completed_participants / exam.total_participants) * 100)
                    : 0;

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">${exam.name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${exam.grade}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm">
                                <span class="font-semibold">${exam.active_participants}</span>
                                <span class="text-gray-500">/ ${exam.total_participants}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-12 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-500 transition-all" style="width: ${progressPercentage}%"></div>
                                </div>
                                <span class="ml-2 text-xs text-gray-600">${progressPercentage}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">${exam.remaining_time} min</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                Berlangsung
                            </span>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            this.showTableError('activeExamsBody', 6);
        }
    }

    /**
     * Load recent exam scores
     */
    async loadRecentScores() {
        try {
            const data = await this.apiRequest("/api/kepala/dashboard/recent-scores");
            const tbody = document.getElementById('recentScoresBody');

            if (!tbody) return;

            if (data.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Belum ada nilai ujian yang terekam
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = data.map(score => {
                const scoreValue = parseFloat(score.average_score);
                const scoreColor = scoreValue >= 80 ? 'text-green-600' :
                                  scoreValue >= 60 ? 'text-yellow-600' :
                                  'text-red-600';

                return `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">${score.exam_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${score.grade_name}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold ${scoreColor}">${score.average_score}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">${score.participant_count} siswa</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="/kepala/exams/${score.exam_id}/scores?grade_id=${score.grade_id}"
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium transition-colors">
                                Lihat Detail →
                            </a>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            this.showTableError('recentScoresBody', 5);
        }
    }

    /**
     * Utility: Update element text
     */
    updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    /**
     * Utility: Format number with locale
     */
    formatNumber(number) {
        return Number(number).toLocaleString('id-ID');
    }

    /**
     * Utility: Show error in table
     */
    showTableError(tableId, colspan) {
        const tbody = document.getElementById(tableId);
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${colspan}" class="px-6 py-4 text-center text-red-500">
                        Gagal memuat data
                    </td>
                </tr>
            `;
        }
    }

    /**
     * Show error notification
     */
    showError(message) {
        console.error("✗ " + message);
        // Integrate with your notification system if available
    }

    /**
     * Start auto-refresh
     */
    startAutoRefresh() {
        setInterval(() => {
            if (!this.isLoading) {
                this.loadAllDashboardData();
            }
        }, this.refreshInterval);
    }

    /**
     * Get exam scores with filters
     */
    async getExamScores(examId, gradeId = null) {
        try {
            let url = `/api/kepala/exams/${examId}/scores`;
            if (gradeId) {
                url += `?grade_id=${gradeId}`;
            }
            return await this.apiRequest(url);
        } catch (error) {
            this.showError("Gagal memuat nilai ujian");
            throw error;
        }
    }

    /**
     * Get session details
     */
    async getSessionDetails(sessionId) {
        try {
            return await this.apiRequest(`/api/kepala/exams/sessions/${sessionId}`);
        } catch (error) {
            this.showError("Gagal memuat detail sesi");
            throw error;
        }
    }
}

// Initialize dashboard when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.kephalaDashboard = new KephalaDashboardManager();
});
