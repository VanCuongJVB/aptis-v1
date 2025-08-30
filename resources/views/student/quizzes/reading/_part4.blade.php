{{-- Reading Part 4: Gap Filling --}}
<div class="reading-part-4">
    <h3 class="text-lg font-bold mb-4">Part 4: Gap Filling</h3>
    
    <div class="space-y-4">
        <div class="border p-4 rounded">
            <h4 class="font-medium mb-2">Text with Gaps</h4>
            <div class="prose max-w-none gap-text">
                @php
                    $gapText = $question->getGapFillingText();
                    $parts = preg_split('/\[gap\]/', $gapText);
                @endphp
                
                @foreach($parts as $index => $part)
                    {{ $part }}
                    @if($index < count($parts) - 1)
                        <select name="answers[{{ $question->id }}_{{ $index + 1 }}]" 
                                class="inline-block border rounded px-2 py-1">
                            <option value="">Select...</option>
                            @foreach($question->getGapOptions() as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    @endif
                @endforeach
            </div>
        </div>
        
        <div class="border p-4 rounded bg-gray-50">
            <h4 class="font-medium mb-2">Available Words</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($question->getGapOptions() as $option)
                    <span class="px-3 py-1 bg-white border rounded">{{ $option }}</span>
                @endforeach
            </div>
        </div>
    </div>
</div>
