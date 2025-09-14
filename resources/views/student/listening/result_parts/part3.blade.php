@php
    $meta = $question->metadata ?? [];
    $ansMeta = $answer && isset($answer->metadata) ? $answer->metadata : null;

    // normalize user array
    $userArr = [];
    if (is_array($ansMeta)) {
        if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userArr = array_values($ansMeta['selected']);
        elseif (isset($ansMeta['values']) && is_array($ansMeta['values'])) $userArr = array_values($ansMeta['values']);
        elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) $userArr = array_values($ansMeta['value']);
        else $userArr = array_values($ansMeta);
    } elseif (is_string($ansMeta)) {
        $dec = json_decode($ansMeta, true);
        if (is_array($dec)) $userArr = array_values($dec);
    }

    $items = is_array($meta['items'] ?? null) ? $meta['items'] : [];
    $options = is_array($meta['options'] ?? null) ? $meta['options'] : [];
    $toText = function($v) {
        if (is_array($v)) return $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v, JSON_UNESCAPED_UNICODE);
        if (is_object($v)) return $v->text ?? $v->label ?? $v->content ?? $v->value ?? json_encode((array)$v, JSON_UNESCAPED_UNICODE);
        return (string)$v;
    };
    $answersKey = $meta['answers'] ?? $meta['correct'] ?? [];

    // If options are list of people objects, build map
    $peopleMap = [];
    if (!empty($meta['people']) && is_array($meta['people'])) {
        foreach ($meta['people'] as $p) {
            $id = $p['id'] ?? ($p['key'] ?? null);
            if ($id === null) continue;
            $peopleMap[(string)$id] = $p;
        }
    }
@endphp

<div class="mt-3 text-sm">
    @php
        $qTitle = $question->content ?? $question->title ?? ($question->metadata['title'] ?? null);
    @endphp
    @if(!empty($qTitle))
        <div class="text-sm font-medium mb-2">{{ e($qTitle) }}</div>
    @endif

    @if(empty($items))
        @if(!empty($userArr))
            <div class="ml-2 flex flex-wrap gap-2">
                @foreach($userArr as $k => $v)
                    <div class="px-2 py-1 rounded bg-yellow-100 text-yellow-800 text-sm">{{ is_array($v) ? json_encode($v) : $v }}</div>
                @endforeach
            </div>
        @else
            <div class="ml-2 text-gray-600">Chưa trả lời</div>
        @endif
    @else
        @php
            $detail = $computedDetails[$question->id] ?? null;
            $perMatches = $detail['matches'] ?? null;
            $perMatchCount = $detail['matchCount'] ?? null;
            $perTotal = is_array($perMatches) ? count($perMatches) : count($items);
        @endphp

        <div class="mb-2 text-sm text-gray-600">@if(!is_null($perMatchCount)) Đúng {{ $perMatchCount }} / {{ $perTotal }} @endif</div>

        <div class="space-y-3">
            @foreach($items as $i => $it)
                @php
                    $prompt = is_array($it) ? ($it['prompt'] ?? $it['text'] ?? '') : $it;
                    $raw = $userArr[$i] ?? null;
                    $userText = ($raw !== null && isset($options[$raw])) ? $toText($options[$raw]) : ($raw !== null ? (string)$raw : '');
                    $corrRaw = $answersKey[$i] ?? null;
                    $corrText = ($corrRaw !== null && isset($options[$corrRaw])) ? $toText($options[$corrRaw]) : ($corrRaw !== null ? (string)$corrRaw : '');
                    // prefer computed match flag when available
                    $isCorrect = null;
                    if (is_array($perMatches) && isset($perMatches[$i])) {
                        $isCorrect = (bool)($perMatches[$i]['match'] ?? false);
                    }
                    if (is_null($isCorrect)) {
                        $isCorrect = ($userText !== '' && $corrText !== '' && mb_strtolower(trim($userText)) === mb_strtolower(trim($corrText)));
                    }
                @endphp

                <div class="border rounded-lg p-4 bg-white">
                    <div class="text-sm text-gray-800 mb-2">{{ $i+1 }}. {{ e($prompt) }}</div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1">
                            <div class="px-3 py-2 rounded {{ $isCorrect ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }} text-sm">{{ $userText === '' ? 'Chưa trả lời' : e($userText) }}</div>
                        </div>
                        <div class="shrink-0 text-right">
                            @if($isCorrect)
                                <div class="text-green-700 text-sm">Đúng</div>
                            @else
                                <div class="text-red-700 text-sm">Sai</div>
                            @endif
                            @if(!$isCorrect && $corrText !== '')
                                <div class="text-xs text-gray-500 mt-1">Đáp án đúng: <span class="ml-1 font-medium">{{ e($corrText) }}</span></div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
