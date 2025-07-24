<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Manajemen Sekolah - ') . $school->name }}
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
                                    <img src="{{ Storage::url($school->logo)}}" alt="School Logo" class="w-12 h-12 rounded-full">
                                    <div>
                                        <h3 class="text-lg font-medium">{{ $school->name }}</h3>
                                        <p class="text-sm text-gray-500">NPSN: {{ $school->npsn }}</p>
                                    </div>
                                </div>
                                <nav class="space-y-2">
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition bg-gray-100 rounded-md hover:bg-gray-200" data-tab="siswa">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        Data Siswa
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="guru">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                        </svg>
                                        Data Guru
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="kepala">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Data Kepala Sekolah
                                    </button>
                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-gray-700 transition rounded-md hover:bg-gray-200" data-tab="subjects">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Data Mata Pelajaran
                                    </button>
                                    <a href="{{route('admin.sekolah.nonaktif', $school->id)}}" class="flex items-center w-full px-4 py-2 text-sm font-medium text-left text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2" title="Non-aktifkan Akun">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Non-Aktifkan Akun
                                    </a>
                                </nav>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="w-3/4">
                            <div class="tab-content">
                            <!-- Siswa Tab -->
                            <div class="hidden tab-pane" id="siswa">
                                <div class="bg-white rounded-lg shadow">
                                    <div class="flex items-center justify-between p-4 border-b">
                                        <h3 class="text-lg font-medium">Data Siswa</h3>
                                        <button class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-modal-target="addSiswaModal">
                                            + Tambah Siswa
                                        </button>
                                    </div>
                                    <div class="p-4">
                                        <div class="overflow-x-auto">
                                            <x-input-error :messages="$errors->get('error')" class="mb-4" />
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">NIS</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @forelse ($students as $student)
                                                    <tr>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$student->nis}}
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$student->name}}
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            {{$student->grade->name}}
                                                        </td>
                                                        <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                                            <button class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-modal-target="editSiswaModal">
                                                                Ubah
                                                            </button>
                                                            <a href="{{-- route('admin.siswa.edit', $student->id) --}}" class="text-blue-600 hover:underline">Edit</a>
                                                            <form action="{{route('admin.siswa.destroy', $student->id)}}" method="POST" class="inline-block">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="ml-2 text-red-600 hover:underline" onclick="return confirm('Apakah Anda yakin ingin menghapus siswa ini?')">Hapus</button>
                                                            </form>
                                                        </tr>

                                                        {{-- add modal edit siswa --}}
