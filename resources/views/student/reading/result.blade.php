@extends('layouts.app')

@section('title', 'Kết quả Reading')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">{{ $quiz->title }} — Kết quả</h2>
                <p class="text-sm text-gray-500">Part {{ $quiz->part }}</p>
            </div>
            <div class="text-right">
                <div class="text-lg font-bold">{{ $attempt->score_percentage ?? 0 }}%</div>
                <div class="text-sm text-gray-600">{{ $attempt->correct_answers ?? 0 }} / {{ $attempt->total_questions ?? count($questions) }}</div>
            </div>
        </div>

        {{-- <div class="mb-4 grid grid-cols-3 gap-4 text-sm text-gray-700">
            <div>Thời gian bắt đầu: {{ $attempt->started_at ? $attempt->started_at->format('H:i d/m/Y') : '-' }}</div>
            <div>Thời gian nộp: {{ $attempt->submitted_at ? $attempt->submitted_at->format('H:i d/m/Y') : '-' }}</div>
            <div>Thời lượng (phút): {{ $duration ?? '-' }}</div>
        </div> --}}

        <hr class="my-4">
    {{-- include helper so result page can render inline-feedback exactly like the practice view --}}
    @includeIf('student.reading.parts._check_helper')

        @foreach($questions as $idx => $question)
            @php
                $num = $idx + 1;
                $ans = $answers->get($question->id) ?? null;
                $isCorrect = $ans && isset($ans->is_correct) ? $ans->is_correct : null;

                // Normalize question metadata (could be JSON string or array/object)
                $meta = null;
                if (is_string($question->metadata) && !empty($question->metadata)) {
                    $meta = json_decode($question->metadata, true) ?: null;
                } elseif (is_array($question->metadata) || is_object($question->metadata)) {
                    $meta = (array) $question->metadata;
                }

                // Try to extract authoritative answers from metadata
                $authOptionIds = [];
                $authTexts = [];
                if (!empty($meta)) {
                    if (!empty($meta['correct_answers']) && is_array($meta['correct_answers'])) {
                        foreach ($meta['correct_answers'] as $a) {
                            if (is_array($a)) {
                                if (isset($a['option_id'])) $authOptionIds[] = $a['option_id'];
                                if (isset($a['text'])) $authTexts[] = $a['text'];
                            } else {
                                if (is_numeric($a)) $authOptionIds[] = (int) $a;
                                else $authTexts[] = (string) $a;
                            }
                        }
                    } elseif (!empty($meta['answers']) && is_array($meta['answers'])) {
                        foreach ($meta['answers'] as $a) {
                            if (is_array($a)) {
                                if (isset($a['option_id'])) $authOptionIds[] = $a['option_id'];
                                if (isset($a['text'])) $authTexts[] = $a['text'];
                            } else {
                                // primitive value (could be option id or text)
                                if (is_numeric($a)) $authOptionIds[] = (int) $a;
                                else $authTexts[] = (string) $a;
                            }
                        }
                    }
                    // some metadata use 'correct' or 'key'
                    if (!empty($meta['key']) && is_array($meta['key'])) {
                        foreach ($meta['key'] as $k) {
                            if (is_numeric($k)) $authOptionIds[] = (int)$k; else $authTexts[] = (string)$k;
                        }
                    }
                }

                $selectedOptionId = $ans->selected_option_id ?? null;
                $selectedOptionText = null;
                if ($ans && $ans->selectedOption) {
                    $selectedOptionText = $ans->selectedOption->text ?? $ans->selectedOption->content ?? $ans->selectedOption->label ?? $ans->selectedOption->title ?? null;
                }

                // Normalize answer metadata (accept JSON string or array/object)
                $ansMeta = null;
                if ($ans && isset($ans->metadata)) {
                    if (is_string($ans->metadata) && !empty($ans->metadata)) {
                        $ansMeta = json_decode($ans->metadata, true) ?: null;
                    } elseif (is_array($ans->metadata) || is_object($ans->metadata)) {
                        $ansMeta = (array) $ans->metadata;
                    }
                }

                // If answer metadata is sequential (e.g. selected: [..]) compute per-item correctness.
                $isMultiSelected = false;
                $perItemCorrect = [];
                $perItemPresence = [];
                $perItemTotal = 0;
                $perItemCorrectCount = 0;
                $perItemPresenceCount = 0;
                $chipValues = [];

                // helper: normalize a raw value into array of ints/strings
                $normalizeOrder = function($raw) {
                    if ($raw === null) return null;
                    if (is_array($raw)) return array_values($raw);
                    if (is_string($raw)) {
                        $s = trim($raw);
                        // try json
                        $decoded = json_decode($s, true);
                        if (is_array($decoded)) return array_values($decoded);
                        // strip surrounding [] and split by comma
                        $str = trim($s, "[] \t\n\r");
                        if (strpos($str, ',') !== false) {
                            $parts = array_map('trim', explode(',', $str));
                            return $parts;
                        }
                        // single value
                        return [$s];
                    }
                    return null;
                };

                $partNum = $quiz->part ?? $question->part ?? ($meta['part'] ?? null);
                // Part 4 is composed of multiple paragraph-select items — treat as multi-selected
                // so the header displays per-item counts instead of a single correctness badge.
                if ($partNum == 4) {
                    $isMultiSelected = true;
                }
                if ($partNum == 2 && $ansMeta && is_array($ansMeta)) {
                    $raw = null;
                    if (isset($ansMeta['selected']['order'])) $raw = $ansMeta['selected']['order'];
                    elseif (isset($ansMeta['order'])) $raw = $ansMeta['order'];
                    elseif (isset($ansMeta['selected'])) $raw = $ansMeta['selected'];

                    $selectedOrder = $normalizeOrder($raw);
                    // try to get authoritative order
                    $correctOrder = null;
                    if (!empty($meta['correct_order'])) $correctOrder = $normalizeOrder($meta['correct_order']);
                    elseif (!empty($meta['correct'])) $correctOrder = $normalizeOrder($meta['correct']);
                    elseif (!empty($meta['answers'])) $correctOrder = $normalizeOrder($meta['answers']);

                    if (is_array($selectedOrder) && count($selectedOrder) > 0) {
                        $isMultiSelected = true;
                        $perItemTotal = count($selectedOrder);
                        // map to sentence texts if available
                        $sentences = $meta['sentences'] ?? [];
                        foreach ($selectedOrder as $i => $val) {
                            // determine user text: if val is numeric -> index into sentences, else treat as text
                            $userText = null;
                            if (is_numeric($val)) {
                                $uIdx = (int)$val;
                                $userText = $sentences[$uIdx] ?? ('#' . $uIdx);
                            } else {
                                $userText = (string)$val;
                            }
                            $chipValues[] = $userText;

                            // determine expected text for this position
                            $expectedText = null;
                            if (is_array($correctOrder) && isset($correctOrder[$i])) {
                                $c = $correctOrder[$i];
                                if (is_numeric($c)) {
                                    $cIdx = (int)$c;
                                    $expectedText = $sentences[$cIdx] ?? ('#' . $cIdx);
                                } else {
                                    $expectedText = (string)$c;
                                }
                            } elseif (!empty($authTexts) && isset($authTexts[$i])) {
                                $expectedText = (string)$authTexts[$i];
                            }

                            $correct = false;
                            if ($expectedText !== null) {
                                // normalize and compare
                                $u = mb_strtolower(trim((string)$userText));
                                $e = mb_strtolower(trim((string)$expectedText));
                                $correct = ($u === $e);
                            }
                                $perItemCorrect[] = $correct === true;
                                if ($correct === true) $perItemCorrectCount++;
                                // presence check: userText exists anywhere in sentences (loose match)
                                $presence = false;
                                if (!empty($sentences)) {
                                    $lowUser = mb_strtolower(trim((string)$userText));
                                    foreach ($sentences as $s) {
                                        if ($lowUser === mb_strtolower(trim((string)$s))) { $presence = true; break; }
                                    }
                                }
                                if (!$presence && !empty($authTexts)) {
                                    $lowUser = mb_strtolower(trim((string)$userText));
                                    foreach ($authTexts as $at) { if ($lowUser === mb_strtolower(trim((string)$at))) { $presence = true; break; } }
                                }
                                $perItemPresence[] = $presence;
                                if ($presence) $perItemPresenceCount = ($perItemPresenceCount ?? 0) + 1;
                        }
                    }
                } else {
                    // generic case
                    // Special-case Part 4: multi-paragraph selects
                    if ($partNum == 4) {
                        $options = $meta['options'] ?? [];
                        $paragraphs = $meta['paragraphs'] ?? [];
                        $correctArr = $meta['correct'] ?? $meta['answers'] ?? [];

                        // normalize user array from answer metadata
                        $userArr = [];
                        if (is_array($ansMeta)) {
                            if (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) $userArr = array_values($ansMeta['selected']);
                            elseif (isset($ansMeta['value']) && is_array($ansMeta['value'])) $userArr = array_values($ansMeta['value']);
                            else {
                                $maybe = array_values($ansMeta);
                                if (count($maybe) === 1 && is_array($maybe[0])) $userArr = array_values($maybe[0]);
                                else $userArr = $maybe;
                            }
                        } elseif (is_string($ansMeta)) {
                            $dec = json_decode($ansMeta, true);
                            if (is_array($dec)) $userArr = array_values($dec);
                        }

                        $perItemTotal = max(count($paragraphs), count($userArr), count($correctArr));
                        // Treat Part 4 as multi-selected when we have per-item data so header
                        // shows per-item correct/total instead of a single correct/sai badge.
                        if ($perItemTotal > 0) {
                            $isMultiSelected = true;
                        }
                        for ($i = 0; $i < $perItemTotal; $i++) {
                            $raw = $userArr[$i] ?? null;
                            $userText = '';
                            if ($raw !== null && trim((string)$raw) !== '') {
                                if (is_numeric($raw) && isset($options[(int)$raw])) $userText = $options[(int)$raw];
                                else $userText = (string)$raw;
                            }

                            $corrRaw = $correctArr[$i] ?? null;
                            $corrText = '';
                            if ($corrRaw !== null && trim((string)$corrRaw) !== '') {
                                if (is_numeric($corrRaw) && isset($options[(int)$corrRaw])) $corrText = $options[(int)$corrRaw];
                                else $corrText = (string)$corrRaw;
                            }

                            $ok = false;
                            if ($userText !== '' && $corrText !== '') {
                                $ok = mb_strtolower(trim((string)$userText)) === mb_strtolower(trim((string)$corrText));
                            }
                            $perItemCorrect[] = $ok;
                            if ($ok) $perItemCorrectCount++;
                            // presence: check if userText exists among options
                            $presence = false;
                            if ($userText !== '') {
                                foreach ($options as $opt) {
                                    if (mb_strtolower(trim((string)$opt)) === mb_strtolower(trim((string)$userText))) { $presence = true; break; }
                                }
                            }
                            $perItemPresence[] = $presence;
                            if ($presence) $perItemPresenceCount = ($perItemPresenceCount ?? 0) + 1;
                        }
                    } elseif ($ans && isset($ans->metadata) && is_array($ans->metadata)) {
                        $mdTop = $ans->metadata;
                        // prefer md.selected if present
                        if (isset($mdTop['selected']) && is_array($mdTop['selected'])) {
                            $vals = array_values($mdTop['selected']);
                            $isSeq = $vals === $mdTop['selected'];
                            if ($isSeq) {
                                $isMultiSelected = true;
                                $perItemTotal = count($vals);
                                for ($i = 0; $i < $perItemTotal; $i++) {
                                    $v = (string) ($vals[$i] ?? '');
                                    $auth = $authTexts[$i] ?? null;
                                    $correct = false;
                                    if ($auth !== null) {
                                        $correct = mb_strtolower(trim((string)$auth)) === mb_strtolower(trim($v));
                                    } else {
                                        // fallback: if value exists in authTexts anywhere treat as correct
                                        $correct = in_array($v, $authTexts, true);
                                    }
                                    $perItemCorrect[] = $correct;
                                    if ($correct) $perItemCorrectCount++;
                                }
                                // for generic sequential, set chipValues to the sequential values
                                $chipValues = $vals;
                            }
                        }
                    }
                }

                // per-part color/style map
                $partClass = match($quiz->part ?? null) {
                    1 => 'border-gray-200 bg-white',
                    2 => 'border-gray-200 bg-white',
                    3 => 'border-gray-200 bg-white',
                    4 => 'border-gray-200 bg-white',
                    default => 'border-gray-200 bg-white',
                };
                $chipClass = match($quiz->part ?? null) {
                    1 => 'border-gray-200 bg-white',
                    2 => 'border-gray-200 bg-white',
                    3 => 'border-gray-200 bg-white',
                    4 => 'border-gray-200 bg-white',
                    default => 'border-gray-200 bg-white',
                };
            @endphp

            <div class="mb-4 p-4 border rounded {{ $partClass }} question-block" data-qid="{{ $question->id }}">
                <div class="flex items-start justify-between">
                    <div class="font-semibold flex items-center gap-2">
                        <span class="text-sm font-medium">Câu {{ $num }}</span>
                        @if($isMultiSelected || ($perItemTotal ?? 0) > 0)
                            <span class="inline-flex items-center gap-2 px-2 py-0.5 rounded bg-gray-50 text-gray-800 text-xs">
                                <strong class="text-sm">{{ $perItemCorrectCount }}</strong>
                                /
                                <span class="text-sm">{{ $perItemTotal }}</span>
                                <span class="text-xs text-gray-500">đúng</span>
                            </span>
                        @else
                            @if($isCorrect === true)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
                                    <span>Đúng</span>
                                </span>
                            @elseif($isCorrect === false)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95A1 1 0 013.636 14.95L8.586 10 3.636 5.05A1 1 0 015.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
                                    <span>Sai</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-50 text-gray-800 text-xs">?
                                    <span class="sr-only">Không xác định</span>
                                </span>
                            @endif
                        @endif
                    </div>
                    <div class="text-sm text-gray-600">&nbsp;</div>
                </div>

                <div class="prose mt-2">{!! $question->content ?? $question->title !!}</div>
                <div data-meta-json='@json($meta)' style="display:none"></div>

                @php $partToInclude = $partNum ?? ($quiz->part ?? 1); @endphp
                @includeIf('student.reading.result_parts.part' . $partToInclude)
            </div>
        @endforeach

        <div class="flex justify-between mt-6">
            <a href="{{ route('student.reading.sets.index') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Quay lại bộ đề</a>
            {{-- <a href="{{ route('student.attempts.history') }}" class="btn">Lịch sử</a> --}}
        </div>
    </div>
