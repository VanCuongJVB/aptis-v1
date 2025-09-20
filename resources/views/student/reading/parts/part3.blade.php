<div class="space-y-6">
    @php
        $meta = $question->metadata ?? [];

        $people = [];
        if (is_array($question->items ?? null)) {
            $people = $question->items;
        } elseif (is_array($meta['items'] ?? null)) {
            $people = $meta['items'];
        } elseif (is_array($meta['people'] ?? null)) {
            $people = $meta['people'];
        }
        $stem = $question->stem ?? ($meta['stem'] ?? null);
        $options = is_array($question->options ?? null) ? $question->options : (is_array($meta['options'] ?? null) ? $meta['options'] : []);

    // Build correct answers array for JS feedback
    $correct = [];
    if (!empty($meta['answers']) && is_array($meta['answers'])) {
        // answers dạng: label => [option indices]
        // Chuyển về: mỗi option ứng với index người đúng
        $labelToIdx = [];
        foreach ($people as $idx => $p) {
            $label = is_array($p) ? ($p['label'] ?? $p['id'] ?? $p['name'] ?? chr(65 + $idx)) : $p;
            $labelToIdx[$label] = $idx;
        }
        foreach ($options as $optIdx => $optText) {
            $found = null;
            foreach ($meta['answers'] as $label => $arr) {
                if (is_array($arr) && in_array($optIdx, $arr)) {
                    $found = $labelToIdx[$label] ?? null;
                    break;
                }
            }
            $correct[] = $found;
        }
    } elseif (!empty($meta['correct']) && is_array($meta['correct'])) {
        $correct = $meta['correct'];
    }
    $metadataForJs = $meta;
    $metadataForJs['answers'] = $correct;
    @endphp

    {{-- Stem as main title --}}
    @if($stem)
        <h3 class="text-xl font-bold text-center mb-6">{!! $stem !!}</h3>
    @endif

    <div class="grid md:grid-cols-2 gap-6" data-metadata="@json($metadataForJs)">
        {{-- Left: people texts --}}
        <div class="space-y-4">
            @if(!empty($people))
                <h4 class="text-lg font-semibold">Texts <span class="text-sm text-gray-500">(A - {{ chr(64 + count($people)) }})</span></h4>
                <div class="grid grid-cols-1 gap-4">
                    @foreach($people as $idx => $p)
                        @php
                            $label = is_array($p) ? ($p['label'] ?? $p['id'] ?? $p['name'] ?? null) : $p;
                            $displayLabel = $label ?? chr(65 + $idx);
                            $title = is_array($p) ? ($p['name'] ?? $p['title'] ?? null) : null;
                            $body = is_array($p) ? ($p['text'] ?? $p['prompt'] ?? '') : '';
                        @endphp
                        <div class="bg-white shadow-sm border rounded-lg p-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-indigo-50 text-indigo-600 font-semibold">
                                    {{ e($displayLabel) }}
                                </div>
                                <div class="flex-1">
                                    @if($title)
                                        <div class="font-medium text-sm">{{ e($title) }}</div>
                                    @endif
                                    <div class="mt-2 text-sm text-gray-700 leading-relaxed">{!! nl2br(e($body)) !!}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Right: options with dropdowns --}}
        <div>
            <h4 class="text-lg font-semibold mb-2">Questions</h4>
            <div class="space-y-3">
                @foreach($options as $i => $optText)
                    <div class="bg-white border rounded-lg shadow-sm p-3 flex items-start justify-between gap-3">
                        <div class="flex-1 pr-3">
                            <div class="text-sm text-gray-700">{{ $i + 1 }}. {!! is_string($optText) ? $optText : json_encode($optText) !!}</div>
                        </div>
                        <div class="w-48 flex-shrink-0">
                            <select name="part3_answer[{{ $i }}]" class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <option value="">- Select person -</option>
                                @foreach($people as $pIdx => $p)
                                    @php $plabel = is_array($p) ? ($p['label'] ?? $p['id'] ?? $p['name'] ?? $pIdx) : $p; @endphp
                                    <option value="{{ $pIdx }}">{{ e($plabel) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Check result button --}}
    <div class="flex justify-center mt-4">
        @includeWhen(true, 'student.reading.parts._check_helper')
    </div>
</div>
