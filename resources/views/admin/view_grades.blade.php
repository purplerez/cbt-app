<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Tingkat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @role('admin')
                        <a href="{{ route('admin.input.grades')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >+ Tambah</a>    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                    @endrole
                    @role('super')
                        <a href="{{ route('super.input.grades')}}" class="px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-green-500 transition" >+ Tambah</a>    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                    @endrole
                    <table class="min-w-full mt-4 text-sm text-left bg-white border border-gray-300 table-auto">
                        <thead class="text-gray-700 bg-gray-200">
                            <tr>
                                <th class="px-4 py-2 border w-5">No</th>
                                <th class="px-4 py-2 border w-40">Nama Grade</th>
                                <th class="px-4 py-2 border w-11">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grades as $index => $grade)
                                <tr>
                                    <td class="px-4 py-2 border">{{ $grade->id }}</td>
                                    <td class="px-4 py-2 border">{{ $grade->name }}</td>
                                    <td class="px-4 py-2 border">
                                        @role('admin')
                                            <a href="{{ route('admin.grades.edit', $grade->id) }}" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Edit</a>
                                        @endrole

                                        @role('super')
                                            <a href="{{ route('super.grades.edit', $grade->id) }}" class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition">Edit</a>
                                        @endrole

                                        @role('admin')
                                            <form action="{{ route('admin.grades.destroy', $grade->id) }}" method="POST" class="inline-block">
                                        @endrole

                                        @role('super')
                                            <form action="{{ route('super.grades.destroy', $grade->id) }}" method="POST" class="inline-block">
                                        @endrole
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
