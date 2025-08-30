{{-- Reading Part 2: Notice Matching --}}
<div class="reading-part-2">
    <h3 class="text-lg font-bold mb-4">Part 2: Notice Matching</h3>
    
    <div class="grid grid-cols-2 gap-4">
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Notices</h4>
            @foreach($question->getReadingNotices() as $index => $notice)
                <div class="mb-4 p-3 bg-gray-50 rounded">
                    <div class="notice-box border-2 border-gray-400 p-4 text-center">
                        {{ $notice }}
                    </div>
                    <div class="mt-2 text-sm text-gray-500">Notice {{ chr(65 + $index) }}</div>
                </div>
            @endforeach
        </div>
        
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Questions</h4>
            @foreach($question->options as $option)
                <div class="mb-4">
                    <div class="font-medium mb-2">{{ $loop->iteration }}. {{ $option->stem }}</div>
                    <div class="space-y-2">
                        @foreach(range('A', chr(65 + count($question->getReadingNotices()) - 1)) as $letter)
                            <label class="flex items-center gap-2">
                                <input type="radio" 
                                       name="answers[{{ $question->id }}_{{ $loop->parent->iteration }}]" 
                                       value="{{ $letter }}">
                                <span>Notice {{ $letter }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
