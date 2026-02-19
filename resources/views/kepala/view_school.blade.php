<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Data Madrasah
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                        {{-- @if (session('error'))
                            <div class="mb-4 text-red-600">
                                {{ session('error') }}
                            </div>
                        @endif --}}
                        <x-input-error :messages="$errors->get('error')" class="mb-4" />

                        <!-- Logo -->
                        <div class="flex items-center justify-center mb-4">

                                <img src="{{ Storage::url($school->logo) }}" alt="School Logo" class="object-cover w-24 h-24 rounded-full">

                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            <div class="flex items-start py-3 space-x-4 transition-colors border-b border-gray-100 rounded-md hover:bg-gray-50">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">Nama Madrasah</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block text-gray-800">{{ $school->name }}</span>
                                </div>
                            </div>

                            <div class="flex items-start py-3 space-x-4 transition-colors border-b border-gray-100 rounded-md hover:bg-gray-50">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">NPSN</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block text-gray-800">{{ $school->npsn }}</span>
                                </div>
                            </div>

                            <div class="flex items-start py-3 space-x-4 transition-colors border-b border-gray-100 rounded-md hover:bg-gray-50">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">Alamat Madrasah</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block text-gray-800">{{ $school->address }}</span>
                                </div>
                            </div>

                            <div class="flex items-start py-3 space-x-4 transition-colors border-b border-gray-100 rounded-md hover:bg-gray-50">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">Telp. Madrasah</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block text-gray-800">{{ $school->phone }}</span>
                                </div>
                            </div>

                            <div class="flex items-start py-3 space-x-4 border-b border-gray-100">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">Email</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block text-gray-800">{{ $school->email }}</span>
                                </div>
                            </div>

                            <div class="flex items-start py-3 space-x-4 transition-colors rounded-md hover:bg-gray-50">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">Kode Madrasah</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block">{{ $school->code }}</span>
                                </div>
                            </div>

                            <div class="flex items-start py-3 space-x-4 transition-colors rounded-md hover:bg-gray-50">
                                <span class="w-32 font-semibold text-gray-700 sm:w-40">Kepala Madrasah</span>
                                <div class="flex-1 pl-2">
                                    <span class="inline-block">{{ $school->headmasters->name ?? '-' }}</span>

                                    <a href="{{ route('kepala.headmaster.edit', $school->id) }}" class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                        Rubah Data
                                    </a>
                                </div>

                            </div>

                        </div>

                    <div class="flex justify-end pt-4 mt-6 border-t border-gray-100">
                        <a href="{{ route('kepala.school.edit', $school->id) }}" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            Rubah Data Madrasah
                        </a>
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>
