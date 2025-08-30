{{-- Reading Part 3: Long Text Reading --}}
<div class="reading-part-3">
    <h3 class="text-lg font-bold mb-4">Part 3: Reading Comprehension</h3>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Reading Text</h4>
            <div class="prose max-w-none">
                {!! nl2br(e($question->getReadingText())) !!}
            </div>
        </div>
        
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Questions</h4>
            @foreach($question->options as $index => $option)
                <div class="mb-4">
                    <div class="font-medium mb-2">{{ $loop->iteration }}. {{ $option->stem }}</div>
                    <div class="space-y-2">
                        @foreach($option->choices ?? [] as $choice)
                            <label class="flex items-center gap-2">
                                <input type="radio" 
                                       name="answers[{{ $question->id }}_{{ $loop->parent->iteration }}]" 
                                       value="{{ $choice['value'] }}">
                                <span>{{ $choice['text'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
