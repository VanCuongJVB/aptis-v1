@php
    // normalize answer metadata into a numeric-indexed array of selections
    $userSelected = [];
    if (!empty($ansMeta)) {
        if (is_array($ansMeta)) {
            if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userSelected = array_values($ansMeta['selected']);
            elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) $userSelected = array_values($ansMeta['value']);
            else {
                $maybe = array_values($ansMeta);
                if (count($maybe) === 1 && is_array($maybe[0])) $userSelected = array_values($maybe[0]);
                else $userSelected = $maybe;
            }
        } elseif (is_string($ansMeta)) {
            $dec = json_decode($ansMeta, true);
            if (is_array($dec)) $userSelected = array_values($dec);
        }
    }

    $paragraphs = is_array($meta['paragraphs'] ?? null) ? $meta['paragraphs'] : [];
    $options = $meta['options'] ?? [];
    $corrects = $meta['answers'] ?? $meta['correct'] ?? [];

    // Build option lookup maps: by index and by id
    $optByIndex = [];
    $optById = [];
    if (is_array($options)) {
        foreach ($options as $k => $v) {
            if (is_array($v)) {
                $text = $v['text'] ?? $v['label'] ?? $v['content'] ?? $v['value'] ?? json_encode($v);
                if (isset($v['id'])) $optById[(string)$v['id']] = $text;
            } elseif (is_object($v)) {
                $text = $v->text ?? $v->label ?? $v->content ?? $v->value ?? json_encode((array)$v);
                if (isset($v->id)) $optById[(string)$v->id] = $text;
            } else {
                $text = (string)$v;
            }
            $optByIndex[(string)$k] = $text;
        }
    }
@endphp

<div class="mt-3 text-sm space-y-4">
    <div class="font-medium">Đáp án của bạn (Part 4)</div>

    @if(empty($paragraphs))
        <div class="ml-2 text-gray-600">Không có dữ liệu</div>
    @else
        <div class="space-y-3">
            @foreach($paragraphs as $i => $p)
                @php
                    $sel = $userSelected[$i] ?? null;
                    $selLabel = null;
                    if ($sel !== null) {
                        $sKey = (string)$sel;
                        if (isset($optByIndex[$sKey])) $selLabel = $optByIndex[$sKey];
                        elseif (isset($optById[$sKey])) $selLabel = $optById[$sKey];
                        elseif (isset($optByIndex[(int)$sKey])) $selLabel = $optByIndex[(int)$sKey] ?? null;
                        else $selLabel = (string)$sel;
                    }

                    $correctRaw = $corrects[$i] ?? null;
                    $correctLabel = null;
                    if ($correctRaw !== null) {
                        if (is_numeric($correctRaw) || is_string($correctRaw)) {
                            $cKey = (string)$correctRaw;
                            if (isset($optByIndex[$cKey])) $correctLabel = $optByIndex[$cKey];
                            elseif (isset($optById[$cKey])) $correctLabel = $optById[$cKey];
                            elseif (isset($optByIndex[(int)$cKey])) $correctLabel = $optByIndex[(int)$cKey] ?? null;
                            else $correctLabel = (string)$correctRaw;
                        } elseif (is_array($correctRaw) || is_object($correctRaw)) {
                            $correctLabel = is_array($correctRaw) ? json_encode($correctRaw) : json_encode((array)$correctRaw);
                        }
                    }

                    // determine correctness consistently: prefer label comparison when possible
                    $isCorrect = null;
                    if ($correctRaw !== null && $sel !== null) {
                        // direct raw equality
                        if ((string)$sel === (string)$correctRaw) $isCorrect = true;
                        else {
                            $a = mb_strtolower(trim((string)($selLabel ?? $sel)));
                            $b = mb_strtolower(trim((string)($correctLabel ?? $correctRaw)));
                            $isCorrect = ($a === $b);
                        }
                    }
                @endphp

                <div class="bg-white border rounded-lg shadow-sm p-4">
                    <div class="flex-1">
                        <div class="text-sm text-gray-800 mb-2">{{ $i + 1 }}.</div>
                        <select disabled class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm mb-3">
                            <option value="">- Select -</option>
                            @foreach($options as $optIdx => $optText)
                                <option value="{{ e($optIdx) }}" @if((string)($sel ?? '') === (string)$optIdx) selected @endif>{{ e($optText) }}</option>
                            @endforeach
                        </select>
                        <div class="text-sm text-gray-700 leading-relaxed">{!! nl2br(e($p)) !!}</div>

                        {{-- badges and correct answer below so long labels wrap naturally --}}
                        <div class="mt-3">
                            @if($isCorrect === true)
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm"> 
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
                                    <span>Đúng</span>
                                </div>
                            @elseif($isCorrect === false)
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95A1 1 0 013.636 14.95L8.586 10 3.636 5.05A1 1 0 015.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
                                    <span>Sai</span>
                                </div>
                            @else
                                <div class="text-sm text-gray-400 italic">Không xác định</div>
                            @endif

                            @if(!empty($correctLabel) && $isCorrect !== true)
                                <div class="mt-2 text-xs text-gray-500">Đáp án đúng</div>
                                <div class="mt-1 block text-sm text-green-700 bg-green-50 px-2 py-1 rounded break-words whitespace-normal">{{ e($correctLabel) }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
