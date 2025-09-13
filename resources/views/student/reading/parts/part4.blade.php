<div>
    {{-- Part 4: long text with options pool --}}
    @php
        $meta = $question->metadata ?? [];
        $paragraphs = is_array($meta['paragraphs'] ?? null) ? $meta['paragraphs'] : [];
        $options = is_array($meta['options'] ?? null) ? $meta['options'] : [];
        $selected = is_array($answer->metadata['selected'] ?? null) ? $answer->metadata['selected'] : [];
    @endphp

    @php
        $pairs = [];
        foreach ($options as $optIndex => $opt) {
            $pairs[] = ['idx' => $optIndex, 'label' => $opt];
        }
        shuffle($pairs);
    @endphp

    @foreach($paragraphs as $i => $p)
        <div class="p-3 border rounded mb-3">
            <div class="mb-2 flex items-start gap-3">
                <div class="text-sm font-medium">{{ $i + 1 }}.</div>
                <div class="flex-1">
                    <select name="part4_choice[{{ $i }}]" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm">
                        <option value="">- Select -</option>
                        @foreach($pairs as $pair)
                            <option value="{{ e($pair['idx']) }}" @if(isset($selected[$i]) && (string)$selected[$i] === (string)$pair['idx']) selected @endif>{{ e($pair['label']) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-3 text-sm text-gray-700">{!! nl2br(e($p)) !!}</div>
        </div>
    @endforeach

    @includeWhen(true, 'student.reading.parts._check_helper')
</div>


<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>

