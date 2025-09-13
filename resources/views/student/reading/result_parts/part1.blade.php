<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 1)</div>
    @php
        // normalize user answers (defensive)
        $userSelected = [];
        if (!empty($ansMeta)) {
            if (is_string($ansMeta)) {
                // try to json decode
                $decoded = json_decode($ansMeta, true);
                if (is_array($decoded)) $ansMeta = $decoded;
            }
            if (is_array($ansMeta)) {
                if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userSelected = array_values($ansMeta['selected']);
                else $userSelected = array_values($ansMeta);
            }
        }

        // authoritative answers in metadata
        $correctAnswers = [];
        if (!empty($meta['correct_answers']) && is_array($meta['correct_answers'])) $correctAnswers = $meta['correct_answers'];
        elseif (!empty($meta['answers']) && is_array($meta['answers'])) $correctAnswers = $meta['answers'];
    @endphp

    @if(empty($userSelected))
        <div class="ml-2 text-gray-600">Chưa trả lời</div>
    @else
        <div class="space-y-2">
                @foreach($userSelected as $i => $val)
                @php
                    // ensure scalar for display
                    if (is_array($val) || is_object($val)) {
                        $u = json_encode($val, JSON_UNESCAPED_UNICODE);
                    } else {
                        $u = (string)$val;
                    }
                    $correctRaw = $correctAnswers[$i] ?? null;
                    $isCorrect = null;
                    if ($correctRaw !== null) {
                        // compare texts (best-effort) or direct equality
                        if ((string)$correctRaw === $u) $isCorrect = true;
                        else {
                            $a = mb_strtolower(trim($u));
                            $b = mb_strtolower(trim((string)$correctRaw));
                            $isCorrect = ($a === $b);
                        }
                    }

                    $itemClass = $chipClass ?? 'bg-blue-100 text-blue-800';
                    if ($isCorrect === true) $itemClass = 'bg-green-100 text-green-800';
                    elseif ($isCorrect === false) $itemClass = 'bg-red-100 text-red-800';
                @endphp

                <div class="flex items-center gap-3">
                    <div class="px-3 py-1 rounded {{ $itemClass }} text-sm flex-1">{{ e($u) }}</div>
                    <div class="shrink-0 text-sm">
                        @if($isCorrect === true)
                            <span class="inline-flex items-center gap-1 text-green-700">✓</span>
                        @elseif($isCorrect === false)
                            <span class="inline-flex items-center gap-1 text-red-700">✕</span>
                        @else
                            <span class="text-gray-400 italic">?</span>
                        @endif
                    </div>

                    @if($isCorrect === false && $correctRaw !== null)
                        <div class="text-xs text-gray-500">Đáp án đúng: <span class="ml-1 font-medium">{{ e($correctRaw) }}</span></div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
