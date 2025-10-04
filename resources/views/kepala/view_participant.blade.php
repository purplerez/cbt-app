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
                            <a href="{{route('kepala.student.create')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >
                                + Tambah
                            </a>

                            <form action="{{ route('kepala.students') }}" method="get" class="flex items-center space-x-2">
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
                        </div>
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />

                        {{-- Bulk register form --}}
                        <form id="bulkRegisterForm" action="{{ route('kepala.exams.participants.store', $examId ?? 0) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <button id="bulkRegisterBtn" type="submit" class="px-3 py-1.5 bg-indigo-600 text-white text-sm font-medium rounded hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">Daftarkan yang dipilih</button>
                            </div>

                            <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                    <th class="w-1/6 px-4 py-2 border"><input type="checkbox" id="selectAll"></th>
                                <th class="w-2 px-4 py-2 border">Nama Siswa</th>
                                <th class="px-4 py-2 border w-11">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $index => $student)
                                <tr>
                                    <td class="px-4 py-2 border"><input type="checkbox" name="student_ids[]" value="{{$student->id}}"></td>
                                    <td class="px-4 py-2 border">{{ $student->student->name ?? $student->name }}</td>
                                    <td class="px-4 py-2 border">
                                        @if ($student->preassigned->isNotEmpty())
                                            <span class="font-semibold text-green-600">Sudah terdaftar</span>
                                        @else
                                            <button type="button" data-student-id="{{$student->id}}" class="single-register-btn px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition">Daftarkan</button>
                                        @endif
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
                                if (bulkForm) {
                                    bulkForm.addEventListener('submit', function(e) {
                                        const checked = bulkForm.querySelectorAll('input[name="student_ids[]"]:checked');
                                        if (!checked.length) {
                                            e.preventDefault();
                                            alert('Pilih minimal satu siswa untuk didaftarkan');
                                        } else {
                                            if (!confirm('Konfirmasi: daftarkan ' + checked.length + ' siswa ke ujian ini?')) {
                                                e.preventDefault();
                                            }
                                        }
                                    });
                                }

                                // Select all checkbox behavior
                                const selectAll = document.getElementById('selectAll');
                                if (selectAll) {
                                    selectAll.addEventListener('change', function() {
                                        const checkboxes = document.querySelectorAll('input[name="student_ids[]"]');
                                        checkboxes.forEach(cb => cb.checked = selectAll.checked);
                                    });
                                }

                                // Single register button handler (AJAX POST)
                                const singleButtons = document.querySelectorAll('.single-register-btn');
                                singleButtons.forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const studentId = this.getAttribute('data-student-id');
                                        if (!confirm('Konfirmasi: daftarkan siswa id=' + studentId + ' ke ujian ini?')) return;

                                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                                        const examId = '{{ $examId ?? 0 }}';
                                        const url = '{{ url("/exams") }}' + '/' + examId + '/oneparticipant';

                                        fetch(url, {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': token,
                                                'Accept': 'application/json'
                                            },
                                            body: JSON.stringify({ student_id: studentId })
                                        })
                                        .then(res => {
                                            if (!res.ok) throw res;
                                            return res.json().catch(() => ({ success: true }));
                                        })
                                        .then(data => {
                                            // On success, replace button with 'Sudah terdaftar'
                                            const td = btn.closest('td');
                                            if (td) td.innerHTML = '<span class="font-semibold text-green-600">Sudah terdaftar</span>';
                                        })
                                        .catch(async (err) => {
                                            let msg = 'Gagal mendaftarkan siswa.';
                                            try { const ebody = await err.json(); if (ebody.error) msg = ebody.error; } catch(e){}
                                            alert(msg);
                                        });
                                    });
                                });
                            });
                        </script>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
