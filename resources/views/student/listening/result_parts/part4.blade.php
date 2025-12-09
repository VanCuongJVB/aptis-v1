@php
    $meta = $question->metadata ?? [];
    $answerMeta = $answer && isset($answer->metadata) ? $answer->metadata : null;

    // --------------------------
    // Normalize user answers
    // --------------------------
    $userAnswersSeq = [];   // fallback: tuần tự theo vị trí
    $uaById = [];           // map theo question_id

    if (is_array($answerMeta)) {
        if (isset($answerMeta['answers']) && is_array($answerMeta['answers'])) {
            foreach ($answerMeta['answers'] as $ans) {
                if (isset($ans['question_id'])) {
                    $uaById[(string)$ans['question_id']] = $ans['value'] ?? null;
                } elseif (isset($ans['value'])) {
                    $userAnswersSeq[] = $ans['value'];
                }
            }
        } elseif (isset($answerMeta['selected']) && is_array($answerMeta['selected'])) {
            $userAnswersSeq = array_values($answerMeta['selected']);
        } elseif (isset($answerMeta['value']) && is_array($answerMeta['value'])) {
            $userAnswersSeq = array_values($answerMeta['value']);
        } else {
            $maybe = array_values($answerMeta);
            if (count($maybe) === 1 && is_array($maybe[0])) {
                $userAnswersSeq = array_values($maybe[0]);
            } else {
                $userAnswersSeq = $maybe;
            }
        }
    } elseif (is_string($answerMeta)) {
        $decoded = json_decode($answerMeta, true);
        if (is_array($decoded)) {
            if (isset($decoded['answers'])) {
                foreach ($decoded['answers'] as $ans) {
                    if (isset($ans['question_id'])) {
                        $uaById[(string)$ans['question_id']] = $ans['value'] ?? null;
                    } elseif (isset($ans['value'])) {
                        $userAnswersSeq[] = $ans['value'];
                    }
                }
            } else {
                $userAnswersSeq = array_values($decoded);
            }
        }
    }

    // --------------------------
    // Detect grouped structure
    // --------------------------
    $rawQuestions = is_array($meta['questions'] ?? null) ? $meta['questions'] : [];
    $isGrouped = false;
    if (!empty($rawQuestions)) {
        $first = $rawQuestions[0] ?? null;
        $isGrouped = is_array($first) && isset($first['questions']) && is_array($first['questions']);
    }

    // Chuẩn hóa thành mảng groups: [{ pair_index, audio, questions: [...] }]
    $groups = [];
    if ($isGrouped) {
        $groups = $rawQuestions;
    } else {
        // Dạng cũ (flat): gom thành 1 group
        $groups = [[
            'pair_index' => $meta['pair_index'] ?? null,
            'audio'      => $meta['audio'] ?? null,
            'questions'  => $rawQuestions,
        ]];
    }

    $getOptionText = function($option) {
        if (is_array($option)) return $option['text'] ?? $option['label'] ?? $option['content'] ?? $option['value'] ?? json_encode($option, JSON_UNESCAPED_UNICODE);
        if (is_object($option)) return $option->text ?? $option->label ?? $option->content ?? $option->value ?? json_encode((array)$option, JSON_UNESCAPED_UNICODE);
        return (string)$option;
    };

    $audioUrl = function($path) {
        if (!$path) return null;
        return asset($path);
    };

    static $__qCounter = 0;
    $__qCounter++;
    $headerIndex = (isset($loop) && isset($loop->iteration)) ? $loop->iteration : $__qCounter;
@endphp

<div class="mt-3 text-sm space-y-4">
    <div class="mb-2">
        <h2 class="text-sm font-medium mb-1">
            Câu hỏi #{{ $headerIndex }}
        </h2>
    </div>

    @php
        $hasAny = false;
        foreach ($groups as $g) {
            if (!empty($g['questions'])) { $hasAny = true; break; }
        }
    @endphp

    @if(!$hasAny)
        <div class="ml-2 text-gray-600">Không có dữ liệu</div>
    @else
        <div class="space-y-5">
            @php $seqCursor = 0; @endphp

            @foreach($groups as $gi => $group)
                @php
                    $pairLabel = $group['pair_index'] ?? ($gi + 1);
                    $audio     = $group['audio'] ?? null;
                    $qs        = is_array($group['questions'] ?? null) ? $group['questions'] : [];
                @endphp

                <div class="bg-white border rounded-xl shadow-sm p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-700">
                                Đoạn {{ $pairLabel }}
                            </span>
                            <span class="text-sm text-gray-700">({{ count($qs) }} câu hỏi)</span>
                        </div>

                        @if(!empty($audio))
                            <div class="w-full sm:w-auto">
                                <audio controls class="w-full sm:w-80" crossorigin="anonymous" playsinline webkit-playsinline>
                                    <source src="{{ asset($audio) }}" type="audio/mpeg">
                                    <source src="{{ asset($audio) }}" type="audio/mp3">
                                    Trình duyệt không hỗ trợ audio.
                                </audio>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 space-y-4">
                        @foreach($qs as $qi => $q)
                            @php
                                // >>> TỰ ĐẾM CHO CÂU CON <<<
                                $displayIndex = $qi + 1;

                                // Badge A/B/C... dùng 'sub' nếu có, nếu không suy từ $displayIndex (1->A).
                                $subLetter = $q['sub'] ?? chr(64 + max(1, (int)$displayIndex));

                                $stem      = $q['stem'] ?? '';
                                $textBlock = $q['text'] ?? '';
                                $options   = is_array($q['options'] ?? null) ? $q['options'] : [];
                                $correct   = $q['correct_index'] ?? null;

                                // Lấy đáp án user: ưu tiên theo question_id, fallback theo thứ tự
                                $userRaw = null;
                                if (isset($q['id']) && isset($uaById[(string)$q['id']])) {
                                    $userRaw = $uaById[(string)$q['id']];
                                } elseif (array_key_exists($seqCursor, $userAnswersSeq)) {
                                    $userRaw = $userAnswersSeq[$seqCursor];
                                }
                                $seqCursor++;

                                // Chuẩn hóa kiểu
                                if (is_array($userRaw)) $userRaw = count($userRaw) ? $userRaw[0] : null;
                                if (is_string($userRaw) && is_numeric($userRaw) && ctype_digit($userRaw)) {
                                    $userRaw = (int)$userRaw;
                                }

                                // Lấy text hiển thị
                                $selectedText = '';
                                $correctText  = '';
                                if ($userRaw !== null && $userRaw !== '' && is_array($options)) {
                                    $selectedText = isset($options[$userRaw]) ? $getOptionText($options[$userRaw]) : (string)$userRaw;
                                }
                                if ($correct !== null && $correct !== '' && is_array($options)) {
                                    $correctText = isset($options[$correct]) ? $getOptionText($options[$correct]) : (string)$correct;
                                }

                                // Đánh giá đúng/sai
                                $isCorrect = null;
                                if ($correct !== null && $userRaw !== null) {
                                    if ((string)$userRaw === (string)$correct) {
                                        $isCorrect = true;
                                    } else {
                                        $a = mb_strtolower(trim((string)($selectedText ?: $userRaw)));
                                        $b = mb_strtolower(trim((string)($correctText ?: $correct)));
                                        $isCorrect = ($a === $b);
                                    }
                                }
                            @endphp

                            <div class="rounded-lg border p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-600 text-white text-xs font-semibold">
                                            {{ $subLetter }}
                                        </span>
                                        <div class="text-gray-800 font-medium leading-5">
                                            <span class="text-gray-500 mr-1">#{{ $displayIndex }}</span>{!! e($stem) !!}
                                        </div>
                                    </div>

                                    @if($isCorrect === true)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold">
                                            ✓ Đúng
                                        </span>
                                    @elseif($isCorrect === false)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-50 text-red-700 text-xs font-semibold">
                                            ✗ Sai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-50 text-gray-600 text-xs font-semibold">
                                            • Chưa xác định
                                        </span>
                                    @endif
                                </div>

                                @if(!empty($textBlock))
                                    <details class="mt-3 group">
                                        <summary class="cursor-pointer text-xs text-blue-600 underline select-none">
                                            Hiện đoạn văn
                                        </summary>
                                        <div class="mt-2 text-sm text-gray-700 whitespace-pre-line">
                                            {!! e($textBlock) !!}
                                        </div>
                                    </details>
                                @endif

                                <div class="mt-3 grid sm:grid-cols-2 gap-3">
                                    <div>
                                        <div class="text-xs text-gray-500 mb-1">Bạn chọn</div>
                                        @if($userRaw !== null && $userRaw !== '')
                                            <div class="px-3 py-2 bg-gray-50 border rounded text-sm break-words whitespace-normal
                                                {{ $isCorrect === true ? 'text-green-700 border-green-200 bg-green-50' : 'text-gray-800 border-gray-200' }}">
                                                {!! e($selectedText) !!}
                                            </div>
                                        @else
                                            <div class="px-3 py-2 bg-gray-50 border rounded text-sm text-gray-500 italic">Chưa trả lời</div>
                                        @endif
                                    </div>

                                    @if($correctText !== '' && $isCorrect !== true)
                                        <div>
                                            <div class="text-xs text-gray-500 mb-1">Đáp án đúng</div>
                                            <div class="px-3 py-2 bg-green-50 border border-green-200 rounded text-sm text-green-700 break-words whitespace-normal">
                                                {!! e($correctText) !!}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
