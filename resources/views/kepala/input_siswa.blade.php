
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
                    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                    <form id="addSiswaForm" class="mt-4" action="{{ route('kepala.student.store') }}" method="post" enctype="multipart/form-data">
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
                            <input type="file" name="photo" id="photo"
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
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>
