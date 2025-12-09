<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @php
            $pngFavicon = public_path('images/Logo_Milaedu.png');
        @endphp
        @if(file_exists($pngFavicon))
            <link rel="icon" type="image/png" href="{{ asset('images/Logo_Milaedu.png') }}">
            <link rel="apple-touch-icon" href="{{ asset('images/Logo_Milaedu.png') }}">
        @endif

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
            :root { --font-inter: 'Roboto', Arial, sans-serif; }
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
        
        {{-- Ensure audio can be interacted on Safari iOS --}}
        <script>
            function bindInlineAudioHandlers(root) {
                (root || document).querySelectorAll('audio').forEach(function(audio) {
                    // Ensure pointer events work and allow selection where needed
                    audio.style.pointerEvents = 'auto';
                    audio.style.WebkitUserSelect = 'text';

                    // iOS requires playsinline to avoid fullscreen playback
                    audio.setAttribute('playsinline', '');
                    audio.setAttribute('webkit-playsinline', '');

                    // Make sure native controls are enabled
                    audio.controls = true;

                    // Bind a minimal user-gesture handler so taps directly call .play()
                    if (audio.dataset.userPlayBound) return;
                    function tryPlay() {
                        try {
                            // Only call play if the audio is currently paused. This avoids
                            // forcing playback when the user attempts to pause.
                            if (audio.paused) {
                                var p = audio.play();
                                if (p && typeof p.catch === 'function') p.catch(function(){});
                            }
                        } catch (e) {
                            // ignore
                        }
                    }
                    // Use non-passive handlers on both the audio element and its immediate container
                    // so taps on native controls (or nearby) are more likely to be treated as a user gesture.
                    try {
                        audio.addEventListener('click', tryPlay, false);
                        audio.addEventListener('touchend', tryPlay, false);
                        const parent = audio.parentElement;
                        if (parent && parent !== document.body) {
                            parent.addEventListener('click', function (ev) {
                                // If the click originated inside the native controls area, toggle play
                                if (ev.target && (ev.target === audio || audio.contains(ev.target) || parent.contains(ev.target))) tryPlay();
                            }, false);
                            parent.addEventListener('touchend', function (ev) {
                                try {
                                    if (ev.changedTouches && ev.changedTouches.length) {
                                        const t = ev.changedTouches[0];
                                        const rect = audio.getBoundingClientRect();
                                        if (t.clientX >= rect.left && t.clientX <= rect.right && t.clientY >= rect.top && t.clientY <= rect.bottom) {
                                            tryPlay();
                                        }
                                    } else {
                                        tryPlay();
                                    }
                                } catch (e) {}
                            }, false);
                        }
                    } catch (e) { /* ignore */ }
                    audio.dataset.userPlayBound = '1';
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                bindInlineAudioHandlers(document);
            });

            // Re-bind after SPA container replacements (app uses aptis container replacement events)
            window.addEventListener('aptis:container:replace', function (e) {
                // If event provides a container, bind within it; otherwise bind globally after a short delay
                try {
                    if (e && e.detail && e.detail.container) bindInlineAudioHandlers(e.detail.container);
                    else setTimeout(function(){ bindInlineAudioHandlers(document); }, 50);
                } catch (err) { setTimeout(function(){ bindInlineAudioHandlers(document); }, 50); }
            });
        </script>
        @stack('scripts')
    </body>
</html>
