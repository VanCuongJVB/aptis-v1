@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Summary Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-2">Practice Summary</h1>
            <p class="text-gray-600">You've completed this practice set!</p>
        </div>

        <!-- Overall Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Accuracy Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-1">Accuracy</h3>
                <div class="flex items-baseline">
                    <span class="text-3xl font-bold">{{ $accuracy }}%</span>
                </div>
                @if($accuracy >= 80)
                    <p class="mt-2 text-sm text-green-600">Great job! Keep it up!</p>
                @elseif($accuracy >= 60)
                    <p class="mt-2 text-sm text-yellow-600">Good progress. Room for improvement.</p>
                @else
                    <p class="mt-2 text-sm text-red-600">More practice needed.</p>
                @endif
            </div>

            <!-- Time Spent Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-1">Time Spent</h3>
                <div class="flex items-baseline">
                    <span class="text-3xl font-bold">{{ $timeSpent }}</span>
                    <span class="ml-1 text-gray-500">minutes</span>
                </div>
                <p class="mt-2 text-sm text-gray-600">Average time per question: {{ round($timeSpent / $items->count(), 1) }} minutes</p>
            </div>

            <!-- Questions Stats Card -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-1">Questions</h3>
                <div class="flex items-baseline">
                    <span class="text-3xl font-bold">{{ $items->where('is_correct', true)->count() }}/{{ $items->count() }}</span>
                </div>
                <p class="mt-2 text-sm text-gray-600">{{ $items->where('is_flagged', true)->count() }} questions flagged</p>
            </div>
        </div>

        <!-- Questions Review -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium">Questions Review</h2>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($items as $item)
                <div class="p-6" x-data="{ showExplanation: false }">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <!-- Question number and status -->
                            <div class="flex items-center mb-4">
                                <span class="text-sm font-medium text-gray-500 mr-2">Question {{ $loop->iteration }}</span>
                                @if($item->is_correct)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Correct
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Incorrect
                                    </span>
                                @endif
                                @if($item->is_flagged)
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Flagged
                                    </span>
                                @endif
                            </div>

                            <!-- Question content -->
                            <div class="prose max-w-none mb-4">
                                {!! $item->question->stem !!}
                                @if($item->question->passage)
                                    <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                                        {!! $item->question->passage !!}
                                    </div>
                                @endif
                            </div>

                            <!-- Answer comparison -->
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-2">Your answer:</p>
                                <div class="flex items-center">
                                    <span class="inline-flex items-center justify-center h-6 w-6 rounded-full border {{ $item->is_correct ? 'border-green-500 text-green-500' : 'border-red-500 text-red-500' }} text-sm font-medium mr-2">
                                        {{ chr(64 + $item->answer_index) }}
                                    </span>
                                    <span class="prose max-w-none">
                                        {!! $item->answer_text !!}
                                    </span>
                                </div>
                                
                                @if(!$item->is_correct)
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-2">Correct answer:</p>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full border border-green-500 text-green-500 text-sm font-medium mr-2">
                                            {{ chr(64 + $item->correct_answer_index) }}
                                        </span>
                                        <span class="prose max-w-none">
                                            {!! $item->correct_answer_text !!}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Explanation toggle -->
                            @if($item->question->explanation)
                            <button @click="showExplanation = !showExplanation"
                                    class="text-sm text-indigo-600 hover:text-indigo-500">
                                <span x-text="showExplanation ? 'Hide Explanation' : 'Show Explanation'"></span>
                            </button>
                            
                            <div x-show="showExplanation" 
                                 x-transition
                                 class="mt-4 p-4 bg-indigo-50 rounded-lg prose max-w-none">
                                {!! $item->question->explanation !!}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex justify-center space-x-4">
            <a href="{{ route('reading.drill.sets', ['part' => $quiz->questions->first()->part]) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Back to Sets
            </a>
            @if($accuracy < 80)
            <a href="{{ route('reading.drill.start', $quiz->id) }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Practice Again
            </a>
            @endif
            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                Print Summary
            </button>
        </div>
    </div>
</div>
@endsection
