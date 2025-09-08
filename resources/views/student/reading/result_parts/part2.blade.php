<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 2):</div>
    @if(!empty($chipValues))
        <div class="ml-2 flex flex-col gap-2">
            @foreach($chipValues as $i => $txt)
                @php
                    $itemCorrect = $perItemCorrect[$i] ?? null;
                    $itemPresence = $perItemPresence[$i] ?? null;
                    $itemClass = $chipClass ?? 'bg-indigo-100 text-indigo-800';
                    if ($itemCorrect === true) $itemClass = 'bg-green-100 text-green-800';
                    elseif ($itemPresence === true) $itemClass = 'bg-amber-100 text-amber-800';
                    else $itemClass = 'bg-red-100 text-red-800';
                @endphp
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded {{ $itemClass }} text-sm inline-flex items-center gap-2 flex-1">{{ $txt }}</span>
                    @if($itemCorrect === true)
                        <span class="text-sm text-green-700">Đúng</span>
                    @elseif($itemPresence === true)
                        <span class="text-sm text-amber-700">Sai</span>
                    @else
                        <span class="text-sm text-red-700">Không có</span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="ml-2 text-gray-600">Chưa trả lời</div>
    @endif
</div>
