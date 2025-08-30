{{-- Part 2: Text Completion --}}
<div class="space-y-6">
    {{-- Main Text with Gaps --}}
    <div class="prose max-w-none">
        <div class="bg-gray-50 rounded-lg p-4" x-data="textCompletion">
            @php
                $text = $question->passage;
                $gaps = $question->meta['gaps'] ?? [];
                foreach ($gaps as $index => $gap) {
                    $gapNumber = $index + 1;
                    $gapHtml = '<span class="inline-block min-w-[80px] text-center border-b-2 border-gray-400 mx-1" x-text="answers[' . $index . '] || \'___\'"></span>';
                    $text = str_replace("[gap-$gapNumber]", $gapHtml, $text);
                }
            @endphp
            {!! $text !!}
        </div>
    </div>

    {{-- Word Bank (if enabled) --}}
    @if(isset($question->meta['show_word_bank']) && $question->meta['show_word_bank'])
    <div class="mt-4 p-4 bg-gray-100 rounded-lg">
        <h4 class="text-sm font-medium text-gray-700 mb-2">Word Bank:</h4>
        <div class="flex flex-wrap gap-2">
            @foreach($question->options as $option)
            <button @click="selectOption('{{ $option->id }}')"
                    :class="{
                        'ring-2 ring-indigo-500': selectedOption === '{{ $option->id }}',
                        'bg-green-50': showAnswer && '{{ $option->id }}' === correctAnswer,
                        'bg-red-50': showAnswer && selectedOption === '{{ $option->id }}' && selectedOption !== correctAnswer
                    }"
                    class="px-3 py-1 rounded border hover:border-indigo-500 transition-colors"
                    :disabled="showAnswer || isWordUsed('{{ $option->id }}')">
                {!! $option->text !!}
            </button>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Options for Current Gap --}}
    <div x-show="!showWordBank" class="grid grid-cols-1 gap-4">
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
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('textCompletion', () => ({
        currentGap: 0,
        answers: Array({{ count($question->meta['gaps'] ?? []) }}).fill(''),
        showWordBank: {{ isset($question->meta['show_word_bank']) && $question->meta['show_word_bank'] ? 'true' : 'false' }},
        
        init() {
            this.$watch('selectedOption', value => {
                if (value && this.showWordBank) {
                    this.answers[this.currentGap] = this.getOptionText(value);
                    this.currentGap = Math.min(this.currentGap + 1, this.answers.length - 1);
                }
            });
        },

        getOptionText(optionId) {
            const option = @json($question->options);
            return option.find(o => o.id === optionId)?.text || '';
        },

        isWordUsed(optionId) {
            return this.answers.includes(this.getOptionText(optionId));
        }
    }));
});
</script>
@endpush
