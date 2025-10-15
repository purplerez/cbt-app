<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Penempatan Siswa ke Ruangan Ujian') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            <!-- Success/Error Messages -->
            @if (session('success'))
                <div class="p-4 mb-4 rounded-md bg-green-50">
                    <div class="flex">
                        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 mb-4 rounded-md bg-red-50">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            @foreach ($errors->all() as $error)
                                <p class="text-sm font-medium text-red-800">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900">Pilih Ujian</h3>
                    @role('kepala')
                        <form method="GET" action="{{ route('kepala.room-assignment.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @endrole
                    @role('guru')
                        <form method="GET" action="{{ route('guru.room-assignment.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @endrole
                        <div>
                            <label for="exam_type_id" class="block text-sm font-medium text-gray-700">Tipe Ujian</label>
                            <select name="exam_type_id" id="exam_type_id" required onchange="this.form.submit()"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Tipe Ujian</option>
                                @foreach($examTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('exam_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if(request('exam_type_id'))
                        <div>
                            <label for="exam_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                            <select name="exam_id" id="exam_id" onchange="this.form.submit()"
                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Mata Pelajaran</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>
                                        {{ $exam->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                    </form>
                </div>
            </div>

            @if(request()->filled('exam_id') && $studentsAvailable->isNotEmpty())
                <!-- Auto Assign Button -->
                <div class="mb-6 overflow-hidden border-2 border-blue-200 shadow-sm bg-blue-50 sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-blue-900">Distribusi Otomatis</h3>
                                <p class="mt-1 text-sm text-blue-700">
                                    Distribusikan {{ $studentsAvailable->count() }} siswa secara merata ke {{ $rooms->count() }} ruangan yang tersedia
                                </p>
                            </div>
                            @role('kepala')
                            <form method="POST" action="{{ route('kepala.room-assignment.auto-assign') }}"
                                  onsubmit="return confirm('Yakin ingin mendistribusikan siswa secara otomatis? Penugasan sebelumnya akan dihapus.')">
                            @endrole

                            @role('guru')
                            <form method="POST" action="{{ route('guru.room-assignment.auto-assign') }}"
                                  onsubmit="return confirm('Yakin ingin mendistribusikan siswa secara otomatis? Penugasan sebelumnya akan dihapus.')">
                            @endrole

                                @csrf
                                <input type="hidden" name="exam_type_id" value="{{ request('exam_type_id') }}">
                                <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                                <input type="hidden" name="school_id" value="{{ session('school_id') }}">
                                <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Distribusi Otomatis
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Manual Assignment Section -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Penugasan Manual</h3>
                        @role('kepala')
                            <form method="POST" action="{{ route('kepala.room-assignment.assign') }}" id="assignmentForm">
                        @endrole

                        @role('guru')
                            <form method="POST" action="{{ route('guru.room-assignment.assign') }}" id="assignmentForm">
                        @endrole
                            @csrf
                            <input type="hidden" name="exam_type_id" value="{{ request('exam_type_id') }}">

                            <div class="mb-4">
                                <label for="room_id" class="block text-sm font-medium text-gray-700">Pilih Ruangan Tujuan</label>
                                <select name="room_id" id="room_id" required
                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Pilih Ruangan</option>
                                    @foreach($rooms as $room)
                                        <option value="{{ $room->id }}">
                                            {{ $room->name }} (Kapasitas: {{ $room->capacity ?? 'Tidak terbatas' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Pilih Siswa</label>
                                    <div class="flex space-x-2">
                                        <button type="button" onclick="selectAll()" class="text-sm text-blue-600 hover:text-blue-800">Pilih Semua</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" onclick="deselectAll()" class="text-sm text-blue-600 hover:text-blue-800">Hapus Pilihan</button>
                                    </div>
                                </div>

                                <div class="overflow-x-auto border border-gray-300 rounded-md">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                                </th>
                                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">NIS</th>
                                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama</th>
                                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($studentsAvailable as $student)
                                                <tr class="{{ $student->assigned_room_id ? 'bg-gray-50' : 'hover:bg-gray-50' }}">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox">
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $student->nis }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $student->name }}</td>
                                                    <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $student->grade_name }}</td>
                                                    <td class="px-6 py-4 text-sm whitespace-nowrap">
                                                        @if($student->assigned_room_id)
                                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 text-green-800 bg-green-100 rounded-full">
                                                                Sudah ditugaskan (Room #{{ $student->assigned_room_id }})
                                                            </span>
                                                        @else
                                                            <span class="inline-flex px-2 text-xs font-semibold leading-5 text-gray-800 bg-gray-100 rounded-full">
                                                                Belum ditugaskan
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Tugaskan ke Ruangan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Room Summary -->
                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Ringkasan Ruangan</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            @foreach($rooms as $room)
                                @php
                                    $assignedCount = $studentsAvailable->where('assigned_room_id', $room->id)->count();
                                    $byGrade = $studentsAvailable->where('assigned_room_id', $room->id)->groupBy('grade_name');
                                @endphp
                                <div class="p-4 border border-gray-300 rounded-lg {{ $assignedCount > 0 ? 'bg-blue-50 border-blue-300' : '' }}">
                                    <h4 class="font-semibold text-gray-900">{{ $room->nama_ruangan }}</h4>
                                    <p class="mt-1 text-sm text-gray-600">
                                        Total: <span class="font-semibold">{{ $assignedCount }}</span> siswa
                                        @if($room->kapasitas)
                                            / {{ $room->kapasitas }} kapasitas
                                        @endif
                                    </p>
                                    @if($byGrade->isNotEmpty())
                                        <div class="mt-2 text-xs text-gray-600">
                                            @foreach($byGrade as $gradeName => $students)
                                                <div>{{ $gradeName }}: {{ $students->count() }} siswa</div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @elseif(request()->filled('exam_id'))
                <div class="overflow-hidden shadow-sm bg-yellow-50 sm:rounded-lg">
                    <div class="p-6 text-center">
                        <p class="text-yellow-800">Tidak ada siswa yang terdaftar untuk ujian ini.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleAll(checkbox) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = checkbox.checked);
        }

        function selectAll() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = true);
            document.getElementById('selectAllCheckbox').checked = true;
        }

        function deselectAll() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            document.getElementById('selectAllCheckbox').checked = false;
        }

        // Form validation
        document.getElementById('assignmentForm')?.addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 siswa untuk ditugaskan!');
                return false;
            }

            const roomSelect = document.getElementById('room_id');
            if (!roomSelect.value) {
                e.preventDefault();
                alert('Pilih ruangan tujuan terlebih dahulu!');
                return false;
            }

            return confirm(`Tugaskan ${checkboxes.length} siswa ke ruangan yang dipilih?`);
        });
    </script>
    @endpush
</x-app-layout>
