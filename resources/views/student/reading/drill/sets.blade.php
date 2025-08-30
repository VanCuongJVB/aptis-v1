@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Part Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold mb-2">{{ $partLabel }}</h1>
            <p class="text-gray-600">Choose a set to practice or review your progress.</p>
        </div>

        <!-- Practice Sets Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($quizzes as $quiz)
            <div class="border rounded-lg shadow-sm hover:shadow-md transition-shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-2">{{ $quiz->title }}</h3>
                    
                    <!-- Status Badge -->
                    <div class="mb-4">
                        @if($quiz->status === 'not_started')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Not Started
                            </span>
                        @elseif($quiz->status === 'in_progress')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                In Progress
                            </span>
                        @elseif($quiz->status === 'completed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Completed
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Needs Review
                            </span>
                        @endif
                    </div>

                    <!-- Statistics -->
                    @if($quiz->accuracy !== null)
                    <div class="mb-4 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Accuracy</p>
                            <p class="font-semibold">{{ $quiz->accuracy }}%</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Last Attempt</p>
                            <p class="font-semibold">{{ $quiz->last_attempt }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="space-x-2">
                        <a href="{{ route('reading.drill.start', $quiz->id) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            @if($quiz->status === 'not_started')
                                Start Practice
                            @else
                                Continue
                            @endif
                        </a>
                        @if($quiz->status === 'completed' || $quiz->status === 'needs_review')
                        <a href="{{ route('reading.drill.wrong-answers', ['part' => $part, 'quiz' => $quiz->id]) }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Review Mistakes
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Progress Summary -->
        @if($quizzes->isNotEmpty())
        <div class="mt-12 bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Your Progress</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-sm text-gray-500">Completed Sets</p>
                    <p class="text-2xl font-bold">
                        {{ $quizzes->where('status', 'completed')->count() }}/{{ $quizzes->count() }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Average Accuracy</p>
                    <p class="text-2xl font-bold">
                        {{ round($quizzes->avg('accuracy')) }}%
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Sets Needing Review</p>
                    <p class="text-2xl font-bold">
                        {{ $quizzes->where('status', 'needs_review')->count() }}
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('drillPart', () => ({
            showFilters: false,
            
            toggleFilters() {
                this.showFilters = !this.showFilters;
            },
            
            startRandomSet() {
                const uncompletedSets = @json($quizzes->where('status', 'not_started')->pluck('id'));
                if (uncompletedSets.length > 0) {
                    const randomIndex = Math.floor(Math.random() * uncompletedSets.length);
                    window.location.href = `/reading/drill/set/${uncompletedSets[randomIndex]}`;
                }
            }
        }));
    });
</script>
@endpush
