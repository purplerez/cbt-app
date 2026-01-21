<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Ujian Antar Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Error Message -->
            <x-input-error :messages="$errors->get('error')" class="mb-4" />

            <!-- Table -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">No</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Ujian</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($exams as $exam)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap">
                                            {{ $exam->id }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 whitespace-nowrap">
                                            {{ $exam->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold leading-5 rounded-full
                                               @if($exam->is_active == true)
                                                    bg-green-100 text-green-800
                                                @else
                                                    bg-red-100 text-red-800
                                                @endif
                                            ">
                                                @if($exam->is_active == true)
                                                    Aktif
                                                @else
                                                    Non-Aktif
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                            <div class="flex flex-wrap gap-2">
                                                @role('kepala')
                                                    <a href="{{ route('kepala.exams.participant', $exam->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-green-600 transition border border-green-600 rounded-md hover:bg-green-600 hover:text-white"
                                                       title="Daftarkan siswa">
                                                        Daftarkan Siswa
                                                    </a>
                                                @endrole

                                                @role('guru')
                                                    <a href="{{ route('guru.exams.participant', $exam->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-green-600 transition border border-green-600 rounded-md hover:bg-green-600 hover:text-white"
                                                       title="Daftarkan siswa">
                                                        Daftarkan Siswa
                                                    </a>
                                                @endrole

                                                @role('kepala')
                                                    <a href="{{ route('kepala.rooms', $exam->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-600 transition border border-blue-600 rounded-md hover:bg-blue-600 hover:text-white"
                                                       title="Ruang">
                                                        Ruang
                                                    </a>
                                                @endrole

                                                @role('guru')
                                                    <a href="{{ route('guru.rooms', $exam->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-blue-600 transition border border-blue-600 rounded-md hover:bg-blue-600 hover:text-white"
                                                       title="Ruang">
                                                        Ruang
                                                    </a>
                                                @endrole

                                                @role('kepala')
                                                    <a href="{{ route('kepala.exams.print-participants', $exam->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-purple-600 transition border border-purple-600 rounded-md hover:bg-purple-600 hover:text-white"
                                                       title="Cetak Kartu Peserta"
                                                       target="_blank">
                                                        Cetak Kartu Peserta
                                                    </a>
                                                @endrole

                                                @role('guru')
                                                    <a href="{{ route('guru.exams.print-participants', $exam->id) }}"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-purple-600 transition border border-purple-600 rounded-md hover:bg-purple-600 hover:text-white"
                                                       title="Cetak Kartu Peserta"
                                                       target="_blank">
                                                        Cetak Kartu Peserta
                                                    </a>
                                                @endrole

                                                <form action="{{ route('kepala.exams.manage', $exam->id) }}" method="post" style="display:inline;">
                                                    @csrf
                                                    <button type="submit"
                                                       class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white transition bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
                                                       title="Rekam Ujian">
                                                        Rekam Ujian
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-sm text-center text-gray-500">
                                            Tidak ada data ujian.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{-- $exams->links() --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
