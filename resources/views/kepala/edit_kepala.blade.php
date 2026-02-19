<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Rubah Data Madrasah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">




                        <form action="{{ route('kepala.headmaster.update') }}" method="post" enctype="multipart/form-data">
                            @method('PUT')
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />

                        <div class="space-y-4">
                                                <div>
                                                    <label for="h_nip" class="block text-sm font-medium text-gray-700">NSM</label>
                                                    <input value="{{ $headmaster->nip }}" type="text" id="nip" name="nip"
                                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="h_nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                                    <input value="{{ $headmaster->name }}" type="text" id="nama" name="name"
                                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="h_gender" class="block text-sm font-medium text-gray-700">Gender</label>
                                                    <select name="gender" id="gender"
                                                        class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                                        <option value="L" @selected($headmaster->gender == 'L')>Laki - Laki</option>
                                                        <option value="P" @selected($headmaster->gender == 'P')>Perempuan</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label for="h_address" class="block text-sm font-medium text-gray-700">Alamat</label>
                                                    <textarea id="address" name="address" rows="3"
                                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ $headmaster->address }}</textarea>
                                                </div>

                                                <div>
                                                    <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                                                    <img src="{{ Storage::url($headmaster->photo) }}" alt="Current Photo" class="w-12 h-12 mb-2 rounded-full">
                                                    <input type="file" id="photo" name="photo"
                                                        class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                                </div>
                                            </div>

                        {{-- hidden input --}}
                        <input type="hidden" name="id" value="{{ $headmaster->id }}">
                        <input type="hidden" name="old_photo" value="{{ $headmaster->photo }}">


                        <!-- Tombol Submit -->
                        <div class="pt-4">
                            <button type="submit"
                                    class="w-full px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
