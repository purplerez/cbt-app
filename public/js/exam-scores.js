document.addEventListener('DOMContentLoaded', function() {
    const schoolDropdown = document.getElementById('school_filter_nilai');
    const gradeDropdown = document.getElementById('grade_filter_nilai');
    const exportButton = document.getElementById('export_nilai_pdf');
    const examIdElement = document.getElementById('current-exam-id');

    if (!examIdElement) {
        console.error('Exam ID element not found');
        return;
    }

    const examId = examIdElement.value;

    if (schoolDropdown) {
        schoolDropdown.addEventListener('change', function() {
            const schoolId = this.value;
            if (schoolId) {
                fetchGrades(schoolId);
                fetchScores();
            } else {
                gradeDropdown.innerHTML = '<option value="">Semua Kelas</option>';
                fetchScores();
            }
        });
    }

    if (gradeDropdown) {
        gradeDropdown.addEventListener('change', function() {
            fetchScores();
        });
    }

    // Update hidden export form fields when filters change
    function updateExportFields() {
        const schoolIdField = document.getElementById('export_school_id');
        const gradeIdField = document.getElementById('export_grade_id');
        if (schoolIdField && gradeIdField) {
            schoolIdField.value = schoolDropdown.value || '';
            gradeIdField.value = gradeDropdown.value || '';
        }
    }

    // Update export fields when dropdowns change
    if (schoolDropdown) {
        schoolDropdown.addEventListener('change', updateExportFields);
    }
    if (gradeDropdown) {
        gradeDropdown.addEventListener('change', updateExportFields);
    }

    // Initial update of export fields
    updateExportFields();

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

    function buildFetchOptions(token) {
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        };
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        const options = {
            method: 'GET',
            headers: headers,
            // allow session-based auth via cookies when no token
            credentials: 'same-origin'
        };

        return options;
    }

    function fetchGrades(schoolId) {
        const token = getApiToken();

        fetch(`/api/admin/schools/${schoolId}/grades`, buildFetchOptions(token))
            .then(response => response.json())
            .then(data => {
                gradeDropdown.innerHTML = '<option value="">Semua Kelas</option>';
                data.forEach(grade => {
                    const option = document.createElement('option');
                    option.value = grade.id;
                    option.textContent = grade.name;
                    gradeDropdown.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching grades:', error);
                alert('Error loading grades');
            });
    }

    function fetchScores() {
        const schoolId = schoolDropdown.value;
        const gradeId = gradeDropdown.value;
        const token = getApiToken();

        fetch(`/api/admin/exam/${examId}/scores?school_id=${schoolId || ''}&grade_id=${gradeId || ''}`, buildFetchOptions(token))
            .then(response => response.json())
            .then(response => {
                if (response.success) {
                    updateScoresTable(response.data);
                }
            })
            .catch(error => {
                console.error('Error fetching scores:', error);
            });
    }

    function updateScoresTable(scores) {
        const tbody = document.getElementById('nilai-list-body');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (!scores.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data nilai</td></tr>';
            return;
        }

        scores.forEach(score => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-6 py-4 text-sm text-gray-900">${score.nis}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${score.name}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${score.grade}</td>
                <td class="px-6 py-4 text-sm text-gray-900">${score.score.toFixed(2)}</td>
            `;
            tbody.appendChild(row);
        });
    }
});
