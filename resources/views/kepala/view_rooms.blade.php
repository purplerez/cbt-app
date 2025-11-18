<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Ruang') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">


                    @role('kepala')
                    <a href="{{ route('kepala.room.create')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >
                        + Tambah
                    </a>
                    @endrole
                    @role('guru')
                    <a href="{{ route('guru.room.create')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >
                        + Tambah
                    </a>
                    @endrole
                    <x-input-error :messages="$errors->get('error')" class="mb-4" />

                    <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                <th class="w-5 px-4 py-2 border">No</th>
                                <th class="w-40 px-4 py-2 border">Nama Ruang</th>
                                <th class="px-4 py-2 border w-11">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rooms as $index => $room)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $room->id }}</td>
                                    <td class="px-4 py-2 border">{{ $room->name }}</td>
                                    <td class="px-4 py-2 border">



                                        <a href="{{route('kepala.room.participants', $room->id)}}" class="px-3 py-1.5 mr-2 bg-white border border-blue-600 text-blue-600 text-sm font-medium rounded hover:bg-blue-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                            Peserta
                                        </a>



                                        <form action="{{route('kepala.room.destroy', $room->id)}}" method="POST" class="inline-block"  onsubmit="return confirm('Yakin ingin menghapus ruang ?');" >

                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-white-600 text-red-700 border border-red-700 text-sm font-medium rounded hover:bg-red-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-red-500 transition">
                                                Delete
                                            </button>
                                        </form>
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
