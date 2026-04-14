<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Ubah Data Ujian Bersama') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                @role('admin')
                    <form action="{{ route('admin.examsglobal.update', $exam->id) }}" method="post" enctype="multipart/form-data">
                @endrole

                        @csrf
                        @method('PUT')
                        
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                        
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Nama Ujian</label>
                                <input autofocus type="text" name="name" id="name" value="{{ old('name', $exam->title) }}" required
                                    class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <x-input-error :messages="$errors->get('name')" class="mt-1" />
                            </div>

                            <div>
                                <label for="start" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                                <input type="date" name="start" id="start" value="{{ old('start', \Carbon\Carbon::parse($exam->start_time)->format('Y-m-d')) }}" required
                                    class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <x-input-error :messages="$errors->get('start')" class="mt-1" />
                            </div>

                            <div>
                                <label for="end" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                                <input type="date" name="end" id="end" value="{{ old('end', \Carbon\Carbon::parse($exam->end_time)->format('Y-m-d')) }}" required
                                    class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <x-input-error :messages="$errors->get('end')" class="mt-1" />
                            </div>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex items-center gap-4 pt-6">
                            <button type="submit"
                                    class="px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Perbarui Data
                            </button>
                            <a href="{{ route('admin.exams') }}" 
                               class="text-sm text-gray-600 hover:text-gray-900">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
