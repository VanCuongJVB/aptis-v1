@extends('layouts.app')
@section('title', $quiz->title)

@section('content')
<div x-data="readingQuiz" class="bg-white p-4 rounded shadow">
    {{-- Header with Timer --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-xl font-bold">{{ $quiz->title }}</h1>
            <p class="text-sm text-slate-600">{{ $quiz->description }}</p>
        </div>
        <div class="text-sm">
            <div class="font-medium">Time Remaining:</div>
            <div x-text="formatTime(timeRemaining)" class="text-xl font-mono text-red-600"></div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="h-2 bg-gray-200 rounded mb-4">
        <div class="h-full bg-blue-600 rounded transition-all duration-300"
             x-bind:style="'width: ' + (currentQuestionIndex / questions.length * 100) + '%'">
        </div>
    </div>

    {{-- Parts Navigation --}}
    <div class="flex gap-4 mb-6 overflow-x-auto py-2">
        @foreach(range(1,4) as $part)
            <button @click="goToPart({{ $part }})"
                    class="px-4 py-2 rounded border"
                    :class="{
                        'bg-blue-600 text-white': currentPart === {{ $part }},
                        'bg-white hover:bg-gray-100': currentPart !== {{ $part }}
                    }">
                Part {{ $part }}
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('student.quizzes.submit', $quiz) }}" id="quizForm" class="space-y-6">
        @csrf
        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
        
        {{-- Reading Content Area --}}
        <div class="grid grid-cols-2 gap-6">
            {{-- Left Panel: Reading Text/Content --}}
            <div class="border rounded p-4">
                <template x-if="currentPart === 1">
                    <div class="space-y-4">
                        <h3 class="font-bold text-lg">Part 1: Sentence Matching</h3>
                        <div class="space-y-3">
                            <template x-for="(sentence, index) in currentQuestion?.meta?.sentences" :key="index">
                                <div class="p-3 bg-gray-50 rounded">
                                    <span x-text="index + 1 + '.'"></span>
                                    <span x-text="sentence"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="currentPart === 2">
                    <div class="space-y-4">
                        <h3 class="font-bold text-lg">Part 2: Notice Matching</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <template x-for="(notice, index) in currentQuestion?.meta?.notices" :key="index">
                                <div class="p-4 bg-yellow-50 border-2 border-yellow-200 rounded">
                                    <div class="text-center font-medium mb-2" x-text="'Notice ' + (index + 1)"></div>
                                    <div x-text="notice"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="currentPart === 3">
                    <div class="space-y-4">
                        <h3 class="font-bold text-lg">Part 3: Reading Comprehension</h3>
                        <div class="prose max-w-none">
                            <div x-html="currentQuestion?.meta?.reading_text"></div>
                        </div>
                    </div>
                </template>

                <template x-if="currentPart === 4">
                    <div class="space-y-4">
                        <h3 class="font-bold text-lg">Part 4: Gap Filling</h3>
                        <div class="prose max-w-none">
                            <template x-for="(part, index) in splitGapText(currentQuestion?.meta?.gap_text)" :key="index">
                                <span>
                                    <span x-text="part.text"></span>
                                    <template x-if="part.isGap">
                                        <select 
                                            :name="'answers[' + currentQuestion?.id + '][' + part.gapIndex + ']'"
                                            class="inline-block border rounded px-2 py-1 mx-1"
                                            @change="setAnswer(currentQuestion?.id + '_' + part.gapIndex, $event.target.value)">
                                            <option value="">Select...</option>
                                            <template x-for="word in currentQuestion?.meta?.gap_options" :key="word">
                                                <option :value="word" x-text="word"></option>
                                            </template>
                                        </select>
                                    </template>
                                </span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Right Panel: Questions --}}
            <div class="border rounded p-4">
                <div class="font-medium mb-4">Questions</div>
                <template x-if="currentQuestion">
                    <div class="space-y-4">
                        <div class="text-lg font-medium" x-text="currentQuestion.stem"></div>
                        
                        <template x-if="['1', '2', '3'].includes(currentPart.toString())">
                            <div class="space-y-2">
                                <template x-for="(option, index) in currentQuestion.options" :key="option.id">
                                    <label class="flex items-start gap-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="radio" 
                                               :name="'answers[' + currentQuestion.id + ']'"
                                               :value="option.id"
                                               @change="setAnswer(currentQuestion.id, $event.target.value)">
                                        <span x-text="option.label"></span>
                                    </label>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        {{-- Navigation Buttons --}}
        <div class="flex justify-between mt-6">
            <button type="button" 
                    @click="prevQuestion"
                    :disabled="currentQuestionIndex === 0"
                    class="px-4 py-2 bg-gray-600 text-white rounded disabled:opacity-50">
                Previous Question
            </button>

            <button type="button" 
                    @click="nextQuestion"
                    :disabled="currentQuestionIndex === questions.length - 1"
                    class="px-4 py-2 bg-blue-600 text-white rounded disabled:opacity-50">
                Next Question
            </button>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-center mt-6">
            <button type="submit" 
                    @click.prevent="submitQuiz"
                    class="px-6 py-3 bg-green-600 text-white rounded hover:bg-green-700 font-medium">
                Submit Test
            </button>
        </div>
    </form>
  </div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('readingQuiz', () => ({
        currentPart: 1,
        currentQuestionIndex: 0,
        timeRemaining: {{ $quiz->time_limit * 60 ?? 3600 }},
        questions: @json($quiz->questions),
        answers: {},
        timer: null,

        init() {
            this.startTimer();
            window.addEventListener('beforeunload', (e) => {
                if (Object.keys(this.answers).length > 0) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },

        get currentQuestion() {
            return this.questions[this.currentQuestionIndex] || null;
        },

        startTimer() {
            this.timer = setInterval(() => {
                if (this.timeRemaining > 0) {
                    this.timeRemaining--;
                } else {
                    this.submitQuiz();
                }
            }, 1000);
        },

        formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        },

        goToPart(part) {
            this.currentPart = part;
            this.currentQuestionIndex = this.questions.findIndex(q => q.part === part);
        },

        nextQuestion() {
            if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
                this.currentPart = this.currentQuestion.part;
            }
        },

        prevQuestion() {
            if (this.currentQuestionIndex > 0) {
                this.currentQuestionIndex--;
                this.currentPart = this.currentQuestion.part;
            }
        },

        setAnswer(questionId, answer) {
            this.answers[questionId] = answer;
        },

        splitGapText(text) {
            if (!text) return [];
            const parts = text.split(/(\[gap\])/g);
            let gapIndex = 0;
            return parts.map(part => {
                if (part === '[gap]') {
                    return { text: '', isGap: true, gapIndex: gapIndex++ };
                }
                return { text: part, isGap: false };
            });
        },

        async submitQuiz() {
            if (!confirm('Are you sure you want to submit the test?')) {
                return;
            }

            try {
                const response = await fetch('{{ route("student.quizzes.submit", $quiz) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        answers: this.answers
                    })
                });

                if (response.ok) {
                    const result = await response.json();
                    window.location.href = result.redirect_url;
                } else {
                    alert('Error submitting quiz. Please try again.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error submitting quiz. Please try again.');
            }
        }
    }));
});
</script>
@endpush

@push('styles')
<style>
.prose {
    max-width: none;
    font-size: 1rem;
    line-height: 1.6;
}
.prose p {
    margin: 1em 0;
}
</style>
@endpush
@endsection