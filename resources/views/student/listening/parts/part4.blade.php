<div class="w-full max-w-3xl mx-auto p-4 question-block mb-6"
     data-qid="{{ $question->id }}"
     data-part="{{ $question->part ?? 4 }}"
     data-metadata='@json($question->metadata)'>

    @php
        // ==== Meta & description / passage ====
        $meta = $question->metadata ?? [];
        $pairQuestions = $meta['questions'] ?? null;

        // Ưu tiên mô tả tĩnh (description/note/instructions), fallback sang passage của sub-question đầu (nếu có)
        $pairDesc = $meta['description'] ?? $meta['note'] ?? $meta['instructions'] ?? null;
        $pairPassage = (is_array($pairQuestions) && !empty($pairQuestions[0]['text'])) ? $pairQuestions[0]['text'] : null;
        $descContent = $pairDesc ?? $pairPassage;

        // ==== Saved answers normalize ====
        $answerMeta = $answer->metadata ?? null;
        $savedSelected = [];
        if (is_array($answerMeta)) {
            // có thể là ['selected' => [0,1]] hoặc ['selected' => 2]
            if (array_key_exists('selected', $answerMeta)) {
                $savedSelected = is_array($answerMeta['selected'])
                    ? array_values($answerMeta['selected'])
                    : [ $answerMeta['selected'] ];
            }
        } elseif (is_string($answerMeta)) {
            $dec = json_decode($answerMeta, true);
            if (is_array($dec) && array_key_exists('selected', $dec)) {
                $savedSelected = is_array($dec['selected'])
                    ? array_values($dec['selected'])
                    : [ $dec['selected'] ];
            }
        }

        // Helper lấy selected tại index (an toàn kiểu)
        $getSelectedAt = function ($arr, $idx) {
            return isset($arr[$idx]) ? (string)$arr[$idx] : null;
        };
    @endphp

    {{-- Audio area --}}
    @if(!empty($audioUrl))
        <div class="mb-4">
            <audio controls preload="metadata" class="w-full" crossorigin="anonymous" playsinline webkit-playsinline>
                <source src="{{ $audioUrl }}" type="audio/mpeg">
                <source src="{{ $audioUrl }}" type="audio/mp3">
                Trình duyệt của bạn không hỗ trợ audio.
            </audio>
        </div>
    @endif
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-800">Câu hỏi {{ $question->order_no }}</h2>
        
        @if(!empty($question->stem))
            <p class="text-gray-800 font-medium">{{ $question->stem }}</p>
        @endif

        @if(!empty($question->content))
            <p class="text-gray-700 mt-1">{{ $question->content }}</p>
        @endif

        @if($descContent)
            <a href="#"
               id="desc-toggle-{{ $question->id }}"
               data-qid="{{ $question->id }}"
               class="js-desc-toggle text-blue-600 underline text-xs ml-2"
               data-label-show="Hiển thị mô tả"
               data-label-hide="Ẩn mô tả">Hiển thị mô tả</a>

            <div id="desc-box-{{ $question->id }}"
                 class="desc-box text-gray-600 text-sm mb-2 hidden mt-1 bg-yellow-50 border border-yellow-200 rounded p-2">
                {!! nl2br(e($descContent)) !!}
            </div>
        @endif
    </div>

    @if($pairQuestions && is_array($pairQuestions))
        {{-- Pair/Group: mỗi sub-question render khối riêng, share passage (nếu có) --}}
        <form class="space-y-3">
            {{-- passage is rendered inside the description toggle (if present) to avoid duplication --}}

            @foreach($pairQuestions as $pIdx => $sub)
                @php
                    $subOptions = $sub['options'] ?? [];
                    $subStem    = $sub['stem'] ?? ($sub['title'] ?? null);
                    $sel        = $getSelectedAt($savedSelected, $pIdx);
                @endphp

                <div class="mb-2 border rounded-lg p-3">
                    @if($subStem)
                        <div class="font-medium text-gray-800 mb-2">{!! $subStem !!}</div>
                    @endif

                    @foreach($subOptions as $idx => $option)
                        @php
                            $inputId = "q{$question->id}_{$pIdx}_opt{$idx}";
                        @endphp
                        <label for="{{ $inputId }}" class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer mb-1">
                            <input id="{{ $inputId }}"
                                   type="radio"
                                   name="selected[{{ $pIdx }}]"
                                   value="{{ $idx }}"
                                   class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                   @checked($sel !== null && $sel === (string)$idx)>
                            <span class="text-gray-800">{!! $option !!}</span>
                        </label>
                    @endforeach
                </div>
            @endforeach
        </form>
    @else
        {{-- Legacy: single-question radio list --}}
        @php
            $legacyOptions = $meta['options'] ?? [];
            $sel = $getSelectedAt($savedSelected, 0);
        @endphp

        <form class="space-y-3">
            @foreach($legacyOptions as $idx => $option)
                @php
                    $inputId = "q{$question->id}_opt{$idx}";
                @endphp
                <label for="{{ $inputId }}" class="flex items-start gap-3 p-3 border rounded-lg hover:bg-gray-50 cursor-pointer mb-2">
                    <input id="{{ $inputId }}"
                           type="radio"
                           name="selected"
                           value="{{ $idx }}"
                           class="mt-1 h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                           @checked($sel !== null && $sel === (string)$idx)>
                    <span class="text-gray-800">{!! $option !!}</span>
                </label>
            @endforeach
        </form>
    @endif

    <div id="feedback-{{ $question->id }}" class="mt-4 hidden"></div>
    <div class="inline-feedback mt-3 text-sm text-gray-700" data-qid-feedback="{{ $question->id }}"></div>
</div>

{{-- Toggle mô tả: gắn 1 lần cho toàn trang, chống double-binding --}}
<script>
(function () {
    if (window.__descToggleBound) return;
    window.__descToggleBound = true;

    document.addEventListener('click', function (e) {
        const btn = e.target.closest && e.target.closest('.js-desc-toggle');
        if (!btn) return;

        e.preventDefault();
        const qid = btn.getAttribute('data-qid') || (btn.id || '').replace('desc-toggle-', '');
        const box = document.getElementById('desc-box-' + qid);
        if (!box) return;

        const showLabel = btn.getAttribute('data-label-show') || 'Hiển thị mô tả';
        const hideLabel = btn.getAttribute('data-label-hide') || 'Ẩn mô tả';

        const willShow = box.classList.contains('hidden');
        box.classList.toggle('hidden', !willShow);
        btn.textContent = willShow ? hideLabel : showLabel;
    }, false);
})();
</script>
