@php
    $meta = $question->metadata ?? [];
    $ansMeta = $answer && isset($answer->metadata) ? $answer->metadata : null;

    // normalize selected value (support several shapes)
    $selected = null;
    if (is_array($ansMeta)) {
        if (isset($ansMeta['selected']['option_id'])) $selected = $ansMeta['selected']['option_id'];
        elseif (isset($ansMeta['selected']) && !is_array($ansMeta['selected'])) $selected = $ansMeta['selected'];
        elseif (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) {
            $s = $ansMeta['selected'];
            if (isset($s['option_id'])) $selected = $s['option_id'];
            elseif (isset($s[0]) && !is_array($s[0])) $selected = $s[0];
        } elseif (isset($ansMeta['option_id'])) $selected = $ansMeta['option_id'];
    } elseif (is_string($ansMeta)) {
        $dec = json_decode($ansMeta, true);
        if (is_array($dec) && isset($dec['selected'])) $selected = $dec['selected'];
    }
    if ($selected === null && isset($answer->selected_option_id)) $selected = $answer->selected_option_id;

    $options = is_array($meta['options'] ?? null) ? $meta['options'] : [];
    $correctIndex = $meta['correct_index'] ?? $meta['correct'] ?? null;

    $toText = function($v) {
        if (is_array($v)) {
            return $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v, JSON_UNESCAPED_UNICODE);
        }
        if (is_object($v)) {
            return $v->text ?? $v->label ?? $v->content ?? $v->value ?? json_encode((array)$v, JSON_UNESCAPED_UNICODE);
        }
        return (string)$v;
    };

    // normalize keys
    $sKeyNormalized = null;
    if ($selected !== null) {
        if (is_array($selected)) {
            if (isset($selected['option_id'])) $sKeyNormalized = (string)$selected['option_id'];
            elseif (isset($selected[0])) $sKeyNormalized = (string)$selected[0];
        } elseif (is_object($selected)) {
            if (isset($selected->option_id)) $sKeyNormalized = (string)$selected->option_id;
        } else {
            $sKeyNormalized = (string)$selected;
        }
    }

    $selectedLabel = null;
    if ($sKeyNormalized !== null && isset($options[$sKeyNormalized])) {
        $selectedLabel = $toText($options[$sKeyNormalized]);
    } elseif ($sKeyNormalized !== null && isset($options[(int)$sKeyNormalized])) {
        $selectedLabel = $toText($options[(int)$sKeyNormalized]);
    } else {
        $selectedLabel = is_array($selected) || is_object($selected) ? json_encode($selected, JSON_UNESCAPED_UNICODE) : (string)$selected;
    }

    $cKeyNormalized = null;
    if ($correctIndex !== null) {
        if (is_array($correctIndex)) {
            if (isset($correctIndex['option_id'])) $cKeyNormalized = (string)$correctIndex['option_id'];
            elseif (isset($correctIndex[0])) $cKeyNormalized = (string)$correctIndex[0];
        } elseif (is_object($correctIndex)) {
            if (isset($correctIndex->option_id)) $cKeyNormalized = (string)$correctIndex->option_id;
        } else {
            $cKeyNormalized = (string)$correctIndex;
        }
    }

    $correctLabel = null;
    if ($cKeyNormalized !== null && isset($options[$cKeyNormalized])) {
        $correctLabel = $toText($options[$cKeyNormalized]);
    } elseif ($cKeyNormalized !== null && isset($options[(int)$cKeyNormalized])) {
        $correctLabel = $toText($options[(int)$cKeyNormalized]);
    } else {
        $correctLabel = is_array($correctIndex) || is_object($correctIndex) ? json_encode($correctIndex, JSON_UNESCAPED_UNICODE) : (string)$correctIndex;
    }

    $isCorrect = null;
    if ($cKeyNormalized !== null && $sKeyNormalized !== null) {
        $isCorrect = ((string)$sKeyNormalized === (string)$cKeyNormalized || ((is_numeric($sKeyNormalized) || is_numeric($cKeyNormalized)) && (int)$sKeyNormalized === (int)$cKeyNormalized));
    }
@endphp

<div class="prose mb-2">{!! $question->stem ?? '' !!}</div>

