{{-- <div class="bg-white border-b">
    <div class="container mx-auto">
        <nav class="flex items-center justify-between p-4">
            <div class="flex items-center space-x-4">
                <a href="{{ url()->previous() }}" 
                   class="text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <a href="{{ route('student.dashboard') }}" 
                   class="text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </div>

            <div class="flex items-center space-x-2">
                <span class="text-sm font-medium text-gray-500">Reading</span>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-sm font-medium">{{ $partLabel ?? 'Practice' }}</span>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('reading.drill.sets', ['part' => $currentPart ?? 1]) }}" 
                   class="text-sm font-medium {{ request()->routeIs('reading.drill.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Part Drill
                </a>
                <a href="{{ route('reading.test.list') }}" 
                   class="text-sm font-medium {{ request()->routeIs('reading.test.*') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                    Full Test
                </a>
                <a href="{{ route('reading.progress') }}" 
                   class="text-sm font-medium {{ request()->routeIs('reading.progress') ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900' }}">
                    My Progress
                </a>
            </div>
        </nav>

        @if(request()->routeIs('reading.drill.*'))
        <div class="flex items-center justify-center space-x-2 p-2 bg-gray-50 border-t">
            @foreach(range(1, 4) as $part)
            <a href="{{ route('reading.drill.sets', ['part' => $part]) }}" 
               class="px-4 py-2 rounded-md text-sm font-medium {{ ($currentPart ?? 0) == $part ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-200' }}">
                Part {{ $part }}
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div> --}}
