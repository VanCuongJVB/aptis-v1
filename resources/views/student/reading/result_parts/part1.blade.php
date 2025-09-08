<div class="mt-3 text-sm">
    <div class="font-medium">Đáp án của bạn (Part 1):</div>
    @php
        // Part1: often an array of selected words
        $values = [];
        if (!empty($ansMeta)) {
            if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $values = $ansMeta['selected'];
            elseif (is_array($ansMeta)) $values = $ansMeta;
        }
    @endphp
    @if(!empty($values))
        <div class="ml-2 flex flex-wrap gap-2">
            @foreach($values as $v)
                <span class="px-2 py-1 rounded {{ $chipClass ?? 'bg-blue-100 text-blue-800' }} text-sm">{{ $v }}</span>
            @endforeach
        </div>
    @else
        <div class="ml-2 text-gray-600">Chưa trả lời</div>
    @endif
</div>
