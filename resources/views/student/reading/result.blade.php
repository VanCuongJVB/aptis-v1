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

        <div class="mb-4 grid grid-cols-3 gap-4 text-sm text-gray-700">
            <div>Thời gian bắt đầu: {{ $attempt->started_at ? $attempt->started_at->format('H:i d/m/Y') : '-' }}</div>
            <div>Thời gian nộp: {{ $attempt->submitted_at ? $attempt->submitted_at->format('H:i d/m/Y') : '-' }}</div>
            <div>Thời lượng (phút): {{ $duration ?? '-' }}</div>
        </div>

        <hr class="my-4">

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
                    if (!empty($meta['answers']) && is_array($meta['answers'])) {
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
                    if ($ans && isset($ans->metadata) && is_array($ans->metadata)) {
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
                    1 => 'border-blue-200 bg-blue-50',
                    2 => 'border-indigo-200 bg-indigo-50',
                    3 => 'border-yellow-200 bg-yellow-50',
                    4 => 'border-purple-200 bg-purple-50',
                    default => 'border-gray-200 bg-white',
                };
                $chipClass = match($quiz->part ?? null) {
                    1 => 'bg-blue-100 text-blue-800',
                    2 => 'bg-indigo-100 text-indigo-800',
                    3 => 'bg-yellow-100 text-yellow-800',
                    4 => 'bg-purple-100 text-purple-800',
                    default => 'bg-gray-100 text-gray-800',
                };
            @endphp

            <div class="mb-4 p-4 border rounded {{ $partClass }}">
                <div class="flex items-start justify-between">
                    <div class="font-semibold flex items-center gap-2">
                        <span class="text-sm font-medium">Câu {{ $num }}</span>
                        @if($isMultiSelected)
                            <span class="inline-flex items-center gap-2 px-2 py-0.5 rounded bg-gray-50 text-gray-800 text-xs">
                                <strong class="text-sm">{{ $perItemCorrectCount }}</strong>
                                /
                                <span class="text-sm">{{ $perItemTotal }}</span>
                                <span class="text-xs text-gray-500">đúng</span>
                                @if(!empty($perItemPresenceCount))
                                    <span class="mx-1">·</span>
                                    <span class="text-xs text-amber-600">{{ $perItemPresenceCount }} hiện diện</span>
                                @endif
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

                @php $partToInclude = $partNum ?? ($quiz->part ?? 1); @endphp
                @includeIf('student.reading.result_parts.part' . $partToInclude)
            </div>
        @endforeach

        <div class="flex justify-between mt-6">
            <a href="{{ route('reading.sets.index') }}" class="btn">Quay lại bộ đề</a>
            {{-- <a href="{{ route('student.attempts.history') }}" class="btn">Lịch sử</a> --}}
        </div>
    </div>
</div>
@endsection
