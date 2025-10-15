<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Data Ruang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div id="successMessage" class="p-4 mb-4 text-sm text-green-700 transition-opacity duration-500 bg-green-100 rounded-lg">{!! session('success') !!}</div>
                    @endif
                    @role('kepala')
                    <form method="POST" action="{{ route('kepala.room.store') }}">
                    @endrole
                    @role('guru')
                    <form method="POST" action="{{ route('guru.room.store') }}">
                    @endrole
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Ruang {{-- session('exam_type_id') --}}</label>
                            <input autofocus type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Kapasitas Ruang {{-- session('exam_type_id') --}}</label>
                            <input autofocus type="number" name="capacity" id="capacity" value="{{ old('capacity') }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('capacity')" class="mt-1" />
                        </div>


                        <!-- Tombol Submit -->
                        <div class="flex items-center justify-start pt-4 space-x-3">
                            <a href="{{ route('kepala.rooms',  session('exam_type_id') ) }}"
                               class="px-4 py-2 text-sm font-medium text-red-600 transition bg-white border border-red-600 rounded hover:bg-red-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                Kembali
                            </a>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white transition bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
