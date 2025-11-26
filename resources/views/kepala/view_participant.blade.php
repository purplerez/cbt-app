<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Siswa'.session('schoolname')) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                        <div class="flex items-center justify-between space-x-4">
                            <div class="flex items-center space-x-4">
                                @role('kepala')
                                <form action="{{ route('kepala.students') }}" method="get" class="flex items-center space-x-2">
                                @endrole
                                @role('guru')
                                <form action="{{ route('guru.students') }}" method="get" class="flex items-center space-x-2">
                                @endrole
                                    <label for="grade_id" class="sr-only">Kelas</label>
                                    <select name="grade_id" id="grade_id" class="block w-48 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Pilih Kelas</option>
                                        @foreach ($grade as $g)
                                            <option value="{{ $g->id }}" @if(isset($selectedGrade) && $selectedGrade == $g->id) selected @endif>{{ $g->name }}</option>
                                        @endforeach
                                    </select>

                                    <x-input-error :messages="$errors->get('grade_id')" class="mt-1" />
                                    <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Filter</button>
                                </form>

                                <!-- Form start - wraps both exam select and student table -->
                                @role('kepala')
                                    <form id="bulkRegisterForm" action="{{ route('kepala.exams.participants.register') }}" method="POST">
                                @endrole
                                @role('guru')
                                    <form id="bulkRegisterForm" action="{{ route('guru.exams.participants.register') }}" method="POST">
                                @endrole
                                    @csrf
                                    <div class="flex items-center ml-auto space-x-2">
                                        <input type="hidden" name="exam_id" id="bulk_exam_id" value="">
                                        <label for="exam_select" class="sr-only">Ujian</label>
                                        <select id="exam_select" onchange="document.getElementById('bulk_exam_id').value=this.value" class="block border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">-- Pilih Ujian --</option>
                                            @foreach($examsList as $ex)
                                                <option value="{{ $ex->id }}">{{ $ex->title }}</option>
                                            @endforeach
                                        </select>
                                        <div class="ml-2">
                                            <button id="bulkRegisterBtn" type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">Daftarkan yang dipilih</button>
                                        </div>
                                    </div>
                            </div>
                        </div>
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />

                        {{-- Student table (inside the bulk register form) --}}
                            <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                    <th class="w-1/6 px-4 py-2 border"><input type="checkbox" id="selectAll"></th>
                                <th class="w-2 px-4 py-2 border">Nama Siswa</th>
                                <th class="w-2 px-4 py-2 border">Kelas</th>
                                <th class="px-4 py-2 border w-11">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $index => $student)
                                <tr>
                                    <td class="px-4 py-2 border"><input type="checkbox" name="student_ids[]" value="{{$student->id}}"></td>
                                    <td class="px-4 py-2 border">{{ $student->student->name ?? $student->name }}</td>
                                    <td class="px-4 py-2 border">{{ $student->student->grade->name ?? $student->grade->name ?? '-' }}</td>
                                    <td class="px-4 py-2 border">
                                        @if ($student->preassigned->isNotEmpty())
                                            <span class="font-semibold text-green-600">Sudah terdaftar</span>
                                        @else
                                            <button type="button" data-student-id="{{$student->id}}" class="single-register-btn px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition">Daftarkan</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Data Kosong</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </form>

                <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const bulkForm = document.getElementById('bulkRegisterForm');
                                const selectAll = document.getElementById('selectAll');
                                const examSelect = document.getElementById('exam_select');
                                const bulkExamInput = document.getElementById('bulk_exam_id');
                                const tableBody = document.querySelector('table tbody');
                                const bulkBtn = document.getElementById('bulkRegisterBtn');

                                // Bulk form submit validation (keeps synchronous post behavior)
                                if (bulkForm) {
                                    bulkForm.addEventListener('submit', function(e) {
                                        e.preventDefault(); // always prevent default as we're handling submit via fetch

                                        // ensure hidden exam_id is set from examSelect before validate
                                        if (bulkExamInput && examSelect) {
                                            bulkExamInput.value = examSelect.value || '';
                                        }

                                        const checked = document.querySelectorAll('input[name="student_ids[]"]:checked');
                                        const examVal = bulkExamInput ? bulkExamInput.value : '';
                                        if (!examVal) {
                                            e.preventDefault();
                                            alert('Pilih ujian terlebih dahulu');
                                            return;
                                        }
                                        if (!checked.length) {
                                            e.preventDefault();
                                            alert('Pilih minimal satu siswa untuk didaftarkan');
                                            return;
                                        }
                                        if (confirm('Konfirmasi: daftarkan ' + checked.length + ' siswa ke ujian ini?')) {
                                            const formData = new FormData();
                                            formData.append('exam_id', examVal);
                                            checked.forEach(cb => formData.append('student_ids[]', cb.value));
                                            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                            formData.append('_token', token);

                                            // Submit form data
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
                                                    // Reload student list to show updated status
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

                                // Select-all checkbox behavior (works with dynamic table rows)
                                if (selectAll) {
                                    selectAll.addEventListener('change', function() {
                                        const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
                                        checkboxes.forEach(cb => cb.checked = selectAll.checked);
                                    });
                                }

                                // Attach single-register button handlers (call after rows are rendered)
                                function attachSingleHandlers(){
                                    const singleButtons = document.querySelectorAll('.single-register-btn');
                                    singleButtons.forEach(btn => {
                                        // remove any existing to avoid double-binding
                                        btn.replaceWith(btn.cloneNode(true));
                                    });
                                    const freshButtons = document.querySelectorAll('.single-register-btn');
                                    freshButtons.forEach(btn => {
                                        btn.addEventListener('click', function() {
                                            const studentId = this.getAttribute('data-student-id');
                                            if (!confirm('Konfirmasi: daftarkan siswa id=' + studentId + ' ke ujian ini?')) return;

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
                                                // On success, replace button cell with 'Sudah terdaftar'
                                                const td = btn.closest('td');
                                                if (td) td.innerHTML = '<a href="{{--  --}}" class="button-delete">Hapus dari Mapel</a>';
                                                // if (td) td.innerHTML = '<a href="{{--  --}}" class="button-delete">Hapus dari Mapel</a> <span class="font-semibold text-green-600">Sudah terdaftar</span>';
                                            })
                                            .catch(async (err) => {
                                                let msg = 'Gagal mendaftarkan siswa.';
                                                try { const ebody = await err.json(); if (ebody.error) msg = ebody.error; } catch(e){}
                                                alert(msg);
                                            });
                                        });
                                    });
                                }

                                // Load students for a given exam via API and render rows
                                async function loadStudentsForExam(examId) {
                                    // Clear table if no exam selected
                                    if (!examId) {
                                        if (tableBody) tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Pilih ujian untuk menampilkan siswa.</td></tr>';
                                        if (bulkExamInput) bulkExamInput.value = '';
                                        return;
                                    }

                                    if (tableBody) tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Memuat...</td></tr>';
                                    if (bulkBtn) bulkBtn.disabled = true; // prevent submit while loading
                                    const base = '{{ url("/api/kepala/exams") }}';
                                    const url = base + '/' + examId + '/participants';
                                    try {
                                        let res = null;
                                        let json = null;
                                        try {
                                            res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'include' });
                                            if (res.ok) {
                                                json = await res.json();
                                            } else {
                                                // If auth problems (401/419) or other server errors, we'll attempt fallback
                                                console.warn('API fetch failed', res.status);
                                            }
                                        } catch (err) {
                                            console.warn('API fetch error', err);
                                        }

                                        // Fallback to web route (session-auth) if API didn't return a good result
                                        if (!json) {
                                            try {
                                                // use the examType id (passed to the view as $examId) for the route segment
                                                // and include the selected exam_id as a query param so the controller receives it
                                                @role('kepala')
                                                    const webUrl = '{{ url('/kepala/exams') }}' + '/' + '{{ $examId ?? '' }}' + '/students-by-exam?exam_id=' + examId;
                                                @endrole
                                                @role('guru')
                                                    const webUrl = '{{ url('/guru/exams') }}' + '/' + '{{ $examId ?? '' }}' + '/students-by-exam?exam_id=' + examId;
                                                @endrole
                                                const fres = await fetch(webUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'include' });
                                                if (fres.ok) {
                                                    json = await fres.json();
                                                } else {
                                                    let msg = 'Gagal memuat data siswa (status ' + (fres.status || 'network') + ')';
                                                    try { const body = await fres.json(); if (body.error) msg = body.error; } catch(e){}
                                                    throw new Error(msg);
                                                }
                                            } catch (err) {
                                                console.error('Both API and web fallback failed', err);
                                                throw err;
                                            }
                                        }
                                        if (!tableBody) return;
                                        tableBody.innerHTML = '';
                                        if (!json.students || !json.students.length) {
                                            tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Tidak ada siswa.</td></tr>';
                                        } else {
                                            json.students.forEach(s => {
                                                const tr = document.createElement('tr');
                                                tr.innerHTML = `
                                                    <td class="px-4 py-2 border"><input type="checkbox" name="student_ids[]" value="${s.id}"></td>
                                                    <td class="px-4 py-2 border">${s.name}</td>
                                                    <td class="px-4 py-2 border">${s.registered ? '<span class="font-semibold text-green-600">Sudah terdaftar</span>' : '<button type="button" data-student-id="'+s.id+'" class="single-register-btn px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition">Daftarkan</button>'}</td>
                                                `;
                                                tableBody.appendChild(tr);
                                            });
                                        }

                                        // set hidden bulk input and set selectAll to checked (auto-select rows for bulk)
                                        if (bulkExamInput) bulkExamInput.value = examId;
                                        if (selectAll) {
                                            selectAll.checked = true;
                                            // trigger change so checkboxes reflect the selectAll state
                                            selectAll.dispatchEvent(new Event('change'));
                                        }
                                        // enable bulk button only if there are student rows
                                        if (bulkBtn) bulkBtn.disabled = !(json.students && json.students.length);
                                        // reattach handlers for dynamic buttons
                                        attachSingleHandlers();
                                    } catch (err) {
                                        console.error(err);
                                        alert(err.message || 'Terjadi kesalahan saat memuat siswa.');
                                        if (tableBody) tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Gagal memuat data siswa.</td></tr>';
                                    }
                                }

                                // Keep controls (buttons, hidden inputs) in sync and fetch students when exam changes
                                function updateControls() {
                                    const examId = (examSelect && examSelect.value) || '';
                                    if (bulkExamInput) bulkExamInput.value = examId;
                                    // disable bulk button when no exam selected
                                    const bulkBtn = document.getElementById('bulkRegisterBtn');
                                    if (bulkBtn) bulkBtn.disabled = !examId;
                                    // optionally, toggle single-register buttons as well
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

                                    // initial sync on page load
                                    updateControls();

                                    // If an exam is already selected on page load, load its students
                                    if (examSelect.value) {
                                        loadStudentsForExam(examSelect.value);
                                    }
                                }

                                // attach initial handlers for any server-rendered buttons
                                attachSingleHandlers();
                            });
                        </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
