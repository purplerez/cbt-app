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
                        <a href="{{route('kepala.teacher.create')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >
                            + Tambah
                        </a>
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                    <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">NIP</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama</th>
                                                        <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Data guru akan ditampilkan disini -->
                                                    @forelse ($teachers as $teacher)
                                                        <tr class="border-b-slate-50">
                                                            <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->nip }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap">{{ $teacher->name }}</td>
                                                            <td class="px-6 py-4 whitespace-nowrap">
                                                                    <button class="px-4 py-2 text-sm font-medium text-white transition bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500" data-modal-target="editGuruModal{{ $teacher->id }}">
                                                                        Ubah
                                                                    </button>
                                                                    <form action="{{route('admin.teacher.destroy', $teacher->id)}}" method="POST" class="inline-block">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white transition bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" onclick="return confirm('Apakah Anda yakin ingin menghapus data Guru ini?')">Hapus</button>
                                                                    </form>
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
</x-app-layout>
