<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts: prefer Manrope then Public Sans for Vietnamese support -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=public-sans:400,700&display=swap" rel="stylesheet" />

        <!-- Scripts/CSS: prefer pre-built assets in public/build (no npm run dev required) -->
        @php
            $manifestPath = public_path('build/manifest.json');
            $cssFile = null; $jsFile = null;
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (!empty($manifest['resources/css/app.css']['file'])) {
                    $cssFile = '/build/' . $manifest['resources/css/app.css']['file'];
                }
                if (!empty($manifest['resources/js/app.js']['file'])) {
                    $jsFile = '/build/' . $manifest['resources/js/app.js']['file'];
                }
            }
        @endphp

        @if($cssFile)
            <link rel="stylesheet" href="{{ asset($cssFile) }}">
        @else
            @vite(['resources/css/app.css'])
        @endif

        @if($jsFile)
            <script defer src="{{ asset($jsFile) }}"></script>
        @else
            @vite(['resources/js/app.js'])
        @endif

        <style>
            /* font variable requested by user */
            :root{--font-inter: 'Manrope','Public Sans', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Color Emoji', 'Apple Color Emoji', 'Segoe UI Emoji';}
            /* Minimal fallback button styles (kept for safety) */
            .btn-base{display:flex;align-items:center;justify-content: center; padding:0.5rem 0.75rem;border-radius:0.375rem;font-size:0.875rem;font-weight:500;border:1px solid #d1d5db}
            .btn-primary{background-color:#23085a;color:#fff;border-color:transparent}
            .btn-primary:hover{background-color:#19043f}
            /* Use the requested font stack via the CSS variable */
            body { font-family: var(--font-inter); }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @hasSection('content')
                    @yield('content')
                @else
                    {{ $slot ?? '' }}
                @endif
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
