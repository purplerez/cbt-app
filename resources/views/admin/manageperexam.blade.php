<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Manajemen Ujian :  ') . session('examname') }}

        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex">
                        <!-- Sidebar Menu -->
                        <div class="w-1/4 pr-6">
                            <div class="p-4 bg-white rounded-lg shadow">
                                <div class="flex items-center mb-4 space-x-4">
                                    {{-- <img src="{{ Storage::url($school->logo)}}" alt="School Logo" class="w-12 h-12 rounded-full"> --}}
                                    <div>
                                        {{--   <h3 class="text-lg font-medium">{{ $school->name }}</h3>
                                        <p class="text-sm text-gray-500">NPSN: {{ $school->npsn }}</p> --}}
                                    </div>
                                </div>
                                <nav class="space-y-2">
                                    <button type="button"
                                        class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200"
                                        data-tab="banksoal" @if (session('is_active') == '0') disabled @endif>
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                            class="w-5 h-5 mr-2">
                                            <path stroke-linecap="round" stroke-width="2" stroke-linejoin="round"
                                                d="M6.429 9.75 2.25 12l4.179 2.25m0-4.5 5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0 4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0-5.571 3-5.571-3" />
                                        </svg>
                                        Bank Soal {{ session('perexamname') }}
                                    </button>
                                    <button type="button"
                                        class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200"
                                        data-tab="peserta" @if (session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Peserta
                                    </button>
                                    <button type="button"
                                        class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200"
                                        data-tab="logujian">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-width="2" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-width="2" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        Log Ujian
                                    </button>
                                    <button type="button"
                                        class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200"
                                        data-tab="nilaiujian">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-width="2" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-width="2" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        Nilai
                                    </button>
                                    {{--  <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"> --}}
                                    @role('admin')
                                        <a href="{{ route('admin.exams.question.exit') }}"
                                            class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                            title="Non-aktifkan Akun">
                                    @endrole
                                    @role('super')
                                            <a href="{{ route('super.exams.question.exit') }}"
                                                class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                                title="Non-aktifkan Akun">
                                    @endrole
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Kembali Ke Ujian
                                        </a>
                                        {{-- </button> --}}
                                        {{-- <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="guru" @if (session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        Data Guru
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="kepala" @if (session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Data Kepala Sekolah
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="subjects" @if (session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Data Mata Pelajaran
                                    </button> --}}

                                </nav>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="w-3/4">
                            <div class="tab-content">

                                <!-- Bank Soal Tab -->
                                <div class="hidden tab-pane" id="banksoal">
                                    <div class="bg-white rounded-lg shadow">

                                        @if (session('success'))
                                            <div id="successMessage"
                                                class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">
                                                {!! session('success') !!}</div>
                                        @endif
                                        <div class="flex items-center justify-between p-4 border-b">
                                            <h3 class="text-lg font-medium">Data Materi Ujian
                                                {{ session('perexamname') }} - {{session('perexamstatus')}}</h3>
                                            <div class="flex items-center space-x-2">
                                                <!-- Word Upload Button and Form -->
                                                @if (session('perexamstatus') == 1)
                                                    @php
                                                        $importRoute = auth()->user()->hasRole('super')
                                                            ? route('super.exams.questions.import-word', session('perexamid'))
                                                            : route('admin.exams.questions.import-word', session('perexamid'));
                                                        $templateRoute = auth()->user()->hasRole('super')
                                                            ? route('super.exams.questions.template-word', session('perexamid'))
                                                            : route('admin.exams.questions.template-word', session('perexamid'));
                                                        $exportRoute = auth()->user()->hasRole('super')
                                                            ? route('super.exams.questions.export', session('perexamid'))
                                                            : route('admin.exams.questions.export', session('perexamid'));
                                                    @endphp
                                                    <form id="wordImportFormNew"
                                                        action="{{ route('admin.questions.import') }}" method="POST"
                                                        enctype="multipart/form-data"
                                                        class="flex items-center space-x-2">
                                                        @csrf
                                                        <input type="hidden" name="exam_id"
                                                            value="{{ session('perexamid') }}">
                                                        <input type="file" name="word_file" id="word_file_new"
                                                            accept=".doc,.docx" class="hidden"
                                                            onchange="this.form.submit()">
                                                        <button type="button"
                                                            onclick="document.getElementById('word_file_new').click()"
                                                            class="flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                                                                </path>
                                                            </svg>
                                                            Import Word
                                                        </button>
                                                        <a href="{{ route('admin.exams.questions.template-word', session('perexamid')) }}"
                                                            class="flex items-center px-3 py-1.5 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition">
                                                            <svg class="w-4 h-4 mr-1" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24"
                                                                xmlns="http://www.w3.org/2000/svg">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                                                                </path>
                                                            </svg>
                                                            Download Template
                                                        </a>
                                                    </form>

                                                    <!-- Export Button -->
                                                    <a href="{{ route('admin.exams.questions.export', session('perexamid')) }}"
                                                        class="px-4 py-2 text-sm font-medium text-white transition bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500">
                                                        Export
                                                    </a>

                                                    <!-- Add Question Button -->
                                                    <button type="button"
                                                        class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                        data-tab="soal">
                                                        + Tambah Soal
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <div class="overflow-x-auto">
                                                {{-- style for the logo --}}
                                                <div class="overflow-x-auto">
                                                    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                                                    <table class="min-w-full divide-y divide-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr>
                                                                <th
                                                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                    No</th>
                                                                <th
                                                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                    Soal</th>
                                                                <th
                                                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                    Jenis Soal</th>
                                                                <th
                                                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                    Point</th>
                                                                <th
                                                                    class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                    Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="bg-white divide-y divide-gray-200">
                                                            @forelse ($questions as $q)
                                                                <tr>
                                                                    <td
                                                                        class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                                        {{ $q->id }}
                                                                    </td>
                                                                    <td class="px-6 py-4 text-sm text-gray-800">
                                                                        <div class="whitespace-wrap">
                                                                            {{ $q->question_text }}</div>
                                                                        @if ($q->question_image)
                                                                            <img src="{{ Storage::url($q->question_image) }}"
                                                                                alt="Question"
                                                                                class="max-w-xs mt-2 rounded-md max-h-32">
                                                                        @endif
                                                                    </td>
                                                                    <td
                                                                        class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                                        <!-- span for question type label according to the question type id different type has different background colour -->
                                                                        @if ($q->question_type_id == 0)
                                                                            <span
                                                                                class="inline-block px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                                                                                PG
                                                                            </span>
                                                                        @elseif($q->question_type_id == 1)
                                                                            <span
                                                                                class="inline-block px-2 py-1 text-xs font-semibold text-green-700 bg-green-100 rounded-full">
                                                                                PG Kompleks
                                                                            </span>
                                                                        @elseif($q->question_type_id == 2)
                                                                            <span
                                                                                class="inline-block px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-100 rounded-full">
                                                                                Benar/Salah
                                                                            </span>
                                                                        @else
                                                                            <span
                                                                                class="inline-block px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-100 rounded-full">
                                                                                Isian Singkat
                                                                            </span>
                                                                        @endif
                                                                        {{-- {{ $q->question_type_id }} --}}
                                                                    </td>
                                                                    <td
                                                                        class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                                        {{ $q->points }}
                                                                    </td>
                                                                    {{-- <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                                {{$exam->is_active}}
                                                            </td> --}}
                                                                    <td
                                                                        class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">

                                                                        {{-- <input type="hidden" name="id" value="{{$exam->id}}"> --}}
                                                                        <button
                                                                            class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                                                                            data-modal-target="editSoalModal{{ $q->id }}"
                                                                            onclick="openEditSoalModal({{ $q->id }})"
                                                                            @if (session('perexamstatus') == 0) disabled @endif>
                                                                            Ubah
                                                                        </button>

                                                                        {{-- <a href="route('admin.siswa.edit', $student->id)" class="text-blue-600 hover:underline">Edit</a> --}}
                                                                        <form
                                                                            action="{{ route('admin.exams.questions.destroy', $q->id) }}"
                                                                            method="POST" class="inline-block">
                                                                            @csrf
                                                                            @method('DELETE')
                                                                            <button type="submit"
                                                                                class="px-4 py-2 text-sm font-medium text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                                                                onclick="return confirm('Apakah Anda yakin ingin menghapus soal ini?')"
                                                                                @if (session('perexamstatus') == 0) disabled @endif>
                                                                                Hapus</button>
                                                                        </form>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="4"
                                                                        class="px-6 py-4 text-center text-gray-500">
                                                                        Belum ada data soal.</td>
                                                                </tr>
                                                            @endforelse

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Single edit modal template - content loaded via AJAX --}}
                                        <div id="editSoalModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
                                            <div class="min-h-screen px-4 text-center">
                                                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                                                <div class="inline-block w-full max-w-2xl p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                                                    <div class="flex items-center justify-between pb-3 border-b">
                                                        <h3 class="text-lg font-medium text-gray-900">Ubah Soal</h3>
                                                        <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('editSoalModal')">
                                                            <span class="sr-only">Close</span>
                                                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                    <div id="modalContent" class="mt-4 overflow-y-auto max-h-96">
                                                        <p class="text-center text-gray-500">Loading...</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Soal Tab -->
                                <div class="hidden tab-pane" id="soal">
                                    <div class="bg-white rounded-lg shadow">
                                        @if (session('success'))
                                            <div id="successMessage"
                                                class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">
                                                {!! session('success') !!}</div>
                                        @endif
                                        <div class="flex items-center justify-between p-4 border-b">
                                            <h3 class="text-lg font-medium">Bank Soal</h3>
                                            {{-- <button class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-modal-target="addSiswaModal">
                                            + Tambah Siswa
                                        </button> --}}
                                        </div>
                                        <div class="p-4">
                                            <div class="overflow-x-auto">
                                                <x-input-error :messages="$errors->get('error')" class="mb-4" />

                                                <!-- Import/Export Buttons -->
                                                <div class="flex gap-2 mb-4">

                                                </div>


                                                <form
                                                    action="{{ route('admin.exams.question.store', session('perexamid')) }}"
                                                    method="post" enctype="multipart/form-data"
                                                    class="mb-4 question-form">



                                                    @csrf
                                                    <div class="space-y-4">
                                                        <div>
                                                            <label for="question_text"
                                                                class="block text-sm font-medium text-gray-700">Pertanyaan</label>
                                                            <textarea id="question_text" name="question_text" rows="3"
                                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm tinymce-editor focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                                        </div>
                                                        <div>
                                                            <label for="question_image"
                                                                class="block text-sm font-medium text-gray-700">Gambar
                                                                Soal (Opsional)</label>
                                                            <input type="file" id="question_image"
                                                                name="question_image" accept="image/*"
                                                                class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                                                onchange="previewQuestionImage(this)">
                                                            <div id="question_image_preview" class="hidden mt-2">
                                                                <img src="" alt="Preview"
                                                                    class="max-w-xs rounded-md max-h-48">
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <label for="options"
                                                                class="block text-sm font-medium text-gray-700">Pilihan
                                                                Jawaban</label>
                                                            <div id="choices-container" class="space-y-2">
                                                                <div class="choice-item" data-choice-id="1">
                                                                    <div class="flex items-start gap-2">
                                                                        <div class="flex-1">
                                                                            <textarea name="choices[1]" rows="3"
                                                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm tinymce-editor focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                                                                            <div class="mt-2">
                                                                                <label
                                                                                    class="block text-xs font-medium text-gray-600"
                                                                                    for="gambar">Gambar Pilihan
                                                                                    (Opsional)</label>
                                                                                <input type="file"
                                                                                    name="choice_images[1]"
                                                                                    accept="image/*"
                                                                                    class="block w-full mt-1 text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                                                                    onchange="previewChoiceImage(this, 1)">
                                                                                <div id="choice_image_preview_1"
                                                                                    class="hidden mt-1">
                                                                                    <img src="" alt="Preview"
                                                                                        class="max-w-xs rounded-md max-h-32">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <button type="button"
                                                                            class="mt-1 text-red-500 remove-choice hover:text-red-700"
                                                                            onclick="removeChoice(this)">
                                                                            <svg class="w-4 h-4" fill="none"
                                                                                viewBox="0 0 24 24"
                                                                                stroke="currentColor">
                                                                                <path stroke-linecap="round"
                                                                                    stroke-linejoin="round"
                                                                                    stroke-width="2"
                                                                                    d="M6 18L18 6M6 6l12 12" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <button type="button" id="add-choice"
                                                                class="px-3 py-1 mt-2 text-sm text-white bg-blue-500 rounded hover:bg-blue-600">
                                                                + Add Choice
                                                            </button>
                                                        </div>

                                                        {{-- answer --}}
                                                        <div>
                                                            <label for="answer_key"
                                                                class="block text-sm font-medium text-gray-700">Kunci
                                                                Jawaban</label>
                                                            <div id="answer-key-container"></div>
                                                        </div>

                                                        {{--  points --}}
                                                        <div>
                                                            <label for="points"
                                                                class="block text-sm font-medium text-gray-700">Point</label>
                                                            <input type="number" name="points"
                                                                class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                        </div>

                                                        {{--  button --}}
                                                        <div>
                                                            <button type="submit"
                                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">
                                                                Simpan
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Peserta Tab -->
                                <div class="hidden tab-pane" id="peserta">
                                    <div class="bg-white rounded-lg shadow">
                                        @if (session('success'))
                                            <div id="successMessage"
                                                class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">
                                                {!! session('success') !!}</div>
                                        @endif
                                        <div class="flex items-center justify-between p-4 border-b">
                                            <h3 class="text-lg font-medium">Data Peserta</h3>

                                            {{-- dropdown sekolah --}}
                                            <!-- School Dropdown -->
                                            <div class="mb-6">
                                                <label for="school_filter"
                                                    class="block text-sm font-medium text-gray-700">Pilih
                                                    Madrasah</label>
                                                <select id="school_filter"
                                                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                                    <option value="">Pilih Madrasah</option>
                                                    @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}">{{ $school->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <input type="hidden" id="current-exam-id"
                                                    value="{{ session('perexamid') }}">
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                NIS</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Nama</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Kelas</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="student-list-body"
                                                        class="bg-white divide-y divide-gray-200">
                                                        <!-- Students will be loaded here dynamically -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Log Ujian Tab -->
                                <div class="hidden tab-pane" id="logujian">
                                    <div class="bg-white rounded-lg shadow">
                                        <!-- Hidden exam ID field -->
                                        <input type="hidden" id="current-exam-id"
                                            value="{{ session('perexamid') }}">
                                        <!-- Add route URL for exam session detail -->
                                        <script>
                                            window.examSessionDetailUrl = "{{ route('admin.exam-sessions.detail', ['examSession' => ':id']) }}";
                                        </script>

                                        @if (session('success'))
                                            <div id="successMessage"
                                                class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">
                                                {!! session('success') !!}</div>
                                        @endif

                                        <input type="hidden" id="current-exam-id"
                                            value="{{ session('perexamid') }}">

                                        <!-- Statistics Cards -->
                                        <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-3">
                                            <div class="p-4 bg-white rounded-lg shadow">
                                                <h3 class="text-lg font-semibold text-gray-700">Total Peserta</h3>
                                                <p id="total-participants" class="text-2xl font-bold text-blue-600">0
                                                </p>
                                            </div>
                                            <div class="p-4 bg-white rounded-lg shadow">
                                                <h3 class="text-lg font-semibold text-gray-700">Peserta Aktif</h3>
                                                <p id="active-participants" class="text-2xl font-bold text-green-600">
                                                    0</p>
                                            </div>
                                            <div class="p-4 bg-white rounded-lg shadow">
                                                <h3 class="text-lg font-semibold text-gray-700">Sudah Submit</h3>
                                                <p id="submitted-participants"
                                                    class="text-2xl font-bold text-gray-600">0</p>
                                            </div>
                                        </div>

                                        <!-- Filter Section -->
                                        <div class="p-4 border-b">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-medium">Log Aktifitas Peserta</h3>
                                                <div class="w-64">
                                                    <label for="school_filter_logs"
                                                        class="block text-sm font-medium text-gray-700">Filter
                                                        Madrasah</label>
                                                    <select id="school_filter_logs"
                                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                        <option value="">Semua Madrasah</option>
                                                        @foreach ($schools as $school)
                                                            <option value="{{ $school->id }}">{{ $school->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Log Table -->
                                        <div class="p-4">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                NIS</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Nama</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Kelas</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Status</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Aktifitas Terakhir</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="participant-logs-body"
                                                        class="bg-white divide-y divide-gray-200">
                                                        <!-- Data will be populated by JavaScript -->
                                                        <tr>
                                                            <td colspan="6"
                                                                class="px-6 py-4 text-center text-gray-500">
                                                                Memuat data...
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detail Log Tab -->
                                <div class="hidden tab-pane" id="detaillog">
                                    <div class="bg-white rounded-lg shadow">
                                        @if (session('success'))
                                            <div id="successMessage"
                                                class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">
                                                {!! session('success') !!}</div>
                                        @endif
                                        <div class="flex items-center justify-between p-4 border-b">
                                            <h3 class="text-lg font-medium">Detail Log Peserta</h3>

                                        </div>
                                        <div class="p-4">
                                            <div class="overflow-x-auto">
                                                <x-input-error :messages="$errors->get('error')" class="mb-4" />

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nilai Tab -->
                                <div class="hidden tab-pane" id="nilaiujian">
                                    <div class="bg-white rounded-lg shadow">
                                        <div class="flex items-center justify-between p-4 border-b">
                                            <h3 class="text-lg font-medium">Daftar Nilai Siswa</h3>
                                            <div class="flex items-center space-x-2">
                                                <select id="school_filter_nilai"
                                                    class="block w-48 px-3 py-2 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">Semua Madrasah</option>
                                                    @foreach ($schools as $school)
                                                        <option value="{{ $school->id }}">{{ $school->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <select id="grade_filter_nilai"
                                                    class="block w-48 px-3 py-2 text-sm border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                    <option value="">Semua Kelas</option>
                                                </select>
                                                <form method="GET"
                                                    action="{{ route('admin.exam.scores.export', ['exam' => $exam->id]) }}"
                                                    class="inline">
                                                    <input type="hidden" name="school_id" id="export_school_id">
                                                    <input type="hidden" name="grade_id" id="export_grade_id">
                                                    <button type="submit"
                                                        class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                        Export PDF
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                NIS</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Nama Lengkap</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Kelas</th>
                                                            <th
                                                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                                                Nilai</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="nilai-list-body"
                                                        class="bg-white divide-y divide-gray-200">
                                                        <tr>
                                                            <td colspan="4"
                                                                class="px-6 py-4 text-sm text-center text-gray-500">
                                                                Pilih madrasah dan kelas untuk melihat nilai
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        {{-- END OF MAIN CONTENT --}}
                    </div>
                </div>
            </div>
        </div>

        @push('styles')
            <style>
                /* Minimal Force Submit Button Styles */
                .force-submit-btn {
                    background-color: #dc2626;
                    color: white;
                }

                .force-submit-btn:hover {
                    background-color: #b91c1c;
                }
            </style>
        @endpush

        @push('scripts')
            <script>
                window.examSessionDetailUrl = "{{ route('admin.exam-sessions.detail', ['examSession' => ':id']) }}";
            </script>
            <!-- Participant Logs Management -->
            <script src="{{ asset('js/participant-logs.js') }}"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const schoolDropdown = document.getElementById('school_filter');
                    if (schoolDropdown) {
                        schoolDropdown.addEventListener('change', function() {
                            const schoolId = this.value;
                            if (schoolId) {
                                fetchStudents(schoolId);
                            } else {
                                document.getElementById('student-list-body').innerHTML = '';
                            }
                        });
                    }
                });

                function fetchStudents(schoolId) {
                    const examstatus = {{ session('perexamstatus') }};
                    const examId = '{{ $exam->id }}'; // Get the exam ID from the current page
                    fetch(`/api/admin/schools/${schoolId}/students?exam_id=${examId}`)
                        .then(response => response.json())
                        .then(data => {
                            const tbody = document.getElementById('student-list-body');
                            tbody.innerHTML = '';

                            data.forEach(student => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">${student.nis} - ${examstatus}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">${student.name}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">${student.grade?.name || '-'}</td>
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        ${student.is_assigned ?
                            '<span class="text-green-600">Sudah Terdaftar</span>' :
                            `<button onclick="addStudentToExam(${student.id})" class="text-blue-600 hover:text-blue-900"
                                                                                                                                        ${examstatus == 0 ? 'disabled' : ''}
                                                                                                                                        >
                                                                                                                                                        Tambah ke Ujian
                                                                                                                                                    </button>`
                        }
                    </td>
                `;
                                tbody.appendChild(row);
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error fetching students');
                        });
                }

                function addStudentToExam(studentId) {
                    const examId = '{{ $exam->id }}';
                    fetch('/api/admin/exams/add-student', {
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
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Siswa berhasil ditambahkan ke ujian');
                                // Refresh the student list
                                fetchStudents(document.getElementById('school_filter').value);
                            } else {
                                alert(data.message || 'Gagal menambahkan siswa ke ujian');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error adding student to exam');
                        });
                }
            </script>

            <!-- Student List Management -->
            <script>
                window.examStatus = {{ session('perexamstatus') }};
            </script>
            <script src="{{ asset('js/student-list.js') }}"></script>
            <!-- Exam Scores Management -->
            <script src="{{ asset('js/exam-scores.js') }}"></script>
            <!-- edit js -->
            <script>
                // Preview functions for edit modals
                function previewEditQuestionImage(input, questionId) {
                    const preview = document.getElementById('edit_question_image_preview_' + questionId);
                    if (!preview) return;

                    const img = preview.querySelector('img');
                    if (!img) return;

                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            preview.classList.remove('hidden');
                        }
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        preview.classList.add('hidden');
                    }
                }

                function previewEditChoiceImage(input, questionId, choiceId) {
                    const preview = document.getElementById('edit_choice_image_preview_' + questionId + '_' + choiceId);
                    if (!preview) return;

                    const img = preview.querySelector('img');
                    if (!img) return;

                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            preview.classList.remove('hidden');
                        }
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        preview.classList.add('hidden');
                    }
                }

                // Global variable to store current answer keys
                let currentAnswerKeys = {};
                // Global variable to store current answer keys
                // let currentAnswerKeys = {};

                function updateAnswerKey(input, questionId) {
                    if (!currentAnswerKeys[questionId]) {
                        currentAnswerKeys[questionId] = [];
                    }

                    if (input.type === 'radio') {
                        currentAnswerKeys[questionId] = [input.value];
                    } else {
                        if (input.checked) {
                            currentAnswerKeys[questionId].push(input.value);
                        } else {
                            currentAnswerKeys[questionId] = currentAnswerKeys[questionId].filter(val => val !== input.value);
                        }
                    }

                    // Update hidden input
                    const hiddenInput = document.getElementById(`answer_key_${questionId}`);
                    if (hiddenInput) {
                        hiddenInput.value = JSON.stringify(currentAnswerKeys[questionId]);
                    }

                    console.log('Updated answer key:', currentAnswerKeys[questionId]);
                }

                function initEditForm(questionId, choices, answerKey, questionType) {
                    console.log('Init Edit Form with:', {
                        questionId,
                        choices,
                        answerKey,
                        questionType
                    });

                    // Initialize current answer key for this question
                    currentAnswerKeys[questionId] = Array.isArray(answerKey) ? answerKey : (answerKey ? [answerKey] : []);

                    // Parse choices and answerKey if they're strings
                    if (typeof choices === 'string') {
                        try {
                            choices = JSON.parse(choices);
                            console.log('Parsed choices:', choices);
                        } catch (e) {
                            console.error('Failed to parse choices:', e);
                            choices = null;
                        }
                    }
                    if (typeof answerKey === 'string') {
                        try {
                            answerKey = JSON.parse(answerKey);
                            console.log('Parsed answerKey:', answerKey);
                        } catch (e) {
                            console.error('Failed to parse answerKey:', e);
                            answerKey = answerKey || ''; // Keep string value for essay type
                        }
                    }
                    let editChoiceCounter = choices ? Object.keys(choices).length : 0;
                    const editContainer = document.getElementById(`edit-choices-container-${questionId}`);
                    const editAnswerKeyContainer = document.getElementById(`edit-answer-key-container-${questionId}`);
                    const addChoiceButton = document.getElementById(`edit-add-choice-${questionId}`);

                    if (!editContainer || !editAnswerKeyContainer) return;

                    function renderEditAnswerKey() {
                        if (!editAnswerKeyContainer) return;
                        editAnswerKeyContainer.innerHTML = '';
                        let choiceElements = editContainer.querySelectorAll('.edit-choice-item');

                        if (choiceElements.length === 0) {
                            // Essay mode
                            editAnswerKeyContainer.innerHTML = `
                <textarea name="answer_key" rows="3"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm tinymce-editor">${answerKey || ''}</textarea>
            `;
                            return;
                        }

                        // Normalize answerKey to array format
                        let normalizedAnswerKey = [];
                        if (Array.isArray(answerKey)) {
                            // Convert to integers
                            normalizedAnswerKey = answerKey.map(k => {
                                const parsed = parseInt(k);
                                return !isNaN(parsed) ? parsed : k;
                            });
                        } else if (answerKey) {
                            // answerKey might be a string like "A" or "B" - convert to index
                            if (typeof answerKey === 'string') {
                                // If it's a letter (A, B, C, D), convert to index (0, 1, 2, 3)
                                if (answerKey.match(/^[A-Z]$/)) {
                                    let index = answerKey.charCodeAt(0) - 64; // A=0, B=1, etc
                                    normalizedAnswerKey = [index];
                                } else {
                                    normalizedAnswerKey = [parseInt(answerKey) || answerKey];
                                }
                            } else {
                                normalizedAnswerKey = [parseInt(answerKey) || answerKey];
                            }
                        }

                        console.log('Normalized answer key:', normalizedAnswerKey);
                        console.log('Choice elements:', choiceElements.length);
                        console.log('Question type:', questionType);

                        // Determine input type:
                        // questionType 1 = PG Kompleks (multiple answers) → checkbox
                        // questionType 2 = True/False → radio
                        // questionType 0 = PG (single answer) → radio
                        let inputType = 'radio';
                        if (questionType === 1 || questionType === '1') {
                            inputType = 'checkbox'; // PG Kompleks uses checkbox
                        } else if (choiceElements.length === 2) {
                            inputType = 'radio'; // True/False uses radio
                        }

                        let checkboxes = '';
                        choiceElements.forEach((choice, index) => {
                            // Use string ID to match form input names (choices[1], choices[2], etc)
                            let id = choice.dataset.choiceId.toString();
                            let text = choice.querySelector('textarea').value.trim() || `Pilihan ${index+1}`;

                            // Normalize answer key for comparison (convert all to strings for consistency)
                            let normalizedForComparison = normalizedAnswerKey.map(k => k.toString());

                            // Check if this choice is in the answer key
                            let checked = normalizedForComparison.includes(id);

                            console.log(`Choice ${id}: text="${text}", checked=${checked}`);

                            checkboxes += `
                <label class="flex items-center gap-2">
                    <input type="${inputType}" name="answer_key[]" value="${id}" ${checked ? 'checked' : ''}>
                    ${text}
                </label>
            `;
                        });
                        editAnswerKeyContainer.innerHTML = `<div class="flex flex-col gap-1">${checkboxes}</div>`;
                    }

                    function removeChoice(button) {
                        button.parentElement.remove();
                        renderEditAnswerKey();
                    }

                    // Add click handler for the add choice button
                    if (addChoiceButton) {
                        addChoiceButton.addEventListener('click', function() {
                            editChoiceCounter++;
                            let div = document.createElement('div');
                            div.classList.add('edit-choice-item');
                            div.dataset.choiceId = editChoiceCounter.toString();
                            div.innerHTML = `
                <div class="flex items-start gap-2">
                    <div class="flex-1">
                        <textarea name="choices[${editChoiceCounter}]" rows="3"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm tinymce-editor"></textarea>
                        <div class="mt-2">
                            <label class="block text-xs font-medium text-gray-600">Gambar Pilihan (Opsional)</label>
                            <input type="file" name="choice_images[${editChoiceCounter}]" accept="image/*"
                                class="block w-full mt-1 text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                                onchange="previewEditChoiceImage(this, ${questionId}, ${editChoiceCounter})">
                            <div id="edit_choice_image_preview_${questionId}_${editChoiceCounter}" class="hidden mt-1">
                                <img src="" alt="Preview" class="max-w-xs rounded-md max-h-32">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="mt-1 text-red-500 hover:text-red-700" onclick="this.closest('.edit-choice-item').remove(); initEditForm(${questionId}, null, ${JSON.stringify(answerKey)})">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            `;
                            editContainer.appendChild(div);
                            // Re-initialize TinyMCE for the new textarea
                            setTimeout(() => {
                                tinymce.remove();
                                tinymce.init({
                                    selector: '.tinymce-editor:not(.tox-hidden)',
                                    license_key: 'gpl',
                                    theme: 'silver',
                                    plugins: [
                                        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                                        'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
                                        'fullscreen', 'insertdatetime', 'media', 'table', 'paste',
                                        'help',
                                        'wordcount'
                                    ],
                                    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image media | code fullscreen help',
                                    menubar: 'file edit view insert format tools table help',
                                    branding: false,
                                    height: 300,
                                    body_class: 'mce-content-body',
                                    content_style: 'body { font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif; font-size:14px; line-height:1.6; }',
                                    paste_as_text: false,
                                    valid_elements: '+*[*]',
                                    valid_children: '+*[*]'
                                });
                            }, 100);
                            renderEditAnswerKey();
                        });
                    }

                    // Add input handler for choices container
                    if (editContainer) {
                        editContainer.addEventListener('input', renderEditAnswerKey);
                    }

                    // Initial render
                    renderEditAnswerKey();
                }

                // Initialize edit form when modal is opened
                function openEditSoalModal(questionId) {
                    // Show modal with loading state
                    const modal = document.getElementById('editSoalModal');
                    const modalContent = document.getElementById('modalContent');
                    if (!modal || !modalContent) {
                        alert('Element modal tidak ditemukan di halaman.');
                        return;
                    }

                    // Show loading
                    modal.classList.remove('hidden');
                    modalContent.innerHTML = '<p class="text-center text-gray-500">Loading...</p>';

                    //csrf token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    if (!csrfToken) {
                        modalContent.innerHTML = '<p class="text-center text-red-500">Error: CSRF token tidak ditemukan. Refresh halaman dan coba lagi.</p>';
                        return;
                    }

                    // Tentukan prefix route (admin/super) secara dinamis
                    let prefix = '';
                    if (window.location.pathname.includes('/admin/')) {
                        prefix = '/admin';
                    } else if (window.location.pathname.includes('/super/')) {
                        prefix = '/super';
                    }
                    const url = `${prefix}/exams/questions/${questionId}/modal`;

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'text/html',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error('Gagal load modal: ' + text);
                            });
                        }
                        return response.text();
                    })
                    .then(html => {
                        // Insert HTML into modal
                        modalContent.innerHTML = html;

                        // Initialize TinyMCE for newly added textarea elements
                        if (typeof tinymce !== 'undefined') {
                            // First remove any existing tinymce instances
                            tinymce.remove();

                            // Initialize for all tinymce-editor textareas
                            const textareas = modalContent.querySelectorAll('.tinymce-editor');
                            textareas.forEach(textarea => {
                                tinymce.init({
                                    target: textarea,
                                    height: 280,
                                    menubar: false,
                                    plugins: 'lists link table code',
                                    toolbar: 'bold italic | bullist numlist | link table | code',
                                    branding: false,
                                    statusbar: false
                                });
                            });
                        }

                        // Load answer key choices if not essay type
                        if (typeof loadAnswerKeyChoices === 'function') {
                            loadAnswerKeyChoices(questionId);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading modal:', error);
                        modalContent.innerHTML = '<p class="text-center text-red-500">Gagal memuat modal. Silakan coba lagi.<br>' + error.message + '</p>';
                    });
                }

                function loadAnswerKeyChoices(questionId) {
                    const questionTypeEl = document.getElementById('questionType');
                    if (!questionTypeEl) return;

                    const questionType = parseInt(questionTypeEl.value);
                    if (questionType === 3) return; // Skip for essay type

                    const choicesContainer = document.getElementById('edit-choices-container-modal');
                    const answerKeyContainer = document.getElementById('edit-answer-key-container-modal');

                    if (!choicesContainer || !answerKeyContainer) return;

                    // Get current choices and answer key from form
                    const choiceElements = choicesContainer.querySelectorAll('.edit-choice-item');
                    if (choiceElements.length === 0) return;

                    // Get answer key from hidden or existing form data
                    let answerKeyInputs = document.querySelectorAll('input[name="answer_key[]"]');
                    let selectedKeys = Array.from(answerKeyInputs).map(el => el.value);

                    // Determine input type based on question type
                    let inputType = 'radio';
                    if (questionType === 1) {
                        inputType = 'checkbox'; // PG Kompleks uses checkbox
                    }

                    let checkboxesHTML = '';
                    choiceElements.forEach((choice, index) => {
                        const choiceId = choice.dataset.choiceId.toString();
                        const choiceText = choice.querySelector('textarea').value.trim() || `Pilihan ${index+1}`;
                        const isChecked = selectedKeys.includes(choiceId);

                        checkboxesHTML += `
                            <label class="flex items-center gap-2 mb-2">
                                <input type="${inputType}" name="answer_key[]" value="${choiceId}" ${isChecked ? 'checked' : ''}>
                                <span class="text-sm">${choiceText.substring(0, 50)}</span>
                            </label>
                        `;
                    });

                    answerKeyContainer.innerHTML = `<div class="flex flex-col gap-1">${checkboxesHTML}</div>`;
                }


            </script>
            <!-- end edit js -->

            <script>
                // Image preview functions
                function previewQuestionImage(input) {
                    const preview = document.getElementById('question_image_preview');
                    const img = preview.querySelector('img');

                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            preview.classList.remove('hidden');
                        }
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        preview.classList.add('hidden');
                    }
                }

                function previewChoiceImage(input, choiceId) {
                    const preview = document.getElementById('choice_image_preview_' + choiceId);
                    const img = preview.querySelector('img');

                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            img.src = e.target.result;
                            preview.classList.remove('hidden');
                        }
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        preview.classList.add('hidden');
                    }
                }

                let choiceCounter = 1;
                const container = document.getElementById('choices-container');
                const answerKeyContainer = document.getElementById('answer-key-container');

                // Add choice
                document.getElementById('add-choice').addEventListener('click', function() {
                    choiceCounter++;
                    let div = document.createElement('div');
                    div.classList.add('choice-item');
                    div.dataset.choiceId = choiceCounter;
                    div.innerHTML = `
            <div class="flex items-start gap-2">
                <div class="flex-1">
                    <textarea name="choices[${choiceCounter}]" rows="3"
                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm tinymce-editor"></textarea>
                    <div class="mt-2">
                        <label class="block text-xs font-medium text-gray-600">Gambar Pilihan (Opsional)</label>
                        <input type="file" name="choice_images[${choiceCounter}]" accept="image/*"
                            class="block w-full mt-1 text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100"
                            onchange="previewChoiceImage(this, ${choiceCounter})">
                        <div id="choice_image_preview_${choiceCounter}" class="hidden mt-1">
                            <img src="" alt="Preview" class="max-w-xs rounded-md max-h-32">
                        </div>
                    </div>
                </div>
                <button type="button" class="mt-1 text-red-500 remove-choice hover:text-red-700" onclick="removeChoice(this)">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        `;
                    container.appendChild(div);
                    // Re-initialize TinyMCE for the new textarea
                    setTimeout(() => {
                        tinymce.remove();
                        tinymce.init({
                            selector: '.tinymce-editor:not(.tox-hidden)',
                            license_key: 'gpl',
                            theme: 'silver',
                            plugins: [
                                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                                'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
                                'fullscreen', 'insertdatetime', 'media', 'table', 'paste', 'help',
                                'wordcount'
                            ],
                            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image media | code fullscreen help',
                            menubar: 'file edit view insert format tools table help',
                            branding: false,
                            height: 300,
                            body_class: 'mce-content-body',
                            content_style: 'body { font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif; font-size:14px; line-height:1.6; }',
                            paste_as_text: false,
                            valid_elements: '+*[*]',
                            valid_children: '+*[*]'
                        });
                    }, 100);
                    renderAnswerKey();
                });

                // Keep track of selected answer keys
                let selectedAnswerKeys = [];

                // Remove choice
                function removeChoice(button) {
                    // Save current selections before removing
                    selectedAnswerKeys = Array.from(answerKeyContainer.querySelectorAll('input[type="checkbox"]:checked')).map(cb =>
                        cb.value);
                    button.closest('.choice-item').remove();
                    renderAnswerKey();
                }

                // Render Answer Key automatically
                function renderAnswerKey() {
                    answerKeyContainer.innerHTML = '';
                    let choices = container.querySelectorAll('.choice-item');

                    if (choices.length === 0) {
                        // Essay mode
                        answerKeyContainer.innerHTML = `
                <textarea name="answer_key" rows="3"
                    class="block w-full mt-1 border-gray-300 rounded-md shadow-sm tinymce-editor"></textarea>
            `;
                        return;
                    }

                    // Multiple-choice mode → render checkboxes (for multiple correct answers)
                    let checkboxes = '';
                    choices.forEach((choice, index) => {
                        let id = choice.dataset.choiceId || index + 1;
                        // correspond the text I input in textarea in answer options
                        let text = choice.querySelector('textarea').value.trim() || `Pilihan ${index+1}`;
                        let isChecked = selectedAnswerKeys.includes(id.toString()) ? 'checked' : '';

                        checkboxes += `
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="answer_key[]" value="${id}" ${isChecked}>
                    ${text}
                </label>
            `;
                    });
                    answerKeyContainer.innerHTML = `<div class="flex flex-col gap-1">${checkboxes}</div>`;

                    // Update selected keys when checkboxes change
                    answerKeyContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.addEventListener('change', function() {
                            selectedAnswerKeys = Array.from(answerKeyContainer.querySelectorAll(
                                'input[type="checkbox"]:checked')).map(cb => cb.value);
                        });
                    });
                }

                // Initial render
                renderAnswerKey();
            </script>




            <script>
                // Cache DOM elements for better performance
                const tabPanes = document.querySelectorAll('.tab-pane');
                const tabButtons = document.querySelectorAll('[data-tab]');
                const modalButtons = document.querySelectorAll('button[data-modal-target]');

                document.addEventListener('DOMContentLoaded', function() {
                    // Handle success message auto-hide
                    const successMessage = document.getElementById('successMessage');
                    if (successMessage) {
                        setTimeout(function() {
                            successMessage.style.opacity = '0';
                            setTimeout(function() {
                                successMessage.style.display = 'none';
                            }, 500);
                        }, 3000);
                    }

                    // Check for tab from session and click the corresponding button
                    @if (session('tab'))
                        document.querySelector('button[data-tab="{{ session('tab') }}"]').click();
                    @endif

                    // Modal functions
                    window.closeModal = function(modalId) {
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            modal.classList.add('hidden');
                            document.body.classList.remove('overflow-hidden');
                        }
                    }

                    window.openModal = function(modalId) {
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            modal.classList.remove('hidden');
                            document.body.classList.add('overflow-hidden');
                        }
                    }

                    // Handle clicking outside modal to close
                    document.querySelectorAll('.fixed.inset-0').forEach(modal => {
                        modal.addEventListener('click', function(event) {
                            if (event.target === this || event.target.classList.contains('bg-gray-500')) {
                                closeModal(this.id);
                            }
                        });
                    });

                    // Add click handler for add buttons within tabs
                    modalButtons.forEach(button => {
                        button.addEventListener('click', function(e) {
                            e.preventDefault();
                            const modalId = this.getAttribute('data-modal-target');
                            openModal(modalId);
                        });
                    });

                    // Add click handler for tab buttons - SINGLE handler, not duplicate
                    tabButtons.forEach(tab => {
                        tab.addEventListener('click', function(e) {
                            e.preventDefault();
                            const tabId = this.getAttribute('data-tab');
                            showTab(tabId);

                            // If there's an associated modal button in the tab, enable it
                            const tabPane = document.getElementById(tabId);
                            if (tabPane) {
                                const modalButton = tabPane.querySelector('button[data-modal-target]');
                                if (modalButton) {
                                    modalButton.removeAttribute('disabled');
                                }
                            }
                        });
                    });

                    // Show first tab by default
                    const firstTab = tabButtons[0];
                    if (firstTab) {
                        const firstTabId = firstTab.getAttribute('data-tab');
                        showTab(firstTabId);
                    }
                });

                function showTab(tabId) {
                    // Cache query results instead of querying multiple times
                    tabPanes.forEach(pane => {
                        pane.classList.add('hidden');
                    });

                    tabButtons.forEach(tab => {
                        tab.classList.remove('bg-gray-100');
                        tab.classList.add('hover:bg-gray-200');
                    });

                    // Show selected tab and add active class
                    const selectedTab = document.getElementById(tabId);
                    const tabButton = document.querySelector(`[data-tab="${tabId}"]`);

                    if (selectedTab && tabButton) {
                        selectedTab.classList.remove('hidden');
                        tabButton.classList.add('bg-gray-100');
                        tabButton.classList.remove('hover:bg-gray-200');
                    }
                }
                /* function initializeForms() {
                     // Add submit handlers for forms
                     ['addSiswaForm', 'addGuruForm', 'kepalaSekolahForm'].forEach(formId => {
                         const form = document.getElementById(formId);
                         /* if (form) {
                             form.addEventListener('submit', function(e) {
                                 e.preventDefault();
                                 // Get form data
                                 const formData = new FormData(this);

                                 // Add CSRF token to form data
                                 formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                                 // Submit form using fetch
                                 fetch(this.getAttribute('action') || window.location.href, {
                                     method: 'POST',
                                     body: formData,
                                     headers: {
                                         'X-Requested-With': 'XMLHttpRequest'
                                     }
                                 })
                                 .then(response => response.json())
                                 .then(data => {
                                     if (data.success) {
                                         // Close modal if it's a modal form
                                         if (this.closest('.modal')) {
                                             closeModal(this.closest('.modal').id);
                                         }
                                         // Refresh data or show success message
                                         alert(data.message || 'Data berhasil disimpan');
                                     } else {
                                         alert(data.message || 'Terjadi kesalahan');
                                     }
                                 })
                                 .catch(error => {
                                     console.error('Error:', error);
                                     alert('Terjadi kesalahan saat menyimpan data');
                                 });
                             });
                         }
                     });
                 }
                 */
                // Prevent modal close when clicking modal content
                document.querySelectorAll('.modal-content').forEach(content => {
                    content.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                });

                // Add click handlers for buttons that open modals - already handled above in DOMContentLoaded
                // Removed duplicate event listeners for performance improvement

                // Handle Word file upload
                function handleWordFileUpload(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('word_file', file);

                    // Show loading message
                    showLoadingMessage('Memproses file Word...');

                    const examId = '{{ session('perexamid') }}';
                    const route = '{{ route('admin.exams.questions.import-word', '__EXAM_ID__') }}'.replace('__EXAM_ID__', examId);

                    fetch(route, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show confirmation modal with questions preview
                                showWordImportPreview(data.questions, examId);
                            } else {
                                alert('Error: ' + (data.message || 'Terjadi kesalahan saat memproses file'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan saat upload file');
                        });
                }

                function showLoadingMessage(message) {
                    const div = document.createElement('div');
                    div.id = 'loading-message';
                    div.className = 'fixed top-4 right-4 bg-blue-500 text-white px-6 py-3 rounded shadow-lg';
                    div.textContent = message;
                    document.body.appendChild(div);
                }

                function hideLoadingMessage() {
                    const div = document.getElementById('loading-message');
                    if (div) div.remove();
                }

                function showWordImportPreview(questions, examId) {
                    hideLoadingMessage();

                    // Create modal for preview
                    const modal = document.createElement('div');
                    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
                    modal.innerHTML = `
                        <div class="max-w-4xl p-6 overflow-y-auto bg-white rounded-lg shadow-xl max-h-96">
                            <h2 class="mb-4 text-xl font-bold">Preview Soal yang Diimport (${questions.length} soal)</h2>
                            <div class="space-y-4">
                                ${questions.map((q, idx) => `
                                                                                                <div class="py-2 pl-4 border-l-4 border-blue-500">
                                                                                                    <p class="font-semibold">${idx + 1}. ${q.question_text.substring(0, 100)}...</p>
                                                                                                    <p class="text-sm text-gray-600">
                                                                                                        Tipe: ${getQuestionTypeName(q.question_type)} |
                                                                                                        Pilihan: ${Object.keys(q.choices || {}).length}
                                                                                                    </p>
                                                                                                </div>
                                                                                            `).join('')}
                            </div>
                            <div class="flex justify-end mt-6 space-x-4">
                                <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-600">Batal</button>
                                <button onclick="saveWordQuestions(${examId}, '${btoa(JSON.stringify(questions))}')" class="px-4 py-2 text-white bg-green-600 rounded hover:bg-green-700">Simpan Semua Soal</button>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                }

                function getQuestionTypeName(type) {
                    const types = {
                        0: 'Pilihan Ganda',
                        1: 'Pilihan Ganda Kompleks',
                        2: 'Benar/Salah',
                        3: 'Essay'
                    };
                    return types[type] || 'Tidak diketahui';
                }

                function saveWordQuestions(examId, questionsData) {
                    const questions = JSON.parse(atob(questionsData));
                    @php
                        $saveRoute = auth()->user()->hasRole('super')
                            ? route('super.exams.questions.save-word-questions')
                            : route('admin.exams.questions.save-word-questions');
                    @endphp
                    const route = '{{ $saveRoute }}';

                    showLoadingMessage('Menyimpan soal...');

                    fetch(route, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                exam_id: examId,
                                questions: questions
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoadingMessage();
                            if (data.success) {
                                alert(data.message);
                                // Reload page to show new questions
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                alert('Error: ' + (data.message || 'Terjadi kesalahan'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            hideLoadingMessage();
                            alert('Terjadi kesalahan saat menyimpan soal');
                        });
                }

                // Handle form submission for TinyMCE
                document.querySelectorAll('.question-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        // Trigger TinyMCE to save content to textarea
                        if (typeof tinymce !== 'undefined') {
                            tinymce.triggerSave();
                        }
                    });
                });
            </script>
        @endpush

</x-app-layout>
