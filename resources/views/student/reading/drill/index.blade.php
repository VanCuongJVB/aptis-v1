@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold">Reading Practice</h1>
            <p class="mt-2 text-gray-600">Choose a part to practice or take a full test.</p>
        </div>

        <!-- Parts Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Part 1 -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold mb-2">Part 1</h2>
                            <p class="text-gray-600 text-sm mb-4">Sentence Completion</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stats['part1']['accuracy'] >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ round($stats['part1']['accuracy']) }}% Accuracy
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Completed Sets</span>
                            <span class="font-medium">{{ $stats['part1']['completed'] }}/{{ $stats['part1']['total'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($stats['part1']['completed'] / $stats['part1']['total']) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="mt-6 space-x-3">
                        <a href="{{ route('reading.drill.sets', ['part' => 1]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Practice Sets
                        </a>
                        @if($stats['part1']['hasWrongAnswers'])
                        <a href="{{ route('reading.drill.wrong-answers', ['part' => 1]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Review Mistakes
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Part 2 -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold mb-2">Part 2</h2>
                            <p class="text-gray-600 text-sm mb-4">Text Completion</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stats['part2']['accuracy'] >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ round($stats['part2']['accuracy']) }}% Accuracy
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Completed Sets</span>
                            <span class="font-medium">{{ $stats['part2']['completed'] }}/{{ $stats['part2']['total'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($stats['part2']['completed'] / $stats['part2']['total']) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="mt-6 space-x-3">
                        <a href="{{ route('reading.drill.sets', ['part' => 2]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Practice Sets
                        </a>
                        @if($stats['part2']['hasWrongAnswers'])
                        <a href="{{ route('reading.drill.wrong-answers', ['part' => 2]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Review Mistakes
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Part 3 -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold mb-2">Part 3</h2>
                            <p class="text-gray-600 text-sm mb-4">Reading for Meaning</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stats['part3']['accuracy'] >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ round($stats['part3']['accuracy']) }}% Accuracy
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Completed Sets</span>
                            <span class="font-medium">{{ $stats['part3']['completed'] }}/{{ $stats['part3']['total'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($stats['part3']['completed'] / $stats['part3']['total']) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="mt-6 space-x-3">
                        <a href="{{ route('reading.drill.sets', ['part' => 3]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Practice Sets
                        </a>
                        @if($stats['part3']['hasWrongAnswers'])
                        <a href="{{ route('reading.drill.wrong-answers', ['part' => 3]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Review Mistakes
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Part 4 -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold mb-2">Part 4</h2>
                            <p class="text-gray-600 text-sm mb-4">Reading for Purpose</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stats['part4']['accuracy'] >= 80 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ round($stats['part4']['accuracy']) }}% Accuracy
                        </span>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Completed Sets</span>
                            <span class="font-medium">{{ $stats['part4']['completed'] }}/{{ $stats['part4']['total'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($stats['part4']['completed'] / $stats['part4']['total']) * 100 }}%"></div>
                        </div>
                    </div>

                    <div class="mt-6 space-x-3">
                        <a href="{{ route('reading.drill.sets', ['part' => 4]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                            Practice Sets
                        </a>
                        @if($stats['part4']['hasWrongAnswers'])
                        <a href="{{ route('reading.drill.wrong-answers', ['part' => 4]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Review Mistakes
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Buttons -->
        <div class="mt-12 flex justify-center space-x-4">
            <a href="{{ route('reading.test.start') }}" 
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700">
                Take Full Test
                <svg class="ml-2 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
            <a href="{{ route('reading.progress') }}" 
               class="inline-flex items-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                View Progress
                <svg class="ml-2 -mr-1 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </a>
        </div>
    </div>
</div>
@endsection