@php
    // detect mapping-style answers for speakers (arrays of selections)
    $mappingOrder = null;
    if (is_array($ansMeta) && isset($ansMeta['order']) && is_array($ansMeta['order'])) {
        $mappingOrder = array_values($ansMeta['order']);
    } elseif (is_array($ansMeta) && isset($ansMeta['originalIndices']) && is_array($ansMeta['originalIndices'])) {
        $mappingOrder = array_values($ansMeta['originalIndices']);
    } elseif (is_array($ansMeta) && isset($ansMeta['texts']) && is_array($ansMeta['texts']) && isset($meta['options']) && is_array($meta['options'])) {
        // fallback: try to map texts to option indexes
        $mappingOrder = [];
        foreach ($ansMeta['texts'] as $t) {
            $found = array_search($t, $meta['options']);
            $mappingOrder[] = $found === false ? null : $found;
        }
    }

    $speakers = is_array($meta['speakers'] ?? null) ? $meta['speakers'] : [];
    $answersCorr = is_array($meta['answers'] ?? null) ? array_values($meta['answers']) : [];
@endphp

<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 2)</div>

    @if($mappingOrder !== null)
        <div class="mt-2 space-y-3">
            @foreach($mappingOrder as $i => $selIdx)
                @php
                    $speakerLabel = isset($speakers[$i]['label']) ? $speakers[$i]['label'] : (isset($speakers[$i]) && is_string($speakers[$i]) ? $speakers[$i] : 'Speaker ' . ($i+1));
                    $userSel = $selIdx;
                    $userLabel = ($userSel !== null && isset($meta['options'][$userSel])) ? $toText($meta['options'][$userSel]) : ($userSel !== null ? (string)$userSel : null);
                    $corrIdx = $answersCorr[$i] ?? null;
                    $corrLabel = ($corrIdx !== null && isset($meta['options'][$corrIdx])) ? $toText($meta['options'][$corrIdx]) : ($corrIdx !== null ? (string)$corrIdx : null);
                    $rowCorrect = ($userSel !== null && $corrIdx !== null && ((string)$userSel === (string)$corrIdx || (int)$userSel === (int)$corrIdx));
                @endphp

                <div class="grid grid-cols-2 gap-4 items-start bg-white border rounded p-3">
                    <div>
                        <div class="text-xs text-gray-500">{{ $speakerLabel }} — Lựa chọn của bạn</div>
                        <div class="mt-1 px-3 py-2 rounded {{ $rowCorrect ? 'bg-green-50 text-green-800' : (empty($userLabel) ? 'bg-gray-50 text-gray-500' : 'bg-red-50 text-red-800') }} text-sm">{{ empty($userLabel) ? 'Chưa trả lời' : e($userLabel) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Đáp án đúng</div>
                        <div class="mt-1 px-3 py-2 rounded bg-white border text-sm text-gray-700">{{ $corrLabel === null ? '(không có)' : e($corrLabel) }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-2">
            @if($selected === null || $selectedLabel === null || $selectedLabel === '')
                <div class="ml-2 text-gray-600">Chưa trả lời</div>
            @else
                <div class="flex items-center gap-3 mt-2">
                    <div class="px-3 py-1 rounded {{ $isCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-sm">{{ e($selectedLabel) }}</div>
                    @if($isCorrect)
                        <div class="text-sm text-green-700">Đúng</div>
                    @else
                        <div class="text-sm text-red-700">Sai</div>
                    @endif
                </div>

                @if(!$isCorrect && $correctLabel !== null)
                    <div class="mt-2 text-xs text-gray-500">Đáp án đúng: <span class="ml-1 font-medium">{{ e($correctLabel) }}</span></div>
                @endif
            @endif
        </div>
    @endif
</div>

@push('scripts')
<script>
    try {
        console.log('Listening result part2: question=', {!! json_encode($question ?? null, JSON_UNESCAPED_UNICODE) !!});
        console.log('Listening result part2: meta=', {!! json_encode($meta ?? null, JSON_UNESCAPED_UNICODE) !!});
        console.log('Listening result part2: answer=', {!! json_encode($answer ?? null, JSON_UNESCAPED_UNICODE) !!});
        console.log('Listening result part2: normalized selected=', {!! json_encode($selected ?? null, JSON_UNESCAPED_UNICODE) !!}, 'sKey=', {!! json_encode($sKeyNormalized ?? null, JSON_UNESCAPED_UNICODE) !!}, 'label=', {!! json_encode($selectedLabel ?? null, JSON_UNESCAPED_UNICODE) !!});
    } catch (e) { console.error('Error logging part2 debug:', e); }
</script>
@endpush
