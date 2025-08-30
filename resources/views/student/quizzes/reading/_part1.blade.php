{{-- Reading Part 1: Sentence Matching --}}
<div class="reading-part-1">
    <h3 class="text-lg font-bold mb-4">Part 1: Sentence Matching</h3>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Sentences</h4>
            @foreach($question->meta['sentences'] ?? [] as $index => $sentence)
                <div class="mb-2 p-2 bg-gray-50 rounded">
                    {{ $index + 1 }}. {{ $sentence }}
                </div>
            @endforeach
        </div>
        
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Match with</h4>
            @foreach($question->options as $option)
                <label class="flex items-start gap-2 mb-2 p-2 bg-gray-50 rounded">
                    <input type="radio" 
                           name="answers[{{ $question->id }}][]" 
                           value="{{ $option->id }}">
                    <span>{{ $option->label }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
