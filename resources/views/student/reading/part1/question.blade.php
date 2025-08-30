@extends('layouts.student')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <!-- Question Navigation -->
        <div class="mb-6 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Question {{ $currentNumber }} of {{ $totalQuestions }}
            </div>
            <div class="flex space-x-2">
                @if($previousQuestion)
                <a href="{{ route('reading.drill.question', ['quiz' => $quiz, 'question' => $previousQuestion]) }}"
                   class="px-3 py-1 bg-gray-100 rounded text-gray-700 hover:bg-gray-200">
                    Previous
                </a>
                @endif
                
                @if($nextQuestion)
                <a href="{{ route('reading.drill.question', ['quiz' => $quiz, 'question' => $nextQuestion]) }}"
                   class="px-3 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    Next
                </a>
                @endif
            </div>
        </div>

        <!-- Question Content -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <!-- Sentence with Gap -->
            <p class="text-lg mb-6">
                {!! $question->getSentenceWithGap() !!}
            </p>

            <!-- Options -->
            <form id="answerForm" class="space-y-4">
                @foreach($question->options as $option)
                <label class="flex items-center space-x-3 p-3 border rounded hover:bg-gray-50 cursor-pointer
                             {{ $submittedAnswer == $option->id ? 
                                ($option->is_correct ? 'bg-green-50 border-green-500' : 'bg-red-50 border-red-500') 
                                : '' }}">
                    <input type="radio" 
                           name="answer" 
                           value="{{ $option->id }}"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500"
                           {{ $submittedAnswer == $option->id ? 'checked' : '' }}
                           {{ $submittedAnswer ? 'disabled' : '' }}>
                    <span class="text-gray-900">{{ $option->label }}</span>

                    @if($submittedAnswer == $option->id)
                        @if($option->is_correct)
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @endif
                    @endif
                </label>
                @endforeach
            </form>

            <!-- Answer Button -->
            @if(!$submittedAnswer)
            <div class="mt-6">
                <button onclick="submitAnswer()"
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 disabled:opacity-50"
                        id="submitButton" disabled>
                    Submit Answer
                </button>
            </div>
            @endif

            <!-- Explanation -->
            @if($submittedAnswer && $question->explanation)
            <div class="mt-6 p-4 bg-blue-50 rounded">
                <h3 class="font-medium text-blue-800 mb-2">Explanation</h3>
                <p class="text-blue-900">{{ $question->explanation }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('input[name="answer"]').forEach(input => {
    input.addEventListener('change', () => {
        document.getElementById('submitButton').disabled = false;
    });
});

function submitAnswer() {
    const answer = document.querySelector('input[name="answer"]:checked').value;
    const form = document.getElementById('answerForm');

    fetch('{{ route("reading.drill.answer") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            question_id: '{{ $question->id }}',
            answer: answer,
            attempt_id: '{{ $attempt->id }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.is_correct) {
            // Show success feedback
            location.reload();
        } else {
            // Show error feedback
            location.reload();
        }
    });
}
</script>
@endpush
@endsection
