@php
    $meta = $question->metadata ?? [];
    $ansMeta = $answer && isset($answer->metadata) ? $answer->metadata : null;

    // normalize selected value
    $selected = null;
    if (is_array($ansMeta)) {
        if (isset($ansMeta['selected']['option_id'])) $selected = $ansMeta['selected']['option_id'];
        elseif (isset($ansMeta['selected'])) $selected = $ansMeta['selected'];
    }
    if ($selected === null && isset($answer->selected_option_id)) $selected = $answer->selected_option_id;

    $options = is_array($meta['options'] ?? null) ? $meta['options'] : [];
    $correctIndex = $meta['correct_index'] ?? $meta['correct'] ?? null;

    // helper to render option value as text
    $toText = function($v) {
        if (is_array($v)) {
            return $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v, JSON_UNESCAPED_UNICODE);
        }
        if (is_object($v)) {
            return $v->text ?? $v->label ?? $v->content ?? $v->value ?? json_encode((array)$v, JSON_UNESCAPED_UNICODE);
        }
        return (string)$v;
    };

    // compute normalized scalar keys for selected and correct to avoid casting arrays to string
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

<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 1)</div>

    @if($selected === null)
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