<!-- Edit Siswa Modal -->
<div id="editSiswaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Tambah Siswa Baru</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('addSiswaModal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- <form  class="mt-4"> --}}
                <form id="addSiswaForm" class="mt-4" action="{{ route('admin.students.update') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                            <div>
                            <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
                            <input type="text" name="nis" id="nis" value="{{$student->nis}}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('nis')" class="mt-1" />
                            <input type="hidden" name="old_nis" value="{{$student->nis}}">
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{$student->name }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <label for="grade_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="grade_id" id="grade_id" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Kelas</option>
                                @foreach ($grade as $g)
                                    <option value="{{ $g->id }}" @if($student->grade_id == $g->id) selected @endif>{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('grade_id')" class="mt-1" />
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <select name="gender" id="gender" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="L" @if($student->gender == 'L') selected @endif >Laki - Laki</option>
                                    <option value="P" @if($student->gender == 'P') selected @endif>Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                        </div>
                        <div>
                            <label for="p_birth" class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                            <input type="text" name="p_birth" id="p_birth" value="{{ $student->p_birth }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('p_birth')" class="mt-1" />
                        </div>
                        <div>
                            <label for="d_birth" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                            <input type="date" name="d_birth" id="d_birth" value="{{ $student->d_birth }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('d_birth')" class="mt-1" />
                        </div>
                        <div>
                            <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                            <label for="photo" class="block text-sm font-medium text-gray-700">
                                <img src="{{ Storage::url($student->photo) }}" alt="Current Photo" class="w-12 h-12 mb-2 rounded-full">
                            </label>
                            <input type="file" name="photo" id="photo"  required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea name="address" id="address" rows="3" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ $student->address }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-1" />
                        </div>
                        {{-- <input type="hidden" name="school_id" value="{{ $school->id }}"> --}}



                        {{-- <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Sekolah</label>
                            <select name="school_id" id="school_id" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Sekolah</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('school_id')" class="mt-1" />
                        </div> --}}

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

                                                        {{-- end of edit modal siswa --}}

                                                    @empty
                                                           <tr>
                                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data Siswa.</td>
                                                            </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Guru Tab -->
                            <div class="hidden tab-pane" id="guru">
                                <div class="bg-white rounded-lg shadow">
                                    <div class="flex items-center justify-between p-4 border-b">
                                        <h3 class="text-lg font-medium">Data Guru</h3>
                                        <button class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" onclick="openModal('addGuruModal')">
                                            + Tambah Guru
                                        </button>
                                    </div>
                                    <div class="p-4">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">NIS</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Kelas</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data guru akan ditampilkan disini -->
                                                    @forelse ($teachers as $teacher)
                                                        <tr>
                                                            <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->nip }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->name }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->subject->name ?? 'N/A' }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                <button class="text-blue-600 hover:text-blue-900">Edit</button>
                                                                <button class="text-red-600 hover:text-red-900">Hapus</button>
                                                            </td>
                                                        </tr>

                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada data guru.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kepala Sekolah Tab -->
                            <div class="hidden tab-pane" id="kepala">
                                <div class="bg-white rounded-lg shadow">
                                    <div class="p-4 border-b">
                                        <h3 class="text-lg font-medium">Data Kepala Sekolah</h3>
                                    </div>
                                    <div class="p-4">
                                        <form id="kepalaSekolahForm">
                                            <div class="space-y-4">
                                                <div>
                                                    <label for="kepsek_nip" class="block text-sm font-medium text-gray-700">NIP</label>
                                                    <input  type="text" id="kepsek_nip" name="nip" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="kepsek_nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                                    <input type="text" id="kepsek_nama" name="nama" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="kepsek_email" class="block text-sm font-medium text-gray-700">Email</label>
                                                    <input type="email" id="kepsek_email" name="email" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                                <div>
                                                    <label for="kepsek_phone" class="block text-sm font-medium text-gray-700">No. Telepon</label>
                                                    <input type="text" id="kepsek_phone" name="phone" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                            </div>
                                            <div class="mt-6">
                                                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    Simpan Data
                                                </button>
                                            </div>
                                        </form>
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

<!-- Add Siswa Modal -->
<div id="addSiswaModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Tambah Siswa Baru</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('addSiswaModal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            {{-- <form  class="mt-4"> --}}
                <form id="addSiswaForm" class="mt-4" action="{{ route('admin.students.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                            <div>
                            <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
                            <input type="text" name="nis" id="nis" value="{{ old('nis') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('nis')" class="mt-1" />
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <label for="grade_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                            <select name="grade_id" id="grade_id" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Kelas</option>
                                @foreach ($grade as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('grade_id')" class="mt-1" />
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                            <select name="gender" id="gender" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="L">Laki - Laki</option>
                                    <option value="P">Perempuan</option>
                            </select>
                            <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                        </div>
                        <div>
                            <label for="p_birth" class="block text-sm font-medium text-gray-700">Tempat Lahir</label>
                            <input type="text" name="p_birth" id="p_birth" value="{{ old('p_birth') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('p_birth')" class="mt-1" />
                        </div>
                        <div>
                            <label for="d_birth" class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                            <input type="date" name="d_birth" id="d_birth" value="{{ old('d_birth') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('d_birth')" class="mt-1" />
                        </div>
                        <div>
                            <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                            <input type="file" name="photo" id="photo"  required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                        </div>
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea name="address" id="address" rows="3" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('address') }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-1" />
                        </div>
                        {{-- <input type="hidden" name="school_id" value="{{ $school->id }}"> --}}



                        {{-- <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Sekolah</label>
                            <select name="school_id" id="school_id" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih Sekolah</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('school_id')" class="mt-1" />
                        </div> --}}

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


<!-- Add Guru Modal -->
<div id="addGuruModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-medium text-gray-900">Tambah Guru Baru</h3>
                <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal('addGuruModal')">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="addGuruForm" class="mt-4">
                <div class="space-y-4">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                        <input type="text" id="nip" name="nip" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="nama_guru" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" id="nama_guru" name="nama" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="mata_pelajaran" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                        <select id="mata_pelajaran" name="mata_pelajaran" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                </div>
                <div class="flex justify-end mt-6 space-x-3">
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="closeModal('addGuruModal')">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
        document.addEventListener('DOMContentLoaded', function() {
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
