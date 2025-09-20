<div class="w-full max-w-3xl mx-auto p-4 question-block" 
     data-qid="{{ $question->id }}" 
     data-metadata='@json(array_merge($question->metadata, ["optionMapping" => array_keys($question->metadata['options'] ?? [])]))'>
    
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
        @if(!empty($question->stem))
            <p class="text-gray-700 mt-1">{{ $question->stem }}</p>
        @endif
    </div>

    <form class="space-y-3">
        @php
            $speakers = $question->metadata['speakers'] ?? [];
            $options = $question->metadata['options'] ?? [];
        @endphp

        @foreach($speakers as $idx => $speaker)
            <div class="p-3 border rounded-md">
                <div class="text-sm font-medium mb-2 flex items-center">
                    {{ $speaker['label'] }}
                </div>
                <select class="w-full border rounded p-2 speaker-select part2-select" data-index="{{ $idx }}">
                    <option value="">- Chọn câu mô tả -</option>
                    @foreach(array_keys($options) as $newIdx)
                        <option value="{{ $newIdx }}">{{ e($options[$newIdx]) }}</option>
                    @endforeach
                </select>
            </div>
        @endforeach
    </form>

    <div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
    <div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>
