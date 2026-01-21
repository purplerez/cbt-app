<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Siswa'.session('schoolname')) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Filters Section -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Grade Filter -->
                        <div>
                            <label for="grade_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="grade_id" id="grade_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Kelas</option>
                                @foreach ($grade as $g)
                                    <option value="{{ $g->id }}" @if(isset($selectedGrade) && $selectedGrade == $g->id) selected @endif>{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('grade_id')" class="mt-1" />
                        </div>

                        <!-- Exam Select and Register Button -->
                        @role('kepala')
                            <form id="bulkRegisterForm" action="{{ route('kepala.exams.participants.register') }}" method="POST" class="md:col-span-2 flex items-end gap-2">
                        @endrole
                        @role('guru')
                            <form id="bulkRegisterForm" action="{{ route('guru.exams.participants.register') }}" method="POST" class="md:col-span-2 flex items-end gap-2">
                        @endrole
                            @csrf
                            <input type="hidden" name="exam_id" id="bulk_exam_id" value="">
                            <div class="flex-1">
                                <label for="exam_select" class="block text-sm font-medium text-gray-700">Ujian</label>
                                <select id="exam_select" onchange="document.getElementById('bulk_exam_id').value=this.value" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">-- Pilih Ujian --</option>
                                    @foreach($examsList as $ex)
                                        <option value="{{ $ex->id }}">{{ $ex->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button id="bulkRegisterBtn" type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Daftarkan
                            </button>
                        </form>
                    </div>
                    <x-input-error :messages="$errors->get('error')" class="mt-4" />
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Siswa</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($students as $index => $student)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}">
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ $student->student->name ?? $student->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $student->student->grade->name ?? $student->grade->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            @if($student->preassigned->isNotEmpty())
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800">
                                                    Sudah Terdaftar
                                                </span>
                                            @else
                                                <button type="button" data-student-id="{{ $student->id }}" class="single-register-btn inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white transition bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                                    Daftarkan
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500">
                                            Tidak ada data siswa.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const bulkForm = document.getElementById('bulkRegisterForm');
            const selectAll = document.getElementById('selectAll');
            const examSelect = document.getElementById('exam_select');
            const bulkExamInput = document.getElementById('bulk_exam_id');
            const tableBody = document.querySelector('table tbody');
            const bulkBtn = document.getElementById('bulkRegisterBtn');
            const gradeFilter = document.getElementById('grade_id');
            const examTypeId = '{{ $examId ?? "" }}';

            if (bulkForm) {
                bulkForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    if (bulkExamInput && examSelect) {
                        bulkExamInput.value = examSelect.value || '';
                    }

                    const checked = document.querySelectorAll('input[name="student_ids[]"]:checked');
                    const examVal = bulkExamInput ? bulkExamInput.value : '';
                    if (!examVal) {
                        alert('Pilih ujian terlebih dahulu');
                        return;
                    }
                    if (!checked.length) {
                        alert('Pilih minimal satu siswa untuk didaftarkan');
                        return;
                    }
                    if (confirm('Konfirmasi: daftarkan ' + checked.length + ' siswa ke ujian ini?')) {
                        const formData = new FormData();
                        formData.append('exam_id', examVal);
                        checked.forEach(cb => formData.append('student_ids[]', cb.value));
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        formData.append('_token', token);

                        fetch(bulkForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.added > 0) {
                                alert('Berhasil menambahkan ' + data.added + ' peserta.');
                                loadStudentsForExam(examVal);
                            } else {
                                alert('Tidak ada peserta baru yang ditambahkan.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat mendaftarkan siswa.');
                        });
                    }
                });
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
                    checkboxes.forEach(cb => cb.checked = selectAll.checked);
                });
            }

            function attachActionHandlers(){
                const singleButtons = document.querySelectorAll('.single-register-btn');
                singleButtons.forEach(btn => {
                    btn.replaceWith(btn.cloneNode(true));
                });
                const freshButtons = document.querySelectorAll('.single-register-btn');
                freshButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const studentId = this.getAttribute('data-student-id');
                        if (!confirm('Konfirmasi: daftarkan siswa ke ujian ini?')) return;

                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const examId = (bulkExamInput && bulkExamInput.value) || (examSelect && examSelect.value);
                        if (!examId) {
                            alert('Pilih ujian terlebih dahulu');
                            return;
                        }
                        @role('kepala')
                            const url = '{{ route("kepala.exams.participants.register") }}';
                        @endrole
                        @role('guru')
                            const url = '{{ route("guru.exams.participants.register") }}';
                        @endrole

                        fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'include',
                            body: JSON.stringify({ student_id: studentId, exam_id: examId })
                        })
                        .then(res => {
                            if (!res.ok) throw res;
                            return res.json().catch(() => ({ success: true }));
                        })
                        .then(data => {
                            loadStudentsForExam(examId);
                        })
                        .catch(async (err) => {
                            let msg = 'Gagal mendaftarkan siswa.';
                            try { const ebody = await err.json(); if (ebody.error) msg = ebody.error; } catch(e){}
                            alert(msg);
                        });
                    });
                });

                const deleteButtons = document.querySelectorAll('.delete-btn');
                deleteButtons.forEach(btn => {
                    btn.replaceWith(btn.cloneNode(true));
                });
                const freshDeleteButtons = document.querySelectorAll('.delete-btn');
                freshDeleteButtons.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const studentId = this.getAttribute('data-student-id');
                        if (!confirm('Konfirmasi: hapus siswa ini dari ujian?')) return;

                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                        const examId = (bulkExamInput && bulkExamInput.value) || (examSelect && examSelect.value);
                        if (!examId) {
                            alert('Pilih ujian terlebih dahulu');
                            return;
                        }

                        @role('kepala')
                            const url = '{{ route("kepala.exams.participants.delete") }}';
                        @endrole
                        @role('guru')
                            const url = '{{ route("guru.exams.participants.delete") }}';
                        @endrole

                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'include',
                            body: JSON.stringify({ student_id: studentId, exam_id: examId })
                        })
                        .then(res => {
                            if (!res.ok) throw res;
                            return res.json();
                        })
                        .then(data => {
                            if (data.success) {
                                loadStudentsForExam(examId);
                                alert('Berhasil menghapus peserta dari ujian');
                            } else {
                                alert(data.message || 'Gagal menghapus peserta');
                            }
                        })
                        .catch(async (err) => {
                            let msg = 'Gagal menghapus peserta.';
                            try { const ebody = await err.json(); if (ebody.error) msg = ebody.error; } catch(e){}
                            alert(msg);
                        });
                    });
                });
            }

            async function loadStudentsForExam(examId) {
                if (!examId) {
                    if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Pilih ujian untuk menampilkan siswa.</td></tr>';
                    if (bulkExamInput) bulkExamInput.value = '';
                    return;
                }

                if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Memuat...</td></tr>';
                if (bulkBtn) bulkBtn.disabled = true;

                const gradeId = gradeFilter ? gradeFilter.value : '';

                try {
                    const webUrl = '{{ url('/kepala/exams') }}' + '/' + examTypeId + '/students-by-exam?exam_id=' + examId + (gradeId ? '&grade_id=' + gradeId : '');
                    const fres = await fetch(webUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'include' });

                    if (!fres.ok) {
                        let msg = 'Gagal memuat data siswa (status ' + (fres.status || 'network') + ')';
                        try { const body = await fres.json(); if (body.error) msg = body.error; } catch(e){}
                        throw new Error(msg);
                    }

                    const json = await fres.json();

                    if (!tableBody) return;
                    tableBody.innerHTML = '';
                    if (!json.students || !json.students.length) {
                        tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada siswa.</td></tr>';
                    } else {
                        json.students.forEach(s => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-gray-50';
                            const actionCell = s.registered
                                        ? `<span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full bg-green-100 text-green-800">Sudah Terdaftar</span>`
                                        : `<button type="button"
                                                data-student-id="${s.id}"
                                                class="single-register-btn inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white transition bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                            Daftarkan
                                        </button>`;

                                    tr.innerHTML = `
                                        <td class="px-6 py-4 whitespace-nowrap"><input type="checkbox" name="student_ids[]" value="${s.id}"></td>
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">${s.name}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">${s.grade}</td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">${actionCell}</td>
                                    `;
                                    tableBody.appendChild(tr);
                                });

                    }

                    if (bulkExamInput) bulkExamInput.value = examId;
                    if (selectAll) {
                        selectAll.checked = true;
                        selectAll.dispatchEvent(new Event('change'));
                    }
                    if (bulkBtn) bulkBtn.disabled = !(json.students && json.students.length);
                    attachActionHandlers();
                } catch (err) {
                    console.error(err);
                    alert(err.message || 'Terjadi kesalahan saat memuat siswa.');
                    if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Gagal memuat data siswa.</td></tr>';
                }
            }

            function updateControls() {
                const examId = (examSelect && examSelect.value) || '';
                if (bulkExamInput) bulkExamInput.value = examId;
                const bulkBtn = document.getElementById('bulkRegisterBtn');
                if (bulkBtn) bulkBtn.disabled = !examId;
                const singleBtns = document.querySelectorAll('.single-register-btn');
                singleBtns.forEach(b => b.disabled = !examId);
            }

            if (examSelect) {
                examSelect.addEventListener('change', function() {
                    const examId = this.value;
                    if (bulkExamInput) bulkExamInput.value = examId;
                    updateControls();
                    loadStudentsForExam(examId);
                });

                updateControls();

                if (examSelect.value) {
                    loadStudentsForExam(examSelect.value);
                }
            }

            if (gradeFilter) {
                gradeFilter.addEventListener('change', function() {
                    const currentExamId = examSelect ? examSelect.value : '';
                    if (currentExamId) {
                        loadStudentsForExam(currentExamId);
                    } else {
                        if (tableBody) {
                            tableBody.innerHTML = '<tr><td colspan="4" class="px-6 py-4 text-center text-gray-500">Pilih ujian terlebih dahulu untuk melihat siswa berdasarkan kelas.</td></tr>';
                        }
                    }
                });
            }

            attachActionHandlers();
        });
    </script>
</x-app-layout>
