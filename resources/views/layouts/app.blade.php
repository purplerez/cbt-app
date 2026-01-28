<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="api_token" content="{{ session('api_token', '') }}">
    @endauth

    <title>{{ config('app.name', 'Online Assessment ') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- TinyMCE Editor -->
    {{-- <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script> --}}

    <!-- Place the first <script> tag in your HTML's <head> -->
<script src="https://cdn.tiny.cloud/1/m74oj0scxb80ztto8ggadpnlq1qir1584o16gx5mev19xo4u/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>


</head>

<body class="font-sans antialiased">
    @php
        $prefix = request()->route()?->getPrefix();
        $prefix = $prefix ? ltrim($prefix, '/') : '';
    @endphp


    <div class="min-h-screen bg-gray-100">

        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white shadow">
                <div class="px-4 py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>

    </div>

    @stack('scripts')
    <script>
        window.apiToken = "{{ session('api_token', '') }}";
        if (!window.apiToken) {
            console.warn("API Token not found in session");
        }

        document.addEventListener('DOMContentLoaded', function () {
        if (document.querySelector('.tinymce-editor')) {
            tinymce.init({
            selector: '.tinymce-editor',
            height: 280,
            menubar: false,
            plugins: 'lists link table code',
            toolbar: 'bold italic | bullist numlist | link table | code',
            branding: false,
            statusbar: false
            });
        }
        });

</script>
</body>

</html>