</div>
@endsection

    @push('scripts')
    <script>
        (function(){
            try {
                var key = 'attempt_answers_{{ $attempt->id }}';
                var raw = null;
                try { raw = localStorage.getItem(key); } catch(e) { raw = null; }
                var answers = null;
                if (raw) {
                    try { answers = JSON.parse(raw); } catch(e) { answers = null; }
                }
                
                if (!answers && typeof window.attemptAnswers !== 'undefined') answers = window.attemptAnswers;

                // debug toggle removed

                // helper: try to derive payload from DOM when persisted answers are not available
                function derivePayloadFromDom(qEl) {
                    if (!qEl) return null;

                    // Part2: hidden inputs or slot state
                    var inOrder = qEl.querySelector('#part2_order, input[name="part2_order"]');
                    var inTexts = qEl.querySelector('#part2_selected_texts, input[name="part2_selected_texts"]');
                    if (inOrder && (inOrder.value || (inTexts && inTexts.value))) {
                        var raw = inOrder.value || '';
                        var order = null;
                        try { order = JSON.parse(raw); } catch(e) {
                            if (raw) order = raw.split(',').map(function(s){ return s.trim(); });
                        }
                        var texts = null;
                        if (inTexts && inTexts.value) {
                            try { texts = JSON.parse(inTexts.value); } catch(e) { texts = inTexts.value.split(',').map(function(s){ return s.trim(); }); }
                        }
                        var p = { part: 'part2', order: order, texts: texts };
                        return p;
                    }

                    // slot-based fallback (if hidden inputs absent)
                    var slots = qEl.querySelectorAll('.slot');
                        if (slots && slots.length) {
                        var order = [];
                        var texts = [];
                        slots.forEach(function(s){
                            var it = s.querySelector('.draggable-item');
                            if (it) { order.push(it.dataset.index); texts.push(it.innerText.trim()); }
                            else order.push(null);
                        });
                        // if any non-null entry, return as part2
                            if (order.some(function(v){ return v !== null && typeof v !== 'undefined'; })) {
                            var p = { part: 'part2', order: order, texts: texts };
                            return p;
                        }
                    }

                    // choices
                    var checked = qEl.querySelectorAll('input[type=radio]:checked, input[type=checkbox]:checked');
                    if (checked && checked.length) return { part: 'choice', value: Array.from(checked).map(function(i){ return i.value; }) };

                    // select
                    var sel = qEl.querySelector('select'); if (sel) return { part: 'select', value: sel.value };
                    // textarea
                    var ta = qEl.querySelector('textarea'); if (ta) return { part: 'text', value: ta.value };

                    return null;
                }

                // Collect qids from the DOM so we can render all feedback even when storage is empty
                
                var qEls = Array.from(document.querySelectorAll('.question-block[data-qid]'));
                
                qEls.forEach(function(qEl){
                    try {
                        var qid = qEl.getAttribute('data-qid');
                        var saved = (answers && answers[qid]) ? answers[qid] : null;

                        // if no saved data, try to derive from DOM
                        if (!saved) {
                            var derived = derivePayloadFromDom(qEl);
                            if (derived) saved = { selected: derived, is_correct: null, metadata: null };
                        }

                        if (!saved) return;

                        var payload = saved.selected ?? saved.metadata ?? saved;
                        var is_correct = (typeof saved.is_correct !== 'undefined') ? saved.is_correct : null;

                        var metaEl = qEl ? qEl.querySelector('[data-meta-json]') : null;
                        var meta = null;
                        if (metaEl) {
                            try { meta = JSON.parse(metaEl.getAttribute('data-meta-json')); } catch(e) { meta = null; }
                        }

                        // DEBUG: attempt to resolve selected IDs to option text for troubleshooting
                        try {
                                var optList = meta && meta.options ? meta.options : null;
                                var selectedArr = null;
                                if (payload && Array.isArray(payload)) selectedArr = payload;
                                else if (payload && payload.selected && Array.isArray(payload.selected)) selectedArr = payload.selected;
                                else if (payload && payload.value && Array.isArray(payload.value)) selectedArr = payload.value;
                                else if (payload && (typeof payload === 'string' || typeof payload === 'number')) selectedArr = [payload];

                                var resolved = null;
                                if (optList && selectedArr) {
                                    resolved = selectedArr.map(function(v){
                                        if (typeof v === 'number' || (!isNaN(v) && String(v).trim() !== '')) {
                                            var idx = Number(v);
                                            if (optList[idx] !== undefined) return optList[idx];
                                        }
                                        for (var oi=0; oi < optList.length; oi++) {
                                            var o = optList[oi];
                                            if (!o) continue;
                                            if (typeof o === 'object' && (o.id == v || o.value == v || o.key == v)) return o.text || o.label || o.content || o.value || JSON.stringify(o);
                                        }
                                        return String(v);
                                    });
                                }
                        } catch(e) { /* debug removed */ }

                        // Save into helper's memory/store so other helpers can reuse it
                        if (window.readingPartHelper && window.readingPartHelper.saveAnswer) {
                            try { window.readingPartHelper.saveAnswer(qid, payload, { is_correct: is_correct }, {{ $attempt->id }}); } catch(e){}
                        }

                        // Try to show feedback. Different helper signatures may exist, so attempt
                        // a couple of variants then fall back to inlineFeedback.
                        var shown = false;
                        if (window.readingPartHelper && window.readingPartHelper.showFeedback) {
                            try { window.readingPartHelper.showFeedback(qid, payload); shown = true; } catch(e) {
                                try { window.readingPartHelper.showFeedback(qid, is_correct, meta || (saved.metadata || {})); shown = true; } catch(e) { shown = false; }
                            }
                        }

                        if (!shown && window.inlineFeedback && qEl) {
                            try { window.inlineFeedback.show(qid, JSON.stringify(payload ?? '(Chưa có đáp án)'), '', ''); } catch(e){}
                        }
                    } catch(e){}
                });

            } catch(e){}
        })();
    </script>
    @endpush
