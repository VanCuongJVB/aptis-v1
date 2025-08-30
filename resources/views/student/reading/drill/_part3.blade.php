{{-- Part 3: Reading for Meaning --}}
<div class="space-y-6" x-data="readingMatching">
    {{-- Reading Passages Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach($question->meta['passages'] as $index => $passage)
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center mb-2">
                <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-indigo-100 text-indigo-800 text-sm font-medium">
                    {{ chr(65 + $index) }}
                </span>
                @if(isset($passage['title']))
                <h3 class="ml-2 text-lg font-medium">{{ $passage['title'] }}</h3>
                @endif
            </div>
            <div class="prose max-w-none text-sm">
                {!! $passage['text'] !!}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Current Question --}}
    <div class="bg-white rounded-lg border p-6">
        <h4 class="text-lg font-medium mb-4">{{ $question->stem }}</h4>
        
        {{-- Options (Text Passages) --}}
        <div class="space-y-3">
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
                    <span>Text {{ $option->text }}</span>
                </div>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Reading Strategy Tips --}}
    @if(isset($question->meta['strategy_tips']))
    <div class="mt-4 bg-blue-50 rounded-lg p-4" x-show="showAnswer">
        <h4 class="text-sm font-medium text-blue-800 mb-2">Reading Strategy:</h4>
        <div class="prose max-w-none text-blue-900 text-sm">
            {!! $question->meta['strategy_tips'] !!}
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('readingMatching', () => ({
        // Any part-specific functionality can be added here
    }));
});
</script>
@endpush
