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
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200" data-tab="ujian" @if(session('is_active') == '0') disabled @endif >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Mapel Ujian
                                    </button>
                                    {{-- <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200" data-tab="siswa" @if(session('is_active') == '0') disabled @endif >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Peserta
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="guru" @if(session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        Data Guru
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="kepala" @if(session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Data Kepala Sekolah
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="subjects" @if(session('is_active') == '0') disabled @endif>
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Data Mata Pelajaran
                                    </button> --}}
                                    @if(session('is_active') == '0')
                                        {{-- button aktifkan --}}
                                        <a href="{{ route('admin.exam.active', session('examid')) }}" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2" data-modal-target="aktifkanModal">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                            </svg>
                                            Aktifkan Ujian
                                        </a>
                                    @else
                                        <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" data-modal-target="nonAktifModal">
                                        {{-- <a href="{{route('admin.sekolah.nonaktif', $school->id)}}" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" title="Non-aktifkan Akun"> --}}
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            Non-Aktifkan Ujian
                                        {{-- </a> --}}
                                        </button>
                                    @endif
                                </nav>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="w-3/4">
                            <div class="tab-content">

                            <!-- Sekolah Tab -->
                            <div class="hidden tab-pane" id="ujian">
                                <div class="bg-white rounded-lg shadow">

                                     @if(session('success'))
                                        <div id="successMessage" class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">{!! session('success') !!}</div>
                                    @endif
                                    <div class="flex items-center justify-between p-4 border-b">
                                        <h3 class="text-lg font-medium">Data Materi Ujian</h3>
                                        <button class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-modal-target="addUjianModal">
                                            + Tambah Materi Ujian
                                        </button>
                                    </div>
                                    <div class="p-4">
                                        <div class="overflow-x-auto">
                                            {{-- style for the logo --}}
                                            <div class="overflow-x-auto">
                                                <x-input-error :messages="$errors->get('error')" class="mb-4" />
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">No</th>
                                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Materi Ujian</th>
                                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Bank Soal</th>
                                                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @forelse ($exam as $exam)
                                                    <tr>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$exam->id}}
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$exam->title}} <br/>
                                                            <span class="text-xs text-gray-500"> Durasi: {{$exam->duration}} menit | Tanggal Ujian: {{ \Carbon\Carbon::parse($exam->start_date)->format('d M Y H:i') }} - {{ \Carbon\Carbon::parse($exam->end_date)->format('d M Y H:i') }}</span>
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$exam->questions->count()}}
                                                        </td>
                                                        {{-- <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$exam->is_active}}
                                                        </td> --}}
                                                        <td class="px-6 py-4 text-xs text-gray-800 whitespace-nowrap">
                                                              <div class="flex gap-2">
                                                            @role('admin')
                                                            <form method="post" action="{{ route('admin.exams.question', $exam->id) }}">
                                                            @endrole

                                                            @role('super')
                                                            <form method="post" action="{{ route('super.exams.question', $exam->id) }}">
                                                            @endrole

                                                                @csrf
                                                                {{-- <input type="hidden" name="id" value="{{$exam->id}}"> --}}
                                                                {{-- smaller size for button --}}

                                                                <button class="px-3 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                                                                    Bank Soal
                                                                </button>
                                                            </form>

                                                                <button type=button class="px-4 py-2 text-sm font-medium text-blue-500 transition bg-white rounded-md ring-2 ring-blue-500 hover:ring-white hover:text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" data-modal-target="editUjianModal{{ $exam->id }}">Ubah</button>

                                                                @if($exam->is_active == 1)
                                                                    <button type=button class="px-4 py-2 text-sm font-medium text-red-500 transition bg-white rounded-md ring-2 ring-red-500 hover:bg-red-700 hover:text-white hover:ring-white focus:outline-none focus:ring-2 focus:ring-red-500" data-modal-target="arsipkanUjian{{ $exam->id }}">Arsipkan</button>
                                                                @else
                                                                    <button type=button class="px-4 py-2 text-sm font-medium text-green-500 transition bg-white rounded-md ring-2 ring-green-500 hover:text-white hover:bg-green-700 hover:ring-white focus:outline-none focus:ring-2 focus:ring-green-500" data-modal-target="arsipkanUjian{{ $exam->id }}">Aktifkan</button>
                                                                @endif

                                                            {{-- <a href="route('admin.siswa.edit', $student->id)" class="text-blue-600 hover:underline">Edit</a> --}}
                                                            <!--form action="{{route('admin.siswa.destroy', $exam->id)}}" method="POST" class="inline-block">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">Hapus</button>
                                                            </form-->
                                                        </div>

                                                        {{-- start edit modal --}}
                                                            <div id="editUjianModal{{ $exam->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto">
                                                                <div class="min-h-screen px-4 text-center">
                                                                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                                                                    <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                                                                        <div class="flex items-center justify-between pb-3 border-b">
                                                                            <h3 class="text-lg font-medium text-gray-900">Ubah Materi Ujian</h3>
                                                                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('editUjianModal{{ $exam->id }}')">
                                                                                <span class="sr-only">Close</span>
                                                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        {{-- <form  class="mt-4"> --}}
                                                                        @role('admin')
                                                                            <form id="editUjianForm" class="mt-4" action="{{ route('admin.exams.update') }}" method="post" enctype="multipart/form-data">
                                                                        @endrole
                                                                        @role('super')
                                                                            <form id="editUjianForm" class="mt-4" action="{{ route('super.exams.update') }}" method="post" enctype="multipart/form-data">
                                                                        @endrole
                                                                                     @method('PUT')
                                                                                    @csrf
                                                                                    {{-- @if (session('error'))
                                                                                        <div class="mb-4 text-red-600">
                                                                                            {{ session('error') }}
                                                                                        </div>
                                                                                    @endif --}}
                                                                                    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                                                                                        <div>
                                                                                        <label for="title" class="block text-sm font-medium text-gray-700">Judul Ujian</label>
                                                                                        <input type="text" name="title" id="title" value="{{ $exam->title }}" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                                                                                    </div>
                                                                                    <div>
                                                                                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                                                                                        <textarea name="description" id="description" rows="3" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ $exam->description }}</textarea>
                                                                                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                                                                                    </div>
                                                                                    <div>
                                                                                        <label for="duration" class="block text-sm font-medium text-gray-700">Tanggal Ujian</label>
                                                                                        <input type="datetime-local" name="start_date" id="start_date" value="{{ $exam->start_date }}" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                                        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                                                                                    </div>
                                                                                    <div>
                                                                                        <label for="duration" class="block text-sm font-medium text-gray-700">Tanggal Akhir Ujian</label>
                                                                                        <input type="datetime-local" name="end_date" id="end_date" value="{{ $exam->end_date }}" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                                        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                                                                                    </div>
                                                                                    <div>
                                                                                        <label for="duration" class="block text-sm font-medium text-gray-700">Durasi</label>
                                                                                        <input type="number" name="duration" id="duration" value="{{ $exam->duration }}" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                                        <x-input-error :messages="$errors->get('duration')" class="mt-1" />
                                                                                    </div>
                                                                                    <div>
                                                                                        <label for="total_quest" class="block text-sm font-medium text-gray-700">Total Soal Ditampilkan</label>
                                                                                        <input type="number" name="total_quest" id="total_quest" value="{{ $exam->total_quest }}" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                                        <x-input-error :messages="$errors->get('total_quest')" class="mt-1" />
                                                                                    </div>
                                                                                    <div>
                                                                                        <label for="score_minimal" class="block text-sm font-medium text-gray-700">Nilai Minimal</label>
                                                                                        <input type="number" name="score_minimal" id="score_minimal" value="{{ old('score_minimal', $exam->score_minimal) }}" required
                                                                                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                                                        <x-input-error :messages="$errors->get('score_minimal')" class="mt-1" />
                                                                                    </div>

                                                                                    <input type="hidden" name="examid" value="{{ $exam->id }}">

                                                                                    <!-- Tombol Submit -->
                                                                                    <div class="pt-4">
                                                                                        <button type="submit"
                                                                                                class="w-full px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
                                                                                            Ubah Data
                                                                                        </button>
                                                                                    </div>
                                                                                </form>
                                                                        {{-- </form> --}}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        {{-- end edit modal --}}

                                                        {{-- start arsipkan feature --}}
                                                            <div id="arsipkanUjian{{ $exam->id }}" class="fixed inset-0 z-50 hidden overflow-y-auto">
                                                                <div class="min-h-screen px-4 text-center">
                                                                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
                                                                    <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                                                                        <div class="flex items-center justify-between pb-3 border-b">
                                                                            @if ($exam->is_active == 1)
                                                                                <h3 class="text-lg font-medium text-gray-900">Arsipkan Ujian</h3>
                                                                            @else
                                                                                <h3 class="text-lg font-medium text-gray-900">Aktifkan Ujian</h3>
                                                                            @endif

                                                                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('arsipkanUjian{{ $exam->id }}')">
                                                                                <span class="sr-only">Close</span>
                                                                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                                </svg>
                                                                            </button>
                                                                        </div>
                                                                        {{-- <form  class="mt-4"> --}}
                                                                        @role('admin')
                                                                            <form id="arsipkanUjianForm" class="mt-4" action="{{ route('admin.exams.archive') }}" method="post" enctype="multipart/form-data">
                                                                        @endrole
                                                                        @role('super')
                                                                            <form id="arsipkanUjianForm" class="mt-4" action="{{ route('super.exams.archive') }}" method="post" enctype="multipart/form-data">
                                                                        @endrole

                                                                                    @csrf
                                                                                    {{-- @if (session('error'))
                                                                                        <div class="mb-4 text-red-600">
                                                                                            {{ session('error') }}
                                                                                        </div>
                                                                                    @endif --}}
                                                                                   <p class="text-sm text-gray-700">Apakah Anda yakin ingin Mengarsipkan/Meng-Aktifkan <strong>{{ strtoupper( $exam->title ) }}</strong> ?</p>
                                                                                <div class="pt-4">
                                                                                    <input type="hidden" name="examid" value="{{ $exam->id }}">
                                                                                @if($exam->is_active == 1)
                                                                                    <button type="submit" class="px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700">
                                                                                        Arsipkan
                                                                                    </button>
                                                                                @else
                                                                                    <button type="submit" class="px-4 py-2 text-white transition bg-green-600 rounded hover:bg-green-700">
                                                                                        Aktifkan
                                                                                    </button>
                                                                                @endif
                                                                                    {{-- tombol batal --}}
                                                                                    <button type="button" class="px-4 py-2 mt-2 text-white transition bg-gray-600 rounded hover:bg-gray-700" onclick="closeModal('arsipkanUjian{{ $exam->id }}')">
                                                                                        Batal
                                                                                    </button>
                                                                                </div>
                                                                                </form>
                                                                        {{-- </form> --}}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                         </td>
                                                        </tr>
                                                        {{-- end of arsipkan feature --}}
                                                        @empty
                                                        <tr>
                                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data materi ujian.</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
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

