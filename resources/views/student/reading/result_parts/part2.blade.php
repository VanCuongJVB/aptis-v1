<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 2)</div>
    @php
        // user values (texts placed into positions), prepared by parent view
        $userVals = $chipValues ?? [];
        // canonical sentence list (may be in 'sentences' or 'paragraphs')
        $sentences = is_array($meta['sentences'] ?? null) ? $meta['sentences'] : (is_array($meta['paragraphs'] ?? null) ? $meta['paragraphs'] : []);

        // Build correct texts for each position.
        // Prefer explicit `correct_order` (indices into sentences), then `order`.
        $correctTexts = [];
        $orderIndices = [];
        if (!empty($meta['correct_order']) && is_array($meta['correct_order'])) {
            $orderIndices = $meta['correct_order'];
        } elseif (!empty($meta['order']) && is_array($meta['order'])) {
            $orderIndices = $meta['order'];
        }

        if (!empty($orderIndices)) {
            // map each index to the canonical sentence text when available
            foreach ($orderIndices as $idx) {
                if (is_numeric($idx) && isset($sentences[(int)$idx])) {
                    $correctTexts[] = $sentences[(int)$idx];
                } elseif (isset($meta['answers'][$idx])) {
                    $correctTexts[] = $meta['answers'][$idx];
                } else {
                    $correctTexts[] = '';
                }
            }
        } elseif (!empty($meta['answers']) && is_array($meta['answers'])) {
            $correctTexts = array_values($meta['answers']);
        } elseif (!empty($sentences)) {
            $correctTexts = array_values($sentences);
        }

        $count = max(count($correctTexts), count($userVals));
    @endphp

    @if($count === 0)
        <div class="ml-2 text-gray-600">Chưa có dữ liệu</div>
    @else
        <div class="mt-2 space-y-3">
            @for($i = 0; $i < $count; $i++)
                @php
                    $u = trim((string)($userVals[$i] ?? ''));
                    $c = trim((string)($correctTexts[$i] ?? ''));
                    $isCorrect = ($u !== '' && $c !== '' && $u === $c);
                    $leftClass = $isCorrect ? 'bg-green-50 text-green-800' : ($u === '' ? 'bg-gray-50 text-gray-500' : 'bg-red-50 text-red-800');
                @endphp

                <div class="grid grid-cols-2 gap-4 items-start bg-white border rounded p-3">
                    <div>
                        <div class="text-xs text-gray-500">Bạn (Vị trí {{ $i + 1 }})</div>
                        <div class="mt-1 px-3 py-2 rounded {{ $leftClass }} text-sm">{{ $u === '' ? 'Chưa trả lời' : e($u) }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500">Đáp án đúng (Vị trí {{ $i + 1 }})</div>
                        <div class="mt-1 px-3 py-2 rounded bg-white border text-sm text-gray-700">{{ $c === '' ? '(không có)' : e($c) }}</div>
                    </div>
                </div>
            @endfor
        </div>
    @endif
</div>
