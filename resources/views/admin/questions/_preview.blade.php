resources/views/admin/questions/_preview.blade.php
@php($q = $q ?? null)

@if(!$q)
    <div class="text-sm text-slate-500">Không có dữ liệu câu hỏi.</div>

@elseif(in_array($q->type, ['dropdown', 'mcq_single']))
    <div class="mt-2 text-sm text-slate-700">
        @if($q->stem)
            <div class="font-medium">{{ $q->stem }}</div>
        @endif
        @if($q->type === 'mcq_single' && $q->audio_url)
            <div class="mt-2">
                <audio controls preload="none" src="{{ $q->audio_url }}" class="w-full"></audio>
            </div>
        @endif
        <ul class="list-disc ml-6 mt-2">
            @foreach($q->options as $opt)
                <li>
                    {{ $opt->label }}
                    @if($opt->is_correct)
                        <span class="text-emerald-700 font-semibold">(đúng)</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>

@elseif($q->type === 'ordering')
    <div class="mt-2 text-sm">
        @if($q->stem)
        <div class="mb-1 font-medium">{{ $q->stem }}</div>@endif
        <div class="font-medium">Items (thứ tự đúng):</div>
        <ol class="list-decimal ml-6">
            @foreach(($q->meta['items'] ?? []) as $it)
                <li>{{ $it }}</li>
            @endforeach
        </ol>
    </div>

@elseif($q->type === 'matching')
    <div class="mt-2 grid md:grid-cols-2 gap-4 text-sm">
        <div>
            <div class="font-medium">Sources (A–D)</div>
            <ol class="list-[upper-alpha] ml-6">
                @foreach(($q->meta['sources'] ?? []) as $s)
                    <li>{{ $s }}</li>
                @endforeach
            </ol>
        </div>
        <div>
            <div class="font-medium">Items (1–7)</div>
            <ol class="list-decimal ml-6">
                @foreach(($q->meta['items'] ?? []) as $idx => $s)
                    @php $ans = $q->meta['answer'][$idx + 1] ?? null; @endphp
                    <li>
                        {{ $s }}
                        @if($ans)
                            <span class="text-emerald-700 font-semibold">→ {{ $ans }}</span>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>

@elseif($q->type === 'heading_matching')
    <div class="mt-2 text-sm">
        <div class="font-medium">Headings (A–G):</div>
        <ol class="list-[upper-alpha] ml-6">
            @foreach(($q->meta['headings'] ?? []) as $h)
                <li>{{ $h }}</li>
            @endforeach
        </ol>

        <div class="font-medium mt-2">Paragraphs (1–8):</div>
        <ol class="list-decimal ml-6">
            @foreach(($q->meta['paragraphs'] ?? []) as $i => $ptext)
                @php $ans = $q->meta['answer'][$i + 1] ?? null; @endphp
                <li>
                    <div class="line-clamp-2">{{ $ptext }}</div>
                    @if($ans)
                        <div class="text-emerald-700 font-semibold">→ {{ $ans }}</div>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>

@else
    <div class="text-sm text-slate-500">Chưa hỗ trợ preview cho type: {{ $q->type }}</div>
@endif