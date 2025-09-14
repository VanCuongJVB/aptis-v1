@php
    $meta = $question->metadata ?? [];
    $ansMeta = $answer && isset($answer->metadata) ? $answer->metadata : null;

    // normalize user vals
    $userVals = [];
    if (is_array($ansMeta)) {
        if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userVals = array_values($ansMeta['selected']);
        elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) $userVals = array_values($ansMeta['value']);
        else {
            $maybe = array_values($ansMeta);
            if (count($maybe) === 1 && is_array($maybe[0])) $userVals = array_values($maybe[0]);
            else $userVals = $maybe;
        }
    } elseif (is_string($ansMeta)) {
        $dec = json_decode($ansMeta, true);
        if (is_array($dec)) $userVals = array_values($dec);
    }

    $paragraphs = is_array($meta['paragraphs'] ?? null) ? $meta['paragraphs'] : [];
    $options = $meta['options'] ?? [];
    // support multiple shapes for correct answers: answers, correct, or correct_index (scalar or array)
    $corrects = $meta['answers'] ?? $meta['correct'] ?? (isset($meta['correct_index']) ? $meta['correct_index'] : []);

    $optByIndex = [];
    $optById = [];
    $toText = function($v) {
        if (is_array($v)) return $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v, JSON_UNESCAPED_UNICODE);
        if (is_object($v)) return $v->text ?? $v->label ?? $v->content ?? $v->value ?? json_encode((array)$v, JSON_UNESCAPED_UNICODE);
        return (string)$v;
    };
    if (is_array($options)) {
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                $text = $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v);
                if (isset($v['id'])) $optById[(string)$v['id']] = $text;
            } else {
                $text = (string)$v;
            }
            $optByIndex[(string)$k] = $text;
        }
    }

    $correctCount = is_array($corrects) ? count($corrects) : ($corrects !== null && $corrects !== '' ? 1 : 0);
    $count = max(count($paragraphs), count($userVals), $correctCount);
@endphp

<div class="mt-3 text-sm space-y-4">
    <div class="mb-2">
        <h2 class="text-sm font-medium mb-1">Câu hỏi {{ $question->order_no ?? '' }}</h2>
        @if(!empty($question->stem))
            <div class="text-sm text-gray-800 font-medium">{{ e($question->stem) }}</div>
        @endif
        @if(!empty($question->content))
            <div class="text-sm text-gray-700 mt-1">{{ e($question->content) }}</div>
        @endif
    </div>
    @if($count === 0)
        <div class="ml-2 text-gray-600">Không có dữ liệu</div>
    @else
        <div class="space-y-3">
            @for($i = 0; $i < $count; $i++)
                @php
                    $raw = $userVals[$i] ?? null;
                    // normalize raw selection: if array like ['0'] -> use first element
                    if (is_array($raw)) $raw = count($raw) ? $raw[0] : null;

                    // coerce numeric strings to ints for stable comparison
                    if (is_string($raw) && is_numeric($raw) && ctype_digit($raw)) {
                        $raw = (int)$raw;
                    }

                    $selLabel = '';
                    if ($raw !== null && $raw !== '') {
                        // prefer index match (int/string), then id match, then fallback to text
                        $sKey = (string)$raw;
                        if (isset($optByIndex[$sKey])) {
                            $selLabel = $toText($optByIndex[$sKey]);
                        } elseif (isset($optByIndex[(int)$sKey])) {
                            $selLabel = $toText($optByIndex[(int)$sKey] ?? '');
                        } elseif (isset($optById[$sKey])) {
                            $selLabel = $toText($optById[$sKey]);
                        } else {
                            $selLabel = (string)$raw;
                        }
                    }

                    if (is_array($corrects)) {
                        $correctRaw = $corrects[$i] ?? null;
                    } else {
                        // scalar correct_index applies to each paragraph
                        $correctRaw = $corrects;
                    }
                    // if correctRaw is array pick first
                    if (is_array($correctRaw)) $correctRaw = count($correctRaw) ? $correctRaw[0] : null;
                    // coerce numeric-string corrects to int
                    if (is_string($correctRaw) && is_numeric($correctRaw) && ctype_digit($correctRaw)) {
                        $correctRaw = (int)$correctRaw;
                    }

                    $correctLabel = '';
                    if ($correctRaw !== null && $correctRaw !== '') {
                        $cKey = (string)$correctRaw;
                        if (isset($optByIndex[$cKey])) $correctLabel = $toText($optByIndex[$cKey]);
                        elseif (isset($optById[$cKey])) $correctLabel = $toText($optById[$cKey]);
                        elseif (isset($optByIndex[(int)$cKey])) $correctLabel = $toText($optByIndex[(int)$cKey] ?? '');
                        else $correctLabel = (string)$correctRaw;
                    }

                    $isCorrect = null;
                    if ($correctRaw !== null && $raw !== null) {
                        if ((string)$raw === (string)$correctRaw) $isCorrect = true;
                        else {
                            $a = mb_strtolower(trim((string)($selLabel ?: $raw)));
                            $b = mb_strtolower(trim((string)($correctLabel ?: $correctRaw)));
                            $isCorrect = ($a === $b);
                        }
                    }
                @endphp

                <div class="bg-white border rounded-lg shadow-sm p-4">
                    <select disabled class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm mb-3">
                        <option value="">- Select -</option>
                        @foreach($options as $optIdx => $optText)
                            <option value="{{ e($optIdx) }}" @if((string)($raw ?? '') === (string)$optIdx) selected @endif>{{ e($toText($optText)) }}</option>
                        @endforeach
                    </select>
                    <div class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($paragraphs[$i] ?? '')) !!}</div>

                    <div class="mt-3">
                        @if($isCorrect === true)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm">Đúng</div>
                        @elseif($isCorrect === false)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm">Sai</div>
                        @else
                            <div class="text-sm text-gray-400 italic">Không xác định</div>
                        @endif

                        @if($correctLabel !== '' && $isCorrect !== true)
                            <div class="mt-2 text-xs text-gray-500">Đáp án đúng</div>
                            <div class="mt-1 block text-sm text-green-700 bg-green-50 px-2 py-1 rounded break-words whitespace-normal">{{ e($correctLabel) }}</div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    @endif
</div>

