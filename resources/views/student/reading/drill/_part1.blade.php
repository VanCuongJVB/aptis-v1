{{-- Part 1: Sentence Completion --}}
<div class="space-y-6">
    {{-- Question Text/Passage --}}
    <div class="prose max-w-none">
        <div class="bg-gray-50 rounded-lg p-4">
            {!! preg_replace('/\[gap\]/', '<span class="px-8 mx-1 border-b-2 border-gray-400">____</span>', $question->passage) !!}
        </div>
    </div>

    {{-- Options Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($question->options as $option)
        <button @click="selectOption('{{ $option->id }}')"
                :class="{
                    'ring-2 ring-indigo-500': selectedOption === '{{ $option->id }}',
                    'bg-green-50': showAnswer && '{{ $option->id }}' === correctAnswer,
                    'bg-red-50': showAnswer && selectedOption === '{{ $option->id }}' && selectedOption !== correctAnswer
                }"
                class="w-full text-left p-4 rounded-lg border hover:border-indigo-500 transition-colors"
                :disabled="showAnswer">
            <div class="flex items-start">
                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full border text-sm font-medium mr-3">
                    {{ chr(64 + $loop->iteration) }}
                </span>
                <div class="prose max-w-none">
                    {!! $option->text !!}
                </div>
            </div>
        </button>
        @endforeach
    </div>

    {{-- Example (if available) --}}
    @if(isset($question->meta['example']))
    <div class="mt-4 bg-blue-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-blue-800 mb-2">Example:</h4>
        <div class="prose max-w-none text-blue-900">
            {!! $question->meta['example'] !!}
        </div>
    </div>
    @endif
</div>
