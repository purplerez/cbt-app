<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <a href="{{ route('admin.inputsekolah')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >+ Tambah</a>    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                    <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 border">No</th>
                                <th class="px-4 py-2 border">Nama Sekolah</th>
                                <th class="px-4 py-2 border">Alamat</th>
                                <th class="px-4 py-2 border">Status</th>
                                <th class="px-4 py-2 border">Kode Sekolah</th>
                                <th class="px-4 py-2 border">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schools as $index => $school)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $school->id }}</td>
                                    <td class="px-4 py-2 border">{{ $school->name }}</td>
                                    <td class="px-4 py-2 border">{{ $school->address }}</td>
                                    <td class="px-4 py-2 border">{{ $school->code }}</td>
                                    <td class="px-4 py-2 border">
                                        <form action="{{ route('admin.schools.manage', $school->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                                Manage
                                            </button>
                                        </form>
                                        {{-- <a href={{ route('admin.schools.manage', $school->id) }} class="btn px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Manage</a> --}}
                                        <a href="{{ route('admin.schools.edit', $school->id) }}" class="btn btn-primary">Edit</a>
                                        <form action="{{ route('admin.schools.destroy', $school->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition">Delete</button>
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
