// Handle school dropdown changes
document.addEventListener('DOMContentLoaded', function () {
    const schoolDropdown = document.getElementById('school_filter');
    if (schoolDropdown) {
        schoolDropdown.addEventListener('change', function () {
            const schoolId = this.value;
            if (schoolId) {
                fetchStudents(schoolId);
            } else {
                clearStudentList();
            }
        });
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

    // Fallback to check if token is available globally
    if (window.apiToken && window.apiToken.trim() !== '') {
        return window.apiToken;
    }

    return null;
}

function getExamId() {
    // Try to get exam ID from session or data attribute
    const examIdElement = document.getElementById('current-exam-id');
    if (examIdElement) {
        return examIdElement.value;
    }
    // Fallback to session data
    return window.examId || null;
}

function fetchStudents(schoolId) {
    const examId = getExamId();
    if (!examId) {
        console.error('No exam ID found');
        alert('Error: No exam ID found');
        return;
    }

    const token = getApiToken();
    if (!token) {
        console.error('No API token found');
        alert('Error: No API token found. Please login again.');
        return;
    }

    console.log('Fetching students for school:', schoolId, 'exam:', examId);
    console.log('Using token:', token ? 'Token available' : 'No token');

    const url = `/api/admin/schools/${schoolId}/students?exam_id=${examId}`;
    console.log('Request URL:', url);

    fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response content-type:', response.headers.get('content-type'));

            if (!response.ok) {
                return response.text().then(text => {
                    console.log('Error response body:', text);
                    throw new Error(`HTTP ${response.status}: ${response.statusText} - ${text.substring(0, 200)}`);
                });
            }

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(text => {
                    console.log('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
                });
            }

            return response.json();
        })
        .then(data => {
            console.log('Received data:', data);
            updateStudentList(data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching students: ' + error.message);
        });
}

function updateStudentList(response) {
    const tbody = document.getElementById('student-list-body');
    const examStatus =  session('perexamstatus') ;
    if (!tbody) return;

    tbody.innerHTML = '';

    // Handle the Laravel Resource response structure
    const students = response.data || [];

    if (!Array.isArray(students) || students.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada siswa ditemukan</td></tr>';
        return;
    }

    students.forEach(student => {
        const row = document.createElement('tr');
        const isAssigned = student.is_assigned;

        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${student.nis || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${student.name || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${student.grade?.name || '-'}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                ${isAssigned ?
                '<span class="text-green-600">Sudah Terdaftar</span>' :
                `<button type="button" onclick="addStudentToExam(${student.id})" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
                     ${examStatus == 0 ? 'disabled' : ''} >
                        Tambah ke Ujian
                    </button>`
            }
            </td>
        `;
        tbody.appendChild(row);
    });
}

function clearStudentList() {
    const tbody = document.getElementById('student-list-body');
    if (tbody) {
        tbody.innerHTML = '';
    }
}

function addStudentToExam(studentId) {
    const examId = getExamId();
    if (!examId) {
        alert('Error: No exam ID found');
        return;
    }

    const token = getApiToken();
    if (!token) {
        console.error('No API token found');
        alert('Error: No API token found. Please login again.');
        return;
    }

    fetch('/api/admin/exams/add-student', {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            exam_id: examId,
            student_id: studentId
        })
    })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert('Siswa berhasil ditambahkan ke ujian');
                // Refresh the student list
                const schoolId = document.getElementById('school_filter').value;
                if (schoolId) {
                    fetchStudents(schoolId);
                }
            } else {
                throw new Error(data.message || 'Gagal menambahkan siswa ke ujian');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message);
        });
}
