@extends('layouts.student')

@section('content')
<div x-data="drillQuestion" 
     class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex justify-between text-sm font-medium text-gray-600 mb-2">
                <span>Question {{ $currentNumber }}/{{ $totalQuestions }}</span>
                <span>Progress: {{ round(($currentNumber/$totalQuestions) * 100) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" 
                     style="width: {{ ($currentNumber/$totalQuestions) * 100 }}%"></div>
            </div>
        </div>

            <!-- Question Content -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            @switch($question->part)
                @case(1)
                    @include('student.reading.drill._part1', ['question' => $question])
                    @break
                @case(2)
                    @include('student.reading.drill._part2', ['question' => $question])
                    @break
                @case(3)
                    @include('student.reading.drill._part3', ['question' => $question])
                    @break
                @case(4)
                    @include('student.reading.drill._part4', ['question' => $question])
                    @break
                @default
                    <div class="prose max-w-none">
                        <p class="text-red-600">Unsupported question type</p>
                    </div>
            @endswitch
        </div>        <!-- Action Bar -->
        <div class="flex justify-between items-center">
            <!-- Timer (if needed) -->
            <div x-show="timer > 0" class="text-gray-600">
                Time: <span x-text="formatTime(timer)"></span>
            </div>

            <div class="space-x-4">
                <!-- Flag Button -->
                <button @click="toggleFlag"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <template x-if="!isFlagged">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"></path>
                        </svg>
                    </template>
                    <template x-if="isFlagged">
                        <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3 6a3 3 0 013-3h10a1 1 0 01.8 1.6L14.25 8l2.55 3.4A1 1 0 0116 13H6a1 1 0 00-1 1v3a1 1 0 11-2 0V6z" clip-rule="evenodd"></path>
                        </svg>
                    </template>
                </button>

                <!-- Submit/Next Button -->
                <button x-show="!showAnswer && selectedOption"
                        @click="submitAnswer"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Submit Answer
                </button>
                <button x-show="showAnswer"
                        @click="nextQuestion"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    Next Question
                </button>
            </div>
        </div>

        <!-- Feedback Panel -->
        <div x-show="showAnswer" 
             x-transition
             class="mt-8 p-6 rounded-lg"
             :class="isCorrect ? 'bg-green-50' : 'bg-red-50'">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <template x-if="isCorrect">
                        <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </template>
                    <template x-if="!isCorrect">
                        <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </template>
                </div>
                <div class="ml-3">
                    <h3 class="text-lg font-medium" x-text="isCorrect ? 'Correct!' : 'Incorrect'"></h3>
                    <div class="mt-2 text-sm" x-html="explanation"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('drillQuestion', () => ({
        selectedOption: null,
        showAnswer: false,
        isCorrect: false,
        correctAnswer: null,
        explanation: '',
        isFlagged: false,
        timer: 0,

        init() {
            // Initialize timer if needed
            if (this.timer > 0) {
                this.startTimer();
            }
        },

        selectOption(optionId) {
            if (!this.showAnswer) {
                this.selectedOption = optionId;
            }
        },

        async submitAnswer() {
            try {
                const response = await fetch('{{ route("reading.drill.answer") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        attempt_id: '{{ $attempt->id }}',
                        question_id: '{{ $question->id }}',
                        answer: this.selectedOption
                    })
                });

                const data = await response.json();
                
                this.showAnswer = true;
                this.isCorrect = data.is_correct;
                this.correctAnswer = data.correct_answer;
                this.explanation = data.explanation;

                // Auto-advance after 1.5 seconds if correct
                if (this.isCorrect) {
                    setTimeout(() => this.nextQuestion(), 1500);
                }
            } catch (error) {
                console.error('Error submitting answer:', error);
            }
        },

        async nextQuestion() {
            window.location.href = '{{ route("reading.drill.next", ["quiz" => $quiz->id, "currentQuestion" => $question->id]) }}';
        },

        async toggleFlag() {
            try {
                const response = await fetch('{{ route("reading.drill.flag") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question_id: '{{ $question->id }}',
                        attempt_id: '{{ $attempt->id }}',
                        is_flagged: !this.isFlagged
                    })
                });

                if (response.ok) {
                    this.isFlagged = !this.isFlagged;
                }
            } catch (error) {
                console.error('Error toggling flag:', error);
            }
        },

        startTimer() {
            const interval = setInterval(() => {
                this.timer--;
                if (this.timer <= 0) {
                    clearInterval(interval);
                    // Handle time up
                }
            }, 1000);
        },

        formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
        }
    }));
});
</script>
@endpush
