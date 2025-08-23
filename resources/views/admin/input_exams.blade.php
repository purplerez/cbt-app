<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Data Ujian Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form action="{{ route('admin.examsglobal.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Ujian</label>
                            <input autofocus type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <label for="deskripsi" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" rows="3" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ old('deskripsi') }}</textarea>
                            <x-input-error :messages="$errors->get('deskripsi')" class="mt-1" />
                        </div>
                        <div>
                            <label for="durasi" class="block text-sm font-medium text-gray-700">Durasi Ujian (dalam detik)</label>
                            <input  type="number" name="durasi" id="durasi" value="{{ old('durasi') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('durasi')" class="mt-1" />
                        </div>
                        <div>
                            <label for="total_soal" class="block text-sm font-medium text-gray-700">Total Soal</label>
                            <input type="number" name="total_soal" id="total_soal" value="{{ old('total_soal') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('total_soal')" class="mt-1" />
                        </div>
                        <div>
                            <label for="skor" class="block text-sm font-medium text-gray-700">Skor Minimal</label>
                            <input type="number" name="skor" id="skor" value="{{ old('skor') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('skor')" class="mt-1" />
                        </div>
                        <div>
                            <label for="start" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input type="date" name="start" id="start" value="{{ old('start') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('start')" class="mt-1" />
                        </div>
                        <div>
                            <label for="selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                            <input type="date" name="end" id="end" value="{{ old('end') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('end')" class="mt-1" />
                        </div>

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
