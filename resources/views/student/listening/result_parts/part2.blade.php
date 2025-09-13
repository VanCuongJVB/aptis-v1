@php
    $meta = $question->metadata ?? [];
    $ansMeta = $answer && isset($answer->metadata) ? $answer->metadata : null;

    // extract user mapping/order
    $selectedOrder = [];
    if (is_array($ansMeta)) {
        if (isset($ansMeta['selected']['order'])) $selectedOrder = $ansMeta['selected']['order'];
        elseif (isset($ansMeta['order'])) $selectedOrder = $ansMeta['order'];
        elseif (isset($ansMeta['selected'])) $selectedOrder = $ansMeta['selected'];
        else $selectedOrder = $ansMeta;
    } elseif (is_string($ansMeta)) {
        $dec = json_decode($ansMeta, true);
        if (is_array($dec)) $selectedOrder = $dec;
    }
    $selectedOrder = is_array($selectedOrder) ? array_values($selectedOrder) : [];

    $sentences = is_array($meta['sentences'] ?? null) ? $meta['sentences'] : (is_array($meta['items'] ?? null) ? $meta['items'] : []);
    $correctOrder = $meta['correct_order'] ?? $meta['correct'] ?? ($meta['order'] ?? []);

    $count = max(count($selectedOrder), count($correctOrder), count($sentences));
@endphp

<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 2)</div>

    @if($count === 0)
        <div class="ml-2 text-gray-600">Chưa có dữ liệu</div>
    @else
        <div class="mt-2 space-y-3">
            @for($i = 0; $i < $count; $i++)
                @php
                    $userIdx = $selectedOrder[$i] ?? null;
                    $corrIdx = $correctOrder[$i] ?? null;
                    $userText = $userIdx !== null && isset($sentences[$userIdx]) ? $sentences[$userIdx] : ($userIdx !== null ? (string)$userIdx : '');
                    $corrText = $corrIdx !== null && isset($sentences[$corrIdx]) ? $sentences[$corrIdx] : ($corrIdx !== null ? (string)$corrIdx : '');
                    $isCorrect = ($userText !== '' && $corrText !== '' && mb_strtolower(trim($userText)) === mb_strtolower(trim($corrText)));
                @endphp

                <div class="grid grid-cols-2 gap-4 items-start bg-white border rounded p-3">
                    <div>
                        <div class="text-xs text-gray-500">Bạn (Vị trí {{ $i + 1 }})</div>
                        <div class="mt-1 px-3 py-2 rounded {{ $isCorrect ? 'bg-green-50 text-green-800' : ($userText === '' ? 'bg-gray-50 text-gray-500' : 'bg-red-50 text-red-800') }} text-sm">{{ $userText === '' ? 'Chưa trả lời' : e($userText) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Đáp án đúng (Vị trí {{ $i + 1 }})</div>
                        <div class="mt-1 px-3 py-2 rounded bg-white border text-sm text-gray-700">{{ $corrText === '' ? '(không có)' : e($corrText) }}</div>
                    </div>
                </div>
            @endfor
        </div>
    @endif
</div>
