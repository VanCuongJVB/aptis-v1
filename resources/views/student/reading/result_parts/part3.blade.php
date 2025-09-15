@php
    // Normalize ansMeta into a per-option map of selected person-id keys (strings)
    $userSelected = [];
    $rawAns = $ansMeta;
    if (is_string($rawAns)) {
        $dec = json_decode($rawAns, true);
        if (is_array($dec)) $rawAns = $dec;
    }

    // Cấu hình cố định: 0=>A, 1=>B, 2=>C, 3=>D, ...
    $labelMap = ['A','B','C','D','E','F','G','H','I','J'];
    $peopleMap = [];
    foreach ($labelMap as $i => $label) {
        $peopleMap[$label] = [
            'id' => $label,
            'label' => $label,
        ];
    }
    $options = is_array($meta['options'] ?? null) ? $meta['options'] : [];
    $items = $options;
    $raw = [];
    if (!empty($rawAns) && is_array($rawAns)) {
        if (isset($rawAns['selected']) && is_array($rawAns['selected'])) $raw = $rawAns['selected'];
        elseif (isset($rawAns['value']) && is_array($rawAns['value'])) $raw = $rawAns['value'];
        else $raw = $rawAns;
    }

    $isLabelMap = false;
    if (is_array($raw) && count($raw)) {
        foreach ($raw as $k => $v) {
            if (!is_numeric($k) && is_array($v)) { $isLabelMap = true; break; }
        }
    }

    if ($isLabelMap) {
        foreach ($raw as $personLabel => $optList) {
            $personLabel = (string)$personLabel;
            $idKey = $personLabelToId[$personLabel] ?? null;
            if ($idKey === null) {
                // try direct id match
                if (isset($peopleMap[$personLabel])) $idKey = $personLabel;
            }
            if ($idKey === null) continue;
            if (!is_array($optList)) $optList = [$optList];
            foreach ($optList as $opt) {
                $oi = (int)$opt;
                $userSelected[$oi] = $idKey;
            }
        }
    } else {
        // treat raw as per-option map or sequential list
        if (is_array($raw) && count($raw)) {
            // if keys are numeric (possibly non-sequential), map each
            $numericKeyed = true;
            foreach (array_keys($raw) as $k) { if (!is_numeric($k)) { $numericKeyed = false; break; } }
            if ($numericKeyed) {
                foreach ($raw as $k => $v) {
                    $oi = (int)$k;
                    if ($v === null || $v === '') { $userSelected[$oi] = null; continue; }
                    // value may be numeric index, person id, or label
                    if (is_numeric($v) && isset($personIndexToId[(int)$v])) {
                        $userSelected[$oi] = $personIndexToId[(int)$v];
                    } else {
                        $vs = (string)$v;
                        if (isset($peopleMap[$vs])) $userSelected[$oi] = $vs;
                        elseif (isset($personLabelToId[$vs])) $userSelected[$oi] = $personLabelToId[$vs];
                        else $userSelected[$oi] = $vs; // unknown id-like value
                    }
                }
            } else {
                // sequential array (0..n-1)
                $vals = array_values($raw);
                foreach ($vals as $oi => $v) {
                    if ($v === null || $v === '') { $userSelected[$oi] = null; continue; }
                    if (is_numeric($v) && isset($personIndexToId[(int)$v])) {
                        $userSelected[$oi] = $personIndexToId[(int)$v];
                    } else {
                        $vs = (string)$v;
                        if (isset($peopleMap[$vs])) $userSelected[$oi] = $vs;
                        elseif (isset($personLabelToId[$vs])) $userSelected[$oi] = $personLabelToId[$vs];
                        else $userSelected[$oi] = $vs;
                    }
                }
            }
        }
    }

    // Map đáp án đúng: optionIndex => label (A, B, ...)
    $correctByOption = [];
    $answersMeta = $meta['answers'] ?? $meta['correct'] ?? [];
    foreach ($answersMeta as $label => $optList) {
        if (!is_array($optList)) $optList = [$optList];
        foreach ($optList as $opt) {
            $correctByOption[(int)$opt] = $label;
        }
    }
@endphp

<div class="mt-3 text-sm space-y-3">
    {{-- Hiển thị 4 đoạn văn của 4 người (A, B, C, D) --}}
    @php
        $personItems = is_array($meta['items'] ?? null) ? $meta['items'] : [];
    @endphp
    @if(count($personItems) >= 4 && isset($personItems[0]['label']))
        <div class="mb-4">
            <div class="font-medium mb-2">Các đoạn văn của 4 người:</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($personItems as $pi)
                    @if(isset($pi['label']) && isset($pi['text']))
                        <div class="border rounded p-3 bg-gray-50">
                            <div class="font-semibold mb-1">{{ $pi['label'] }}</div>
                            <div style="white-space:pre-line;word-break:break-word;">{{ $pi['text'] }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endif
    <div class="font-medium">Đáp án của bạn (Part 3)</div>

    <div class="space-y-3">
        @foreach($items as $i => $optionText)
            @php
                // Lấy đáp án user chọn (label)
                $sel = $userSelected[$i] ?? null;
                $selectedLabel = null;
                if ($sel !== null) {
                    // Nếu là số thì map sang label
                    if (is_numeric($sel) && isset($labelMap[(int)$sel])) {
                        $selectedLabel = $labelMap[(int)$sel];
                    } elseif (isset($peopleMap[$sel])) {
                        $selectedLabel = $sel;
                    } else {
                        $selectedLabel = (string)$sel;
                    }
                }
                // Lấy đáp án đúng (label)
                $correctLabel = $correctByOption[$i] ?? null;
                $isCorrectItem = ($selectedLabel !== null && $correctLabel !== null && $selectedLabel === $correctLabel);
            @endphp
            <div class="border rounded-lg shadow-sm p-4 flex items-start justify-between gap-4" style="{{ $isCorrectItem ? 'background-color:#ecfdf5;' : ($selectedLabel ? 'background-color:#fff1f2;' : 'background-color:#ffffff;') }}">
                <div class="flex-1">
                    <div class="text-sm text-gray-800 mb-2" style="white-space:pre-line;word-break:break-word;">{{ $i+1 }}. {{ $optionText }}</div>
                        <div class="flex items-center gap-3">
                            <select disabled class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm text-gray-700">
                                <option value="">-</option>
                                @foreach($peopleMap as $pid => $p)
                                    <option value="{{ e($pid) }}" @if($selectedLabel === $pid) selected @endif>{{ e($p['label']) }}</option>
                                @endforeach
                            </select>
                        </div>
                </div>
                <div class="shrink-0 text-right flex flex-col items-end gap-2">
                    @if($isCorrectItem)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
                            <span>Đúng</span>
                        </span>
                    @elseif($selectedLabel)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95A1 1 0 013.636 14.95L8.586 10 3.636 5.05A1 1 0 015.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
                            <span>Sai</span>
                        </span>
                    @endif
                    @if($correctLabel && !$isCorrectItem)
                        <div class="text-xs text-gray-500">Đáp án đúng</div>
                        <div class="mt-1 inline-flex items-center gap-2 px-2 py-1 rounded bg-green-50 text-green-700 text-sm">{{ e($correctLabel) }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
