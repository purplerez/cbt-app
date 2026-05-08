<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Profile Header -->
                    <div class="mb-8 pb-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                                <p class="text-gray-500 mt-2">{{ $user->email }}</p>
                                @if($user->roles->count() > 0)
                                    <div class="mt-3 flex gap-2">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div>
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Edit Profile') }}
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Left Column -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ __('Personal Information') }}</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                                    <p class="mt-1 text-gray-900">{{ $user->name }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Email Address') }}</label>
                                    <p class="mt-1 text-gray-900">{{ $user->email }}</p>
                                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                        <p class="text-sm text-yellow-600 mt-2">
                                            {{ __('Email not verified') }}
                                        </p>
                                    @else
                                        <p class="text-sm text-green-600 mt-2">
                                            <span class="inline-flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                </svg>
                                                {{ __('Email verified') }}
                                            </span>
                                        </p>
                                    @endif
                                </div>

                                @if($user->phone)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                                        <p class="mt-1 text-gray-900">{{ $user->phone }}</p>
                                    </div>
                                @endif

                                @if($user->address)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Address') }}</label>
                                        <p class="mt-1 text-gray-900">{{ $user->address }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-6">{{ __('Account Information') }}</h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('User ID') }}</label>
                                    <p class="mt-1 text-gray-900">{{ $user->id }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Account Created') }}</label>
                                    <p class="mt-1 text-gray-900">{{ $user->created_at->format('d M Y H:i') }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Last Updated') }}</label>
                                    <p class="mt-1 text-gray-900">{{ $user->updated_at->format('d M Y H:i') }}</p>
                                </div>

                                @if($user->last_login_at)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">{{ __('Last Login') }}</label>
                                        <p class="mt-1 text-gray-900">{{ $user->last_login_at->format('d M Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ __('Quick Actions') }}</h3>
                        <div class="flex gap-3">
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                {{ __('Edit Profile') }}
                            </a>
                            <a href="{{ route('profile.edit') }}#password" class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                                {{ __('Change Password') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