<!-- Add Ujian Modal -->
<div id="addUjianModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Tambah Materi Ujian</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('addUjianModal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- <form  class="mt-4"> --}}
            @role('admin')
                <form id="addUjianForm" class="mt-4" action="{{ route('admin.exam.store') }}" method="post" enctype="multipart/form-data">
            @endrole
            @role('super')
                <form id="addUjianForm" class="mt-4" action="{{ route('super.exam.store') }}" method="post" enctype="multipart/form-data">
            @endrole
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                            <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Judul Ujian</label>
                            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('title')" class="mt-1" />
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="description" id="description" rows="3" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('address') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-1" />
                        </div>
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700">Tanggal Ujian</label>
                            <input type="datetime-local" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                        </div>
                         <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700">Tanggal Akhir Ujian</label>
                            <input type="datetime-local" name="end_date" id="end_date" value="{{ old('end_date') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                        </div>
                        <div>
                            <label for="duration" class="block text-sm font-medium text-gray-700">Durasi</label>
                            <input type="number" name="duration" id="duration" value="{{ old('duration') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('duration')" class="mt-1" />
                        </div>
                        <div>
                            <label for="total_quest" class="block text-sm font-medium text-gray-700">Total Soal Ditampilkan</label>
                            <input type="number" name="total_quest" id="total_quest" value="{{ old('total_quest') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('total_quest')" class="mt-1" />
                        </div>
                        <div>
                            <label for="score_minimal" class="block text-sm font-medium text-gray-700">Nilai Minimal</label>
                            <input type="number" name="score_minimal" id="score_minimal" value="{{ old('score_minimal') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('score_minimal')" class="mt-1" />
                        </div>

                        <!-- Tombol Submit -->
                        <div class="pt-4">
                            <button type="submit"
                                    class="w-full px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
                                Simpan Data
                            </button>
                        </div>
                    </form>
            {{-- </form> --}}
        </div>
    </div>
