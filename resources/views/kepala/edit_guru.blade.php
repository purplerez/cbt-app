
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Master Data Guru '.session('school_name')) }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <x-input-error :messages="$errors->get('error')" class="mb-4" />
                <form id="addGuruForm" class="mt-4" action="{{ route('kepala.teacher.update') }}" method="post" enctype="multipart/form-data">
                @method ('PUT')
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="nip" class="block text-sm font-medium text-gray-700">NIP</label>
                        <input value="{{ $teacher->nip }}" type="text" id="nip" name="nip" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input value="{{ $teacher->name }}" type="text" id="name" name="name" required class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                    </div>
                    <div>
                        <label for="gender" class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                        <select name="gender" id="gender" required
                            class="block w-full mt-1 border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="L" @if($teacher->gender == 'L') selected @endif >Laki - Laki</option>
                            <option value="P" @if($teacher->gender == 'P') selected @endif>Perempuan</option>
                        </select>
                        <x-input-error :messages="$errors->get('gender')" class="mt-1" />
                    </div>
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Alamat</label>
                        <textarea id="address" name="address" rows="3" required
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{$teacher->address}}</textarea>
                        <x-input-error :messages="$errors->get('address')" class="mt-1" />
                    </div>
                    <div>
                        <label for="photo" class="block text-sm font-medium text-gray-700">Photo</label>
                        <img src="{{ asset('storage/'.$teacher->photo) }}" alt="" class="w-20 h-20">
                        <input type="file" id="photo" name="photo"
                            class="block w-full mt-1 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                    </div>
                </div>
                <div class="flex justify-start mt-6 space-x-3">
                    <input type="hidden" name="id" value="{{ $teacher->id }}" />
                    <input type="hidden" name="old_photo" value="{{ $teacher->photo }}" />
                    <button type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="closeModal('addGuruModal')">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Ubah Data Guru
                    </button>
                </div>
            </form>
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>
