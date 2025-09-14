@php
    // Defensive normalization: ansMeta may be sequential array or contain 'selected'
    $userSelected = [];
    if (!empty($ansMeta) && is_array($ansMeta)) {
        if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userSelected = array_values($ansMeta['selected']);
        else $userSelected = array_values($ansMeta);
    }
    // people (choices) are usually in question metadata
    $people = is_array($meta['people'] ?? null) ? $meta['people'] : [];
    $peopleMap = [];
    foreach ($people as $p) {
        $id = $p['id'] ?? ($p['key'] ?? null);
        if ($id === null) continue;
        $peopleMap[(string)$id] = $p;
    }
    $items = is_array($meta['items'] ?? null) ? $meta['items'] : [];
@endphp

<div class="mt-3 text-sm space-y-3">
    <div class="font-medium">Đáp án của bạn (Part 3)</div>

    @if(empty($items))
        {{-- Fallback: if no items in metadata, show raw values as chips --}}
        @if(!empty($userSelected))
            <div class="ml-2 flex flex-wrap gap-2">
                @foreach($userSelected as $k => $v)
                    <div class="px-2 py-1 rounded {{ $chipClass ?? 'bg-yellow-100 text-yellow-800' }} text-sm">{{ is_array($v) ? json_encode($v) : $v }}</div>
                @endforeach
            </div>
        @else
            <div class="ml-2 text-gray-600">Chưa trả lời</div>
        @endif
    @else
        <div class="space-y-3">
            @foreach($items as $i => $it)
                    @php
                    $prompt = is_array($it) ? ($it['prompt'] ?? $it['text'] ?? '') : $it;
                    $sel = $userSelected[$i] ?? null;
                    $selectedLabel = null;
                    if ($sel !== null) {
                        $s = (string)$sel;
                        if (isset($peopleMap[$s])) {
                            $p = $peopleMap[$s];
                            $selectedLabel = trim(($p['id'] ?? $s) . '. ' . ($p['name'] ?? strip_tags($p['text'] ?? '')));
                        } else {
                            $selectedLabel = (string)$sel;
                        }
                    }

                    // compute correct id/text (best-effort)
                    $correctId = null;
                    $correctLabel = null;
                    if (!empty($meta['answers']) && isset($meta['answers'][$i])) {
                        $ansRaw = $meta['answers'][$i];
                        if (is_array($ansRaw)) {
                            if (isset($ansRaw['option_id'])) $correctId = (string)$ansRaw['option_id'];
                            elseif (isset($ansRaw['id'])) $correctId = (string)$ansRaw['id'];
                            elseif (isset($ansRaw['value'])) $correctId = (string)$ansRaw['value'];
                            if ($correctId !== null && isset($peopleMap[$correctId])) {
                                $p = $peopleMap[$correctId];
                                $correctLabel = trim(($p['id'] ?? $correctId) . '. ' . ($p['name'] ?? strip_tags($p['text'] ?? '')));
                            } else {
                                $correctLabel = is_array($ansRaw) ? json_encode($ansRaw) : (string)$ansRaw;
                            }
                        } else {
                            // primitive (e.g. 'A' or 'B') — treat as both label and id
                            $correctLabel = (string)$ansRaw;
                            $correctId = (string)$ansRaw;
                        }
                    }

                    $isCorrectItem = null;
                    if ($correctId !== null && $sel !== null) {
                        $isCorrectItem = ((string)$sel === (string)$correctId);
                    }
                @endphp

                @php
                    // container classes and inline styles depend on correctness
                    $containerBase = 'border rounded-lg shadow-sm p-4 flex items-start justify-between gap-4';
                    $containerStyle = '';
                    // light green for correct, light red for incorrect, white for unknown
                    if ($isCorrectItem === true) {
                        $containerStyle = 'background-color:#ecfdf5;';
                    } elseif ($isCorrectItem === false) {
                        $containerStyle = 'background-color:#fff1f2;';
                    } else {
                        $containerStyle = 'background-color:#ffffff;';
                    }
                @endphp
                <div class="{{ $containerBase }}" style="{{ $containerStyle }}">
                    <div class="flex-1">
                        <div class="text-sm text-gray-800 mb-2">{{ $i+1 }}. {{ e($prompt) }}</div>
                        <div class="flex items-center gap-3">
                            <select disabled class="w-full bg-gray-50 border border-gray-200 rounded-lg p-2 text-sm text-gray-700">
                                <option value="">-</option>
                                @foreach($peopleMap as $pid => $p)
                                    @php $label = trim(($p['id'] ?? $pid) . '. ' . ($p['name'] ?? strip_tags($p['text'] ?? ''))); @endphp
                                    <option value="{{ e($pid) }}" @if((string)$sel === (string)$pid) selected @endif>{{ e($label) }}</option>
                                @endforeach
                            </select>
                            <div class="shrink-0">
                                @if($selectedLabel)
                                    @if($isCorrectItem === true)
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm">{{ e($selectedLabel) }}</span>
                                    @elseif($isCorrectItem === false)
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm">{{ e($selectedLabel) }}</span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-sm">{{ e($selectedLabel) }}</span>
                                    @endif
                                @else
                                    <span class="text-sm text-gray-400 italic">Chưa trả lời</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="shrink-0 text-right flex flex-col items-end gap-2">
                        @if($isCorrectItem === true)
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-green-50 text-green-700 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
                                <span>Đúng</span>
                            </span>
                        @elseif($isCorrectItem === false)
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-50 text-red-700 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95A1 1 0 013.636 14.95L8.586 10 3.636 5.05A1 1 0 015.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
                                <span>Sai</span>
                            </span>
                        @endif

                        @if(!empty($correctLabel) && $isCorrectItem !== true)
                            <div class="text-xs text-gray-500">Đáp án đúng</div>
                            <div class="mt-1 inline-flex items-center gap-2 px-2 py-1 rounded bg-green-50 text-green-700 text-sm">{{ e($correctLabel) }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
