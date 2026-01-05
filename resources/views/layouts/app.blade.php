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
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
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

        // Initialize TinyMCE for all editors
        tinymce.init({
            selector: '.tinymce-editor',
            license_key: 'gpl',
            theme: 'silver',
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
                'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
                'fullscreen', 'insertdatetime', 'media', 'table', 'paste', 'help',
                'wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist outdent indent | link image media | code fullscreen help',
            menubar: 'file edit view insert format tools table help',
            branding: false,
            height: 300,
            body_class: 'mce-content-body',
            content_style: 'body { font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif; font-size:14px; line-height:1.6; }',
            paste_as_text: false,
            valid_elements: '+*[*]',
            valid_children: '+*[*]'
        });
    </script>
</body>

</html>
