@php
    // --- Chuẩn bị dữ liệu ---
    $labelMap = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    $options = $meta['options'] ?? [];
    $items = $meta['items'] ?? [];
    $answersMeta = $meta['answers'] ?? $meta['correct'] ?? [];

    // Map person label => text (để hiển thị đáp án đúng kèm đoạn văn)
    $personTextMap = [];
    foreach ($items as $p) {
        if (isset($p['label'], $p['text'])) {
            $personTextMap[$p['label']] = $p['text'];
        }
    }

    // --- Chuẩn hóa đáp án user ---
    $userArr = [];
    $rawAns = $ansMeta ?? [];
    if (is_string($rawAns)) {
        $tmp = json_decode($rawAns, true);
        if (is_array($tmp))
            $rawAns = $tmp;
    }
    if (is_array($rawAns)) {
        if (isset($rawAns['selected']) && is_array($rawAns['selected'])) {
            $userArr = array_values($rawAns['selected']);
        } elseif (isset($rawAns['value']) && is_array($rawAns['value'])) {
            $userArr = array_values($rawAns['value']);
        } else {
            $userArr = array_values($rawAns);
        }
    }

    // Normalize user answers -> label
    foreach ($userArr as $i => $v) {
        if ($v === null || $v === '') {
            $userArr[$i] = null;
            continue;
        }
        if (is_numeric($v) && isset($labelMap[(int) $v])) {
            $userArr[$i] = $labelMap[(int) $v];
        } elseif (in_array($v, $labelMap, true)) {
            $userArr[$i] = $v;
        } else {
            $userArr[$i] = (string) $v;
        }
    }

    // --- Chuẩn hóa đáp án đúng ---
    $correctByOption = [];
    if (is_array($answersMeta)) {
        foreach ($answersMeta as $label => $optList) {
            $optList = is_array($optList) ? $optList : [$optList];
            foreach ($optList as $opt) {
                if (is_numeric($opt)) {
                    $correctByOption[(int) $opt] = (string) $label;
                }
            }
        }
    }

    // --- Tính đúng/sai ---
    $perItemCorrect = [];
    $perItemCorrectCount = 0;
    $perItemTotal = count($options);

    for ($i = 0; $i < $perItemTotal; $i++) {
        $selectedLabel = $userArr[$i] ?? null;
        $correctLabel = $correctByOption[$i] ?? null;

        $isCorrect = ($selectedLabel && $correctLabel && $selectedLabel === $correctLabel);
        $perItemCorrect[$i] = $isCorrect;
        if ($isCorrect)
            $perItemCorrectCount++;
    }

@endphp

<div class="mt-3 text-sm space-y-3">
    {{-- Hiển thị các đoạn văn --}}
    @if(count($items))
        <div class="mb-4">
            <div class="font-medium mb-2">Các đoạn văn:</div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($items as $pi)
                    @if(isset($pi['label'], $pi['text']))
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
        @for($i = 0; $i < $perItemTotal; $i++)
            @php
                $optionText = $options[$i] ?? '';
                $selectedLabel = $userArr[$i] ?? null;
                $correctLabel = $correctByOption[$i] ?? null;
                $isCorrect = $perItemCorrect[$i] ?? false;
            @endphp

            <div class="border rounded-lg shadow-sm p-4 flex items-start justify-between gap-4"
                style="{{ $isCorrect ? 'background-color:#ecfdf5;' : ($selectedLabel ? 'background-color:#fff1f2;' : '#ffffff') }}">
                <div class="flex-1">
                    <div class="text-sm text-gray-800 mb-2" style="white-space:pre-line;word-break:break-word;">
                        {{ $i + 1 }}. {{ $optionText }}
                    </div>
                    <div class="flex items-center gap-3">
                        <select disabled
                            class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm text-gray-700">
                            <option value="">-</option>
                            @foreach($labelMap as $pid)
                                <option value="{{ $pid }}" @if($selectedLabel === $pid) selected @endif>
                                    {{ $pid }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="shrink-0 text-right flex flex-col items-end gap-2">
                    @if($isCorrect)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm">
                            ✔️ Đúng
                        </span>
                    @elseif($selectedLabel)
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm">
                            ❌ Sai
                        </span>
                    @endif

                    @if($correctLabel && !$isCorrect)
                        <div class="text-xs text-gray-500">Đáp án đúng</div>
                        <div class="mt-1 inline-flex items-center gap-2 px-2 py-1 rounded bg-green-50 text-green-700 text-sm">
                            {{ $correctLabel }}
                        </div>
                    @endif

                </div>
            </div>
        @endfor
    </div>
</div>

{{-- DEBUG --}}
<pre style="background:#222;color:#fff;padding:8px;font-size:12px;overflow:auto;max-width:100vw;">
Correct: {{ $perItemCorrectCount }}/{{ $perItemTotal }}
UserArr: {{ json_encode($userArr) }}
CorrectMap: {{ json_encode($correctByOption) }}
</pre>