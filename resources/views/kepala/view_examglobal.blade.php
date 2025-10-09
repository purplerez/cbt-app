<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Ujian Antar Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                    <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Nama Ujian</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($exams as $exam)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $exam->id }}</td>
                                    <td class="px-4 py-2 border">{{ $exam->title }}</td>
                                    <td class="px-4 py-2 border">
                                        @if($exam->is_active == true)
                                            <span class="font-semibold text-green-600">Aktif</span>
                                        @else
                                            <span class="font-semibold text-red-600">Non-Aktif</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-2 border">

                                            <a href="{{ route('kepala.exams.participant',  $exam->id ) }}" class="px-3 py-1.5 mr-1 bg-white border border-green-600 text-green-600 text-sm font-medium rounded hover:bg-green-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                                                Daftarkan siswa
                                            </a>

                                        <form action={{ route('kepala.exams.manage',  $exam->id ) }} method="post" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 mr-1 bg-white border border-blue-600 text-blue-600 text-sm font-medium rounded hover:bg-blue-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                Berita Acara
                                            </button>
                                        </form>
                                        <form action={{ route('kepala.exams.manage',  $exam->id ) }} method="post" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                Rekam Ujian
                                            </button>
                                        </form>


                                    </td>

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
