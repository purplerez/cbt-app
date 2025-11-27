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
                            <div class="flex items-center space-x-3">
                                @role('kepala')
                                    <a href="{{route('kepala.student.create')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition">
                                @endrole

                                @role('guru')
                                    <a href="{{route('guru.student.create')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition">
                                @endrole
                                    + Tambah
                                </a>

                                <!-- Excel Upload Button and Form -->
                                @role('kepala')
                                <form action="{{ route('kepala.student.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-2">
                                @endrole

                                @role('guru')
                                <form action="{{ route('guru.student.import') }}" method="POST" enctype="multipart/form-data" class="flex items-center space-x-2">
                                @endrole
                                @csrf
                                    <input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls" class="hidden" onchange="this.form.submit()">
                                    <button type="button" onclick="document.getElementById('excel_file').click()"
                                        class="flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                        </svg>
                                        Import Excel
                                    </button>
                                    @role('kepala')
                                    <a href="{{ route('kepala.student.template') }}"
                                     class="flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download Template
                                    </a>
                                    @endrole

                                    @role('guru')
                                    <a href="{{ route('guru.student.template') }}"
                                     class="flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download Template
                                    </a>
                                    @endrole

                                </form>
                            </div>

                    {{-- end of import button --}}
                            @role('kepala')
                            <form action="{{ route('kepala.students') }}" method="get" class="flex items-center space-x-2">
                            @endrole

                            @role('guru')
                            <form action="{{ route('guru.students') }}" method="get" class="flex items-center space-x-2">
                            @endrole
                                <label for="grade_id" class="sr-only">Kelas </label>
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
                    <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                <th class="w-5 px-4 py-2 border">No</th>
                                <th class="w-5 px-4 py-2 border">NISN</th>
                                <th class="w-40 px-4 py-2 border">Nama Siswa</th>
                                <th class="px-4 py-2 border w-11">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $index => $student)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $student->id }}</td>
                                    <td class="px-4 py-2 border">{{ $student->nis }}</td>
                                    <td class="px-4 py-2 border">{{ $student->name }}</td>
                                    <td class="px-4 py-2 border">
                                        {{-- @role('kepala')
                                        <a href="{{ route('kepala.student.edit', $student->id)}}" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Edit</a>
                                        @endrole

                                        @role('guru')
                                        <a href="{{ route('guru.student.edit', $student->id)}}" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Edit</a>
                                        @endrole --}}


                                        @role('kepala')
                                        <form action="{{route('kepala.student.destroy', $student->id)}}" method="POST" class="inline-block">
                                        @endrole

                                        @role('guru')
                                        <form action="{{route('guru.student.destroy', $student->id)}}" method="POST" class="inline-block">
                                        @endrole
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition">Delete</button>
                                        </form>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Data Kosong</td>
                                </tr>
                            @endforelse

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
