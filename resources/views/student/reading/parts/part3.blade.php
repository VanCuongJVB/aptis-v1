<div class="space-y-6">
    @php
        $meta = $question->metadata ?? [];
        $people = is_array($meta['people'] ?? null) ? $meta['people'] : [];
        $items = is_array($meta['items'] ?? null) ? $meta['items'] : [];
        $selected = is_array($answer->metadata['selected'] ?? null) ? $answer->metadata['selected'] : [];
    @endphp

    {{-- Source texts (A, B, C...) --}}
    @if(!empty($people))
        <div>
            <h4 class="text-lg font-semibold mb-3">Texts <span class="text-sm text-gray-500">(A - {{ chr(64 + count($people)) }})</span></h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($people as $p)
                    <div class="bg-white shadow-sm border rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 flex items-center justify-center rounded-full bg-indigo-50 text-indigo-600 font-semibold">{{ e($p['id']) }}</div>
                            <div class="flex-1">
                                <div class="font-medium text-sm">{{ e($p['name'] ?? '') }}</div>
                                <div class="mt-2 text-sm text-gray-700 leading-relaxed">{!! nl2br(e($p['text'])) !!}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Questions --}}
    <div>
    <h4 class="text-lg font-semibold mb-3">Questions</h4>
        <div class="space-y-4">
            @foreach($items as $i => $it)
                @php
                    $prompt = is_array($it) ? ($it['prompt'] ?? $it['text'] ?? '') : $it;
                    $sel = $selected[$i] ?? null;
                @endphp

                <div class="bg-white border rounded-lg shadow-sm p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="text-sm text-gray-700 mb-3">{{ $i + 1 }}. {{ e($prompt) }}</div>
                            <select name="part3_answer[{{ $i }}]" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="">- Select -</option>
                                @foreach($people as $p)
                                    <option value="{{ e($p['id']) }}" @if((string)$sel === (string)($p['id'] ?? '')) selected @endif>{{ e($p['id'] . '. ' . trim($p['name'] ?? strip_tags($p['text'] ?? ''))) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Selected badge --}}
                        <div class="shrink-0 flex items-center">
                            @if(!empty($sel))
                                @php
                                    $match = collect($people)->firstWhere('id', $sel) ?: collect($people)->firstWhere('id', (string)$sel);
                                    $label = $match ? ($match['id'] . ' ' . ($match['name'] ?? '')) : $sel;
                                @endphp
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm font-medium">{{ e($label) }}</span>
                            @else
                                <span class="text-sm text-gray-400 italic">No answer</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @includeWhen(true, 'student.reading.parts._check_helper')
</div>


<div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>

