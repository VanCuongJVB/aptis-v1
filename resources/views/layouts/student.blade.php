{{-- Main layout for student area --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Student</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Styles -->
    @vite(['resources/css/app.css'])
    
    <!-- Scripts -->
    @vite(['resources/js/app.js'])
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Top Navigation -->
    @if(request()->routeIs('reading.*'))
        @include('student.reading.components.navigation')
    @else
        @include('layouts.navigation')
    @endif

    <!-- Quick Access Menu -->
    <div class="fixed bottom-6 right-6 flex flex-col space-y-2">
        <!-- Quick Practice Button -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="bg-indigo-600 text-white rounded-full p-3 shadow-lg hover:bg-indigo-700 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </button>

            <!-- Quick Menu Popup -->
            <div x-show="open" 
                 @click.away="open = false"
                 x-transition
                 class="absolute bottom-full right-0 mb-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                    <a href="{{ route('reading.drill.sets', ['part' => 1]) }}" 
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Practice Reading Part 1
                    </a>
                    <a href="{{ route('reading.drill.sets', ['part' => 2]) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Practice Reading Part 2
                    </a>
                    <a href="{{ route('reading.drill.sets', ['part' => 3]) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Practice Reading Part 3
                    </a>
                    <a href="{{ route('reading.drill.sets', ['part' => 4]) }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Practice Reading Part 4
                    </a>
                    <div class="border-t border-gray-100"></div>
                    <a href="{{ route('reading.test.start') }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Take Full Reading Test
                    </a>
                </div>
            </div>
        </div>

        <!-- Progress Button -->
        <a href="{{ route('reading.progress') }}"
           class="bg-green-600 text-white rounded-full p-3 shadow-lg hover:bg-green-700 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </a>

        <!-- Help Button -->
        <button @click="$dispatch('open-help')"
                class="bg-gray-600 text-white rounded-full p-3 shadow-lg hover:bg-gray-700 focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </button>
    </div>

    <!-- Help Modal -->
    <div x-data="{ show: false }" 
         @open-help.window="show = true"
         x-show="show" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="show = false"></div>

            <div class="relative bg-white rounded-lg max-w-2xl w-full p-6">
                <div class="absolute right-4 top-4">
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <h2 class="text-2xl font-bold mb-4">How to Practice</h2>
                
                <div class="prose max-w-none">
                    <h3>Part Drill Mode</h3>
                    <ul>
                        <li>Choose a specific part to practice</li>
                        <li>Get immediate feedback after each question</li>
                        <li>Review explanations and tips</li>
                        <li>Track your progress for each part</li>
                    </ul>

                    <h3>Full Test Mode</h3>
                    <ul>
                        <li>Complete test experience</li>
                        <li>Timed sections</li>
                        <li>Get your score at the end</li>
                        <li>Review answers after completion</li>
                    </ul>

                    <h3>Keyboard Shortcuts</h3>
                    <ul>
                        <li><kbd>1</kbd>-<kbd>4</kbd> - Select options A-D</li>
                        <li><kbd>Space</kbd> or <kbd>Enter</kbd> - Submit answer</li>
                        <li><kbd>→</kbd> - Next question</li>
                        <li><kbd>F</kbd> - Flag question</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="pb-16">
        @yield('content')
    </main>

    <!-- Stack Scripts -->
    @stack('scripts')
</body>
</html>
