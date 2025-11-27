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

            // Reset grade dropdown
            gradeDropdown.innerHTML = '<option value="">Semua Kelas</option>';

            // Clear scores table
            const tbody = document.getElementById('nilai-list-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Pilih kelas untuk melihat nilai</td></tr>';
            }

            if (schoolId) {
                fetchGrades(schoolId);
            }

            updateExportFields();

                // fetchScores();
            // } else {
            //     gradeDropdown.innerHTML = '<option value="">Semua Kelas</option>';
            //     fetchScores();
            // }
        });
    }

    if (gradeDropdown) {
        gradeDropdown.addEventListener('change', function() {
            fetchScores();
            updateExportFields();
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

    //  updateExportFields();

    // Update export fields when dropdowns change
    // if (schoolDropdown) {
    //     schoolDropdown.addEventListener('change', updateExportFields);
    // }
    // if (gradeDropdown) {
    //     gradeDropdown.addEventListener('change', updateExportFields);
    // }

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
            'X-Requested-With': 'XMLHttpRequest',
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
        console.log('Fetching grades for school:', schoolId); // Debug

        fetch(`/api/admin/schools/${schoolId}/grades`, buildFetchOptions(token))
            .then(response => {
                console.log('Grades response status:', response.status); // Debug
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error('Error from server:', data.error);
                    alert('Error: ' + data.error);
                    return;
                }

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

        if (!schoolId) {
            const tbody = document.getElementById('nilai-list-body');
            if (tbody) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Pilih madrasah terlebih dahulu</td></tr>';
            }
            return;
        }

        // Show loading
        const tbody = document.getElementById('nilai-list-body');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-4 text-center text-gray-500">Memuat data...</td></tr>';
        }

        fetch(`/api/admin/exam/${examId}/scores?school_id=${schoolId || ''}&grade_id=${gradeId || ''}`, buildFetchOptions(token))
            .then(response => {
                console.log('Scores response status:', response.status); // Debug
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Scores data:', data); // Debug

                if (data.error) {
                    if (tbody) {
                        tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">${data.error}</td></tr>`;
                    }
                    return;
                }

                updateScoresTable(data.students || [], data.total_possible_score);
            })
            .catch(error => {
                console.error('Error fetching scores:', error);
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="6" class="px-6 py-4 text-center text-red-500">Error: ${error.message}</td></tr>`;
                }
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
