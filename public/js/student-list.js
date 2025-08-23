// Handle school dropdown changes
document.addEventListener('DOMContentLoaded', function() {
    const schoolDropdown = document.getElementById('school_filter');
    if (schoolDropdown) {
        schoolDropdown.addEventListener('change', function() {
            const schoolId = this.value;
            if (schoolId) {
                fetchStudents(schoolId);
            } else {
                clearStudentList();
            }
        });
    }
});

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

    fetch(`/api/schools/${schoolId}/students?exam_id=${examId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateStudentList(data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error fetching students: ' + error.message);
        });
}

function updateStudentList(students) {
    const tbody = document.getElementById('student-list-body');
    if (!tbody) return;

    tbody.innerHTML = '';

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
                    `<button type="button" onclick="addStudentToExam(${student.id})" class="px-3 py-1 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">
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

    fetch('/api/exams/add-student', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            exam_id: examId,
            student_id: studentId
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
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
