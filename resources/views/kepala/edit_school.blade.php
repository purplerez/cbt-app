<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tambah Data Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">



                        <form action="{{ route('kepala.school.update') }}" method="post" enctype="multipart/form-data">
                            @method('PUT')
                        @csrf
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Madrasah</label>
                            <input type="text" name="name" id="name" value="{{ $school->name}}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <!-- NPSN -->
                        <div>
                            <label for="npsn" class="block text-sm font-medium text-gray-700">NPSN</label>
                            <input type="text" name="npsn" id="npsn" required value="{{ $school->npsn}}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('npsn')" class="mb-4" />

                        </div>

                        <!-- Alamat -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea name="address" id="address" rows="3"
                                    class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ $school->address}}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mb-4" />
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                            <input type="text" name="phone" id="phone" value="{{ $school->phone}}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ $school->email}}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('email')" class="mb-4" />
                        </div>

                        <!-- Kode Sekolah -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Kode Sekolah</label>
                            <input type="text" name="code" id="code" required value="{{ $school->code}}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('code')" class="mb-4" />
                        </div>

                        <!-- Logo -->
                        <div>
                            <label for="logo" class="block text-sm font-medium text-gray-700">Logo Sekolah</label>
                            <img src="{{ Storage::url($school->logo) }}" alt="School Logo" class="object-cover w-24 h-24 rounded-full">
                            <input type="file" name="logo" id="logo"
                                class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <x-input-error :messages="$errors->get('logo')" class="mb-4" />
                        </div>

                        {{-- hidden input --}}
                        <input type="hidden" name="id" value="{{ $school->id }}">
                        <input type="hidden" name="old_photo" value="{{ $school->logo }}">


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