</div>


<!-- Non Aktifkan Sekolah Modal -->
<div id="nonAktifModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Non-Aktifkan Ujian</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('nonAktifModal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- <form  class="mt-4"> --}}
                <form id="nonAktifForm" class="mt-4" action="{{ route('admin.exam.inactive') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <!-- Tombol Submit -->
                        <p class="text-sm text-gray-700">Apakah Anda yakin ingin menonaktifkan <strong>{{ strtoupper(session('examname')) }}</strong> ini?</p>
                        <div class="pt-4">
                            <input type="hidden" name="examid" value="{{ session('examid') }}">
                            <button type="submit"
                                    class="w-full px-4 py-2 text-white transition bg-red-600 rounded hover:bg-red-700">
                                Lanjut Non-Aktifkan
                            </button>
                            {{-- tombol batal --}}
                            <button type="button" class="w-full px-4 py-2 mt-2 text-white transition bg-gray-600 rounded hover:bg-gray-700" onclick="closeModal('nonAktifModal')">
                                Batal
                            </button>
                        </div>
                    </form>
            {{-- </form> --}}
        </div>
    </div>
</div>

@push('scripts')
<script>
        document.addEventListener('DOMContentLoaded', function() {
        // Handle success message auto-hide
        const successMessage = document.getElementById('successMessage');
        if (successMessage) {
            setTimeout(function() {
                successMessage.style.opacity = '0';
                setTimeout(function() {
                    successMessage.style.display = 'none';
                }, 500); // Wait for fade out animation to complete
            }, 3000); // Show message for 3 seconds
        }

        // Check for tab from session and click the corresponding button
        @if(session('tab'))
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
        document.querySelectorAll('button[data-modal-target]').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal-target');
                openModal(modalId);
            });
        });

        // Add click handler for tab buttons and their associated modals
        document.querySelectorAll('[data-tab]').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');

                // Show the tab content
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
        });        // Tab functionality
        // Show first tab by default
        const firstTab = document.querySelector('[data-tab]');
        if (firstTab) {
            const firstTabId = firstTab.getAttribute('data-tab');
            showTab(firstTabId);
        }

        // Add click handlers for tabs
        document.querySelectorAll('[data-tab]').forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const tabId = this.getAttribute('data-tab');
                showTab(tabId);
            });
        });

        // Initialize forms
        initializeForms();
    });

        function showTab(tabId) {
            // Hide all tabs and remove active classes
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.add('hidden');
            });

            document.querySelectorAll('[data-tab]').forEach(tab => {
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

                // Handle specific tab actions
                switch(tabId) {
                    case 'siswa':
                        // Ensure siswa modal button is properly configured
                        const addSiswaBtn = selectedTab.querySelector('button[data-modal-target="addSiswaModal"]');
                        if (addSiswaBtn) {
                            addSiswaBtn.onclick = () => openModal('addSiswaModal');
                        }
                        break;
                    case 'guru':
                        // Ensure guru modal button is properly configured
                        const addGuruBtn = selectedTab.querySelector('button[data-modal-target="addGuruModal"]');
                        if (addGuruBtn) {
                            addGuruBtn.onclick = () => openModal('addGuruModal');
                        }
                        break;
                    case 'kepala':
                        // No modal needed for kepala sekolah as it's inline form
                        break;
                    case 'subjects':
                        const addSubjectBtn = selectedTab.querySelector('button[data-modal-target="addSubjectModal"]');
                        if (addGuruBtn) {
                            addSubjectBtn.onclick = () => openModal('addSubjectModal');
                        }
                        break;
                }
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

        // Add click handlers for buttons that open modals
        document.querySelectorAll('[data-modal-target]').forEach(button => {
            button.addEventListener('click', function() {
                const modalId = this.getAttribute('data-modal-target');
                openModal(modalId);
            });
        });
</script>
@endpush

</x-app-layout>
