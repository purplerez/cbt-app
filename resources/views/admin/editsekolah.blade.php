<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Perbarui Data Sekolah'.$dataSchool->name) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">



                    <form action="{{ route('admin.schools.update', $dataSchool->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nama Sekolah</label>
                            <input type="text" name="name" id="name" value="{{ $dataSchool->name }}" required
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('name')" class="mt-1" />
                        </div>

                        <!-- NPSN -->
                        <div>
                            <label for="npsn" class="block text-sm font-medium text-gray-700">NPSN</label>
                            <input type="text" name="npsn" id="npsn" required value="{{ $dataSchool->npsn }}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('npsn')" class="mb-4" />

                        </div>

                        <!-- Alamat -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                            <textarea name="address" id="address" rows="3"
                                    class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">{{ $dataSchool->address }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mb-4" />
                        </div>

                        <!-- Telepon -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Telepon</label>
                            <input type="text" name="phone" id="phone" value="{{ $dataSchool->phone }}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ $dataSchool->email }}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('email')" class="mb-4" />
                        </div>

                        <!-- Kode Sekolah -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">Kode Sekolah</label>
                            <input type="text" name="code" id="code" required value="{{ $dataSchool->code }}"
                                class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <x-input-error :messages="$errors->get('code')" class="mb-4" />
                        </div>

                        <!-- Logo -->
                        <div>
                            {{-- <label for="logo" class="block text-sm font-medium text-gray-700">Logo Sekolah</label> --}}
                            <label for="" class="block text-sm font-medium text-gray-700">Current Logo</label>
                            <img src="{{ Storage::url($dataSchool->logo)}}" alt="Logo Sekolah" class="w-6 h-10 mb-4">
                            {{-- <img src --}}
                            <input type="file" name="logo" id="logo"
                                class="block w-full mt-1 text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <x-input-error :messages="$errors->get('logo')" class="mb-4" />
                        </div>


                        <!-- Tombol Submit -->
                        <div class="pt-4">
                            <input type="hidden" name="oldpic" value="{{ $dataSchool->logo }}">
                            {{-- <input type="hidden" name="id" value="{{ $dataSchool->id }}"> --}}
                            <button type="submit"
                                    class="w-full px-4 py-2 text-white transition bg-blue-600 rounded hover:bg-blue-700">
                                Perbarui Data Sekolah
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
