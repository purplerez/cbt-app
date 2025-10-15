<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <!-- <div-- class="flex items-center shrink-0">
                    @if(auth()->user()->hasRole('admin'))
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @elseif(auth()->user()->hasRole('guru'))
                        <x-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @elseif(auth()->user()->hasRole('kepala'))
                        <x-nav-link :href="route('kepala.dashboard')" :active="request()->routeIs('kepala.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @elseif(auth()->user()->hasRole('siswa'))
                        <x-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @elseif(auth()->user()->hasRole('super'))
                        <x-nav-link :href="route('super.dashboard')" :active="request()->routeIs('super.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endif
                    {{-- <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block w-auto text-gray-800 fill-current h-9" />
                    </a> --}}
                </div-->

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if(auth()->user()->hasRole('admin'))
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Admin Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.schools')" :active="request()->routeIs('admin.schools')">
                            {{ __('Madrasah') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.grades')" :active="request()->routeIs('admin.grades')">
                            {{ __('Tingkat') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.questions.types')" :active="request()->routeIs('admin.questions.types')">
                            {{ __('Jenis Soal') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.exams')" :active="request()->routeIs('admin.exams')">
                            {{ __('Jenis Ujian') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.schools')" :active="request()->routeIs('admin.subjects')">
                            {{ __('Log Aktifitas') }}
                        </x-nav-link>
                    @elseif (auth()->user()->hasRole('guru'))
                        <x-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')">
                            {{ __('Guru Dashboard') }} {{-- $prefix --}}
                        </x-nav-link>
                         <x-nav-link :href="route('guru.students')" :active="request()->routeIs('guru.students')">
                            {{ __('Data Siswa') }}
                        </x-nav-link>
                        <x-nav-link :href="route('guru.indexall')" :active="request()->routeIs('guru.indexall')">
                            {{ __('Ujian Bersama') }}
                        </x-nav-link>
                        <x-nav-link :href="route('guru.room-assignment.index')" :active="request()->routeIs('guru.room-assignment.*')">
                            {{ __('Penempatan Siswa') }}
                        </x-nav-link>
                        <x-nav-link :href="route('guru.berita-acara.index')" :active="request()->routeIs('guru.berita-acara.*')">
                            {{ __('Berita Acara') }}
                        </x-nav-link>
                    @elseif (auth()->user()->hasRole('kepala'))
                        <x-nav-link :href="route('kepala.dashboard')" :active="request()->routeIs('kepala.dashboard')">
                            {{ __('Kepala Dashboard') }} {{-- $prefix --}}
                        </x-nav-link>
                        <x-nav-link :href="route('kepala.school')" :active="request()->routeIs('kepala.school')">
                            {{ __('Data Madrasah') }}
                        </x-nav-link>
                        <x-nav-link :href="route('kepala.students')" :active="request()->routeIs('kepala.students')">
                            {{ __('Data Siswa') }}
                        </x-nav-link>
                        <x-nav-link :href="route('kepala.teachers')" :active="request()->routeIs('kepala.teachers')">
                            {{ __('Data Operator') }}
                        </x-nav-link>
                        <!-- x-nav-link :href="route('kepala.dashboard')" :active="request()->routeIs('admin.subjects')">
                            {{ __('Data Mata Pelajaran') }}
                        </-x-nav-link-->
                        <!-- x-nav-link :href="route('kepala.dashboard')" :active="request()->routeIs('admin.subjects')">
                            {{ __('Ujian Sekolah ') }}
                        </!-->
                        <x-nav-link :href="route('kepala.indexall')" :active="request()->routeIs('admin.subjects')">
                            {{ __('Ujian Bersama ') }}
                        </x-nav-link>
                        <x-nav-link :href="route('kepala.room-assignment.index')" :active="request()->routeIs('kepala.room-asignment.*')">
                            {{ __('Penempatan Siswa') }}
                        </x-nav-link>
                        <x-nav-link :href="route('kepala.berita-acara.index')" :active="request()->routeIs('kepala.berita-acara.*')">
                            {{ __('Berita Acara') }}
                        </x-nav-link>
                    @elseif (auth()->user()->hasRole('siswa'))
                        <x-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')">
                            {{ __('Siswa Dashboard') }}
                        </x-nav-link>
                    @elseif (auth()->user()->hasRole('super'))
                        <x-nav-link :href="route('super.dashboard')" :active="request()->routeIs('super.dashboard')">
                            {{ __('Super Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super.school')" :active="request()->routeIs('super.school')">
                            {{ __('Data Madrasah') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super.grades')" :active="request()->routeIs('super.grades')">
                            {{ __('Tingkat') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super.exams')" :active="request()->routeIs('admin.exams')">
                            {{ __('Jenis Ujian') }}
                        </x-nav-link>
                        <x-nav-link :href="route('super.exams')" :active="request()->routeIs('admin.exams')">
                            {{ __('Log Aktifitas') }}
                        </x-nav-link>
                    @endif
                    {{-- <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link> --}}
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md hover:text-gray-700 focus:outline-none">
                            <div>{{ Ucfirst(Auth::user()->name) }}</div>

                            <div class="ms-1">
                                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @if(auth()->user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @elseif (auth()->user()->hasRole('guru'))
                <x-responsive-nav-link :href="route('guru.dashboard')" :active="request()->routeIs('guru.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @elseif (auth()->user()->hasRole('kepala'))
                <x-responsive-nav-link :href="route('kepala.dashboard')" :active="request()->routeIs('kepala.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @elseif (auth()->user()->hasRole('siswa'))
                <x-responsive-nav-link :href="route('siswa.dashboard')" :active="request()->routeIs('siswa.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endif
            {{-- <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link> --}}
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
