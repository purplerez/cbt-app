<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Master Data Siswa'.session('schoolname')) }}
            </h2>
            @role('kepala')
                <a href="{{ route('kepala.student.create') }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Siswa Baru
                </a>
            @endrole
            @role('guru')
                <a href="{{ route('guru.student.create') }}"
                   class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Siswa Baru
                </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Filters Section -->
            <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <!-- Filter and Import Form -->
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                        <!-- Grade Filter -->
                        @role('kepala')
                        <form action="{{ route('kepala.students') }}" method="get" class="flex items-end gap-2 md:col-span-2">
                        @endrole
                        @role('guru')
                        <form action="{{ route('guru.students') }}" method="get" class="flex items-end gap-2 md:col-span-2">
                        @endrole
                            <div class="flex-1">
                                <label for="grade_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                                <select name="grade_id" id="grade_id" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Semua Kelas</option>
                                    @foreach ($grade as $g)
                                        <option value="{{ $g->id }}" @if(isset($selectedGrade) && $selectedGrade == $g->id) selected @endif>{{ $g->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-gray-800 border border-transparent rounded-md hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                Filter
                            </button>
                        </form>

                        <!-- Import Buttons -->
                        <div class="flex items-end gap-2 md:col-span-2">
                            @role('kepala')
                            <form action="{{ route('kepala.student.import') }}" method="POST" enctype="multipart/form-data" class="flex-4" >
                            @endrole
                            @role('guru')
                            <form action="{{ route('guru.student.import') }}" method="POST" enctype="multipart/form-data" class="flex-4">
                            @endrole
                                @csrf
                                <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" class="hidden" onchange="this.form.submit()">
                                <button type="button" onclick="document.getElementById('excel_file').click()"
                                    class="inline-flex items-center justify-center w-full px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out bg-green-600 border border-transparent rounded-md hover:bg-green-700" style="width: 100%;">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Import-XLS
                                </button>
                            </form>
                            @role('kepala')
                            <a href="{{ route('kepala.student.template') }}"
                             class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Template
                            </a>
                            @endrole
                            @role('guru')
                            <a href="{{ route('guru.student.template') }}"
                             class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Template
                            </a>
                            @endrole
                        </div>
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
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">No</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">NISN</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Siswa</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($students as $index => $student)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ $student->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $student->nis }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $student->name }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                @role('kepala')
                                                <a href="{{ route('kepala.student.edit', $student->id) }}"
                                                   class="px-3 py-1 text-xs font-semibold text-white bg-yellow-600 rounded-md hover:bg-yellow-700">
                                                    Edit
                                                </a>
                                                @endrole
                                                @role('guru')
                                                <a href="{{ route('guru.student.edit', $student->id) }}"
                                                   class="px-3 py-1 text-xs font-semibold text-white bg-yellow-600 rounded-md hover:bg-yellow-700">
                                                    Edit
                                                </a>
                                                @endrole
                                                @role('kepala')
                                                <form action="{{ route('kepala.student.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?');">
                                                @endrole
                                                @role('guru')
                                                <form action="{{ route('guru.student.destroy', $student->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus siswa ini?');">
                                                @endrole
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="px-3 py-1 text-xs font-semibold text-white bg-red-600 rounded-md hover:bg-red-700">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
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

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{-- $students->links() --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
