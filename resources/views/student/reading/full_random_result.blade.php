@extends('layouts.app')

@section('title', 'Kết quả Full Random Reading')

@section('content')
    @php
        // Helper để ép metadata về array an toàn
        function toArray($meta)
        {
            if (is_string($meta))
                return json_decode($meta, true) ?: [];
            if (is_array($meta))
                return $meta;
            if (is_object($meta))
                return (array) $meta;
            return [];
        }

        $totalCorrectAnswers = 0;
        $totalQuestions = 0;

        foreach ($groupedQuestions as $part => $questions) {
            foreach ($questions as $question) {
                $ans = $answers->get($question->id) ?? null;
                $questionTotal = 1;
                $questionCorrect = 0;

                switch ($part) {
                    case 1: // Multiple Choice
                        $questionCorrect = $ans?->is_correct ? 1 : 0;
                        break;

                    case 2: // Sentence Ordering
                        $meta = toArray($question->metadata);
                        $sentences = array_values($meta['sentences'] ?? ($meta['items'] ?? []));
                        $correct = $meta['correct_order'] ?? ($meta['correct'] ?? []);

                        $ansMeta = $ans ? toArray($ans->metadata) : [];
                        $user = $ansMeta['user_answer'] ?? ($ansMeta['selected'] ?? ($ansMeta['answers'] ?? []));

                        $toIdx = function ($arr, $sents) {
                            $out = [];
                            foreach ($arr as $v) {
                                if (is_numeric($v) && isset($sents[$v])) {
                                    $out[] = (int) $v;
                                } elseif (is_string($v)) {
                                    $f = array_search($v, $sents, true);
                                    if ($f !== false)
                                        $out[] = $f;
                                }
                            }
                            return $out;
                        };

                        $userIdx = $toIdx($user, $sentences);
                        $correctIdx = $toIdx($correct, $sentences);

                        if ($userIdx === $correctIdx && count($userIdx) > 0) {
                            $questionCorrect = 1;
                        }
                        break;

                    case 3: // Category Matching
                        $meta = toArray($question->metadata);
                        $ansMeta = $ans ? toArray($ans->metadata) : [];
                        $userAnswers = $ansMeta['user_answer'] ?? [];
                        $correctMapping = [];

                        if (isset($meta['answers'])) {
                            foreach ($meta['answers'] as $category => $indices) {
                                foreach ($indices as $index) {
                                    $correctMapping[$index] = $category;
                                }
                            }
                        }

                        $allCorrect = true;
                        $checkedCount = 0;

                        foreach ($userAnswers as $optionIndex => $userCategory) {
                            $correctCategory = $correctMapping[$optionIndex] ?? null;

                            if ($userCategory !== null && $correctCategory !== null) {
                                $checkedCount++;
                                $userLetter = is_numeric($userCategory) ? chr(65 + intval($userCategory)) : strtoupper(trim($userCategory));
                                $correctLetter = strtoupper(trim($correctCategory));

                                if ($userLetter !== $correctLetter) {
                                    $allCorrect = false;
                                    break;
                                }
                            }
                        }

                        $questionCorrect = ($allCorrect && $checkedCount > 0 && $checkedCount === count($userAnswers)) ? 1 : 0;
                        break;

                    case 4: // Paragraph Ordering
                        $meta = toArray($question->metadata);
                        $ansMeta = $ans ? toArray($ans->metadata) : [];
                        $userAnswers = $ansMeta['user_answer'] ?? $ansMeta['selected'] ?? [];
                        $correctOrder = $meta['correct'] ?? [];

                        $correctCount = 0;
                        foreach ($correctOrder as $position => $correctIndex) {
                            $userIndex = $userAnswers[$position] ?? null;
                            if ($userIndex !== null && $userIndex == $correctIndex) {
                                $correctCount++;
                            }
                        }

                        $questionCorrect = ($correctCount === count($correctOrder) && count($correctOrder) > 0) ? 1 : 0;
                        break;

                    default:
                        $questionCorrect = $ans?->is_correct ? 1 : 0;
                        break;
                }

                $totalCorrectAnswers += $questionCorrect;
                $totalQuestions += $questionTotal;
            }
        }

        $calculatedPercentage = $totalQuestions > 0 ? round(($totalCorrectAnswers / $totalQuestions) * 100, 2) : 0;
    @endphp

    <div class="container mx-auto py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold">Full Random Reading Test</h2>
                    <p class="text-sm text-gray-500">Results</p>
                </div>
                <div class="text-right">
                    <div class="text-xl font-bold {{ $calculatedPercentage > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($calculatedPercentage, 2) }}%
                    </div>
                    <div class="text-sm font-medium">
                        {{ $totalCorrectAnswers }} / {{ $totalQuestions }}
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- Include helpers --}}
            @includeIf('student.reading.parts._initialize_answers')
            @includeIf('student.reading.parts._check_helper')
            @includeIf('student.reading.parts._add_data_part_fix')
            @includeIf('student.reading.parts._simplified_debug')

            {{-- Part 1 Results --}}
            @if ($groupedQuestions->has(1) && $groupedQuestions[1]->count() > 0)
                    <div class="mb-6" data-result-part="1">
                        <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 1 - Gap Filling</h3>
                    @foreach ($groupedQuestions[1] as $index => $question)
                        @php
                            $ans = $answers->get($question->id) ?? null;
                            $isCorrect = $ans?->is_correct;
                            $meta = toArray($question->metadata);

                            // Extract data from metadata
                            $paragraphs = $meta['paragraphs'] ?? [];
                            $choices = $meta['choices'] ?? [];
                            $blankKeys = $meta['blank_keys'] ?? [];
                            $correctAnswers = $meta['correct_answers'] ?? [];

                            // Get user answers
                            $userAnswers = [];
                            if ($ans) {
                                $ansMeta = toArray($ans->metadata);
                                $userAnswers = $ansMeta['user_answer'] ?? $ansMeta['selected'] ?? [];
                            }
                        @endphp

                        <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                            <div class="flex items-start justify-between">
                                <div class="font-semibold flex items-center gap-2">
                                    <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
                                    @if ($isCorrect === true)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">✅ Đúng</span>
                                    @elseif($isCorrect === false)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">❌ Sai</span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-50 text-gray-800 text-xs">⏺️ Chưa chấm</span>
                                    @endif
                                </div>
                            </div>

                            @if($question->content)
                                <div class="prose mt-2 mb-4">{!! $question->content !!}</div>
                            @endif

                            <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>

                            <div class="mt-4">
                                <h4 class="font-semibold mb-3">Kết quả điền từ</h4>

                                <!-- Display paragraphs with filled blanks -->
                                <div class="space-y-4 mb-6">
                                    @foreach($paragraphs as $pIndex => $paragraph)
                                        @php
                                            $userAnswer = $userAnswers[$pIndex] ?? null;
                                            $correctAnswer = $correctAnswers[$pIndex] ?? null;
                                            $isBlankCorrect = $userAnswer === $correctAnswer;
                                            $blankKey = $blankKeys[$pIndex] ?? "BLANK" . ($pIndex + 1);
                                        @endphp
                                        <div class="p-4 border rounded {{ $isBlankCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center {{ $isBlankCorrect ? 'bg-green-500' : 'bg-red-500' }} text-white rounded-full text-sm font-medium">
                                                    {{ $pIndex + 1 }}
                                                </div>
                                                <div class="flex-1">
                                                    <div class="text-gray-700 mb-2">
                                                        @php
                                                            // Replace the blank key with the user's answer
                                                            $displayText = str_replace(
                                                                $blankKey,
                                                                '<span class="' . ($isBlankCorrect ? 'text-green-700 font-bold' : 'text-red-700 font-bold') . '">' . ($userAnswer ?? '[No answer]') . '</span>',
                                                                $paragraph
                                                            );
                                                        @endphp
                                                        {!! $displayText !!}
                                                    </div>

                                                    @if(!$isBlankCorrect && $correctAnswer)
                                                        <div class="flex items-center gap-2 text-sm text-green-600 mt-1">
                                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <span>Correct: <strong>{{ $correctAnswer }}</strong></span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Detailed word-by-word comparison -->
                                <div class="mt-6">
                                    <h5 class="font-semibold mb-3 text-gray-700">Chi tiết so sánh từng từ</h5>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @php
                                            $correctCount = 0;
                                            $totalBlanks = count($correctAnswers);
                                        @endphp

                                        @foreach($correctAnswers as $idx => $correctAnswer)
                                            @php
                                                $userAnswer = $userAnswers[$idx] ?? null;
                                                $isWordCorrect = $userAnswer === $correctAnswer;
                                                if ($isWordCorrect)
                                                    $correctCount++;
                                                $choicesForBlank = $choices[$idx] ?? [];
                                            @endphp
                                            <div class="p-3 border rounded {{ $isWordCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-medium text-gray-700">Blank {{ $idx + 1 }}</span>
                                                    <span class="text-sm {{ $isWordCorrect ? 'text-green-600' : 'text-red-600' }} font-medium">
                                                        {{ $isWordCorrect ? '✅ Đúng' : '❌ Sai' }}
                                                    </span>
                                                </div>

                                                <div class="space-y-2">
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-600">Your answer:</span>
                                                        <span class="{{ $isWordCorrect ? 'text-green-700 font-medium' : 'text-red-700 font-medium' }}">
                                                            {{ $userAnswer ?? 'No answer' }}
                                                        </span>
                                                    </div>

                                                    @if(!$isWordCorrect)
                                                        <div class="flex items-center justify-between">
                                                            <span class="text-sm text-gray-600">Correct answer:</span>
                                                            <span class="text-green-700 font-medium">{{ $correctAnswer }}</span>
                                                        </div>
                                                    @endif

                                                    @if(!empty($choicesForBlank))
                                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                                            <div class="text-xs text-gray-500 mb-1">Available choices:</div>
                                                            <div class="flex flex-wrap gap-1">
                                                                @foreach($choicesForBlank as $choice)
                                                                    <span class="text-xs px-2 py-1 rounded {{ $choice === $correctAnswer ? 'bg-green-200 text-green-800' : ($choice === $userAnswer ? 'bg-red-200 text-red-800' : 'bg-gray-100 text-gray-600') }}">
                                                                        {{ $choice }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Summary -->
                                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                        <div class="text-sm text-blue-800 font-medium text-center">
                                            Score: {{ $correctCount }}/{{ $totalBlanks }} correct
                                            @if($totalBlanks > 0)
                                                ({{ round(($correctCount / $totalBlanks) * 100) }}%)
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

                {{-- Part 2 Results --}}
                @if ($groupedQuestions->has(2) && $groupedQuestions[2]->count() > 0)
                    <div class="mb-6" data-result-part="2">
                        <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 2 - Sentence Ordering</h3>
                        @foreach ($groupedQuestions[2] as $index => $question)
                            @php
                                $meta = toArray($question->metadata);
                                $sentences = array_values($meta['sentences'] ?? ($meta['items'] ?? []));
                                $ans = $answers->get($question->id) ?? null;
                                $ansMeta = $ans ? toArray($ans->metadata) : [];

                                $userRaw = $ansMeta['user_answer'] ?? $ansMeta['selected'] ?? $ansMeta['answers'] ?? [];
                                $correctRaw = $ansMeta['correct_answer'] ?? $ansMeta['correct_order'] ?? $meta['correct_order'] ?? $meta['correct'] ?? [];

                                $toIndices = function ($arr, $sents) {
                                    $out = [];
                                    foreach ($arr as $v) {
                                        if (is_numeric($v) && isset($sents[$v])) {
                                            $out[] = (int) $v;
                                        } elseif (is_string($v)) {
                                            $found = array_search($v, $sents, true);
                                            if ($found !== false)
                                                $out[] = $found;
                                        }
                                    }
                                    return $out;
                                };

                                $correctIndices = $toIndices($correctRaw, $sentences);
                                $userIndices = $toIndices($userRaw, $sentences);

                                $displayCorrect = null;
                                if (!empty($userIndices) && !empty($correctIndices) && count($userIndices) === count($correctIndices)) {
                                    $all = true;
                                    for ($i = 0; $i < count($correctIndices); $i++) {
                                        if (!isset($userIndices[$i]) || $userIndices[$i] !== $correctIndices[$i]) {
                                            $all = false;
                                            break;
                                        }
                                    }
                                    $displayCorrect = $all;
                                } else {
                                    $displayCorrect = $ans?->is_correct;
                                }
                            @endphp

                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
                                        @if ($displayCorrect === true)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">✅ Đúng</span>
                                        @elseif($displayCorrect === false)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">❌ Sai</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="prose mt-2">{!! $question->content !!}</div>
                                <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>

                                @if (count($sentences) > 0 && count($correctIndices) > 0)
                                    <h4 class="font-semibold mb-3 mt-4">Kết quả sắp xếp câu</h4>
                                    @include('student.reading.result_parts.full_random_part2', [
                                        'sentences' => $sentences,
                                        'correctIndices' => $correctIndices,
                                        'userIndices' => $userIndices,
                                    ])
                                @else
                                    <div class="p-4 border rounded-lg bg-red-50 text-center mt-4">
                                        <p class="text-red-600 font-medium">No answer provided</p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Part 3 Results --}}
                @if ($groupedQuestions->has(3) && $groupedQuestions[3]->count() > 0)
                    <div class="mb-6" data-result-part="3">
                        <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 3 - Category Matching</h3>
                        @foreach ($groupedQuestions[3] as $index => $question)
                            @php
                                $ans = $answers->get($question->id);
                                $meta = toArray($question->metadata);
                                $ansMeta = $ans ? toArray($ans->metadata) : [];

                                $userAnswers = $ansMeta['user_answer'] ?? [];
                                $options = $meta['options'] ?? [];
                                $items = $meta['items'] ?? [];

                                $correctMapping = [];
                                if (isset($meta['answers'])) {
                                    foreach ($meta['answers'] as $category => $indices) {
                                        foreach ($indices as $idx) {
                                            $correctMapping[$idx] = $category;
                                        }
                                    }
                                }

                                $allCorrect = true;
                                $checkedCount = 0;
                                foreach ($userAnswers as $optionIndex => $userCategory) {
                                    $correctCategory = $correctMapping[$optionIndex] ?? null;
                                    if ($userCategory !== null && $correctCategory !== null) {
                                        $checkedCount++;
                                        $userLetter = is_numeric($userCategory) ? chr(65 + intval($userCategory)) : strtoupper(trim($userCategory));
                                        $correctLetter = strtoupper(trim($correctCategory));
                                        if ($userLetter !== $correctLetter) {
                                            $allCorrect = false;
                                            break;
                                        }
                                    }
                                }

                                $isCorrect = $allCorrect && $checkedCount > 0 && $checkedCount === count($userAnswers);
                            @endphp

                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
                                        @if ($isCorrect)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">✅ Đúng</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">❌ Sai</span>
                                        @endif
                                    </div>
                                </div>

                                @if($question->content)
                                    <div class="prose mt-2 mb-4">{!! $question->content !!}</div>
                                @endif

                                <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>

                                <div class="mt-4 space-y-6">
                                    {{-- Categories --}}
                                    <div class="bg-white border rounded-lg overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 border-b">
                                            <h5 class="font-medium text-gray-700">Categories</h5>
                                        </div>
                                        <div class="p-4">
                                            <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                @foreach (['A', 'B', 'C', 'D'] as $cat)
                                                    @if(isset($items[$loop->index]))
                                                        <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                                                            <div class="font-medium text-blue-900 mb-1">Category {{ $cat }}</div>
                                                            <div class="text-sm text-blue-800">{{ $items[$loop->index]['text'] ?? '' }}</div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Answers --}}
                                    <div class="bg-white border rounded-lg overflow-hidden">
                                        <div class="px-4 py-3 bg-gray-50 border-b">
                                            <h5 class="font-medium text-gray-700">Your Answers</h5>
                                        </div>
                                        <div class="p-4">
                                            <div class="max-w-3xl mx-auto space-y-3">
                                                @foreach ($options as $index => $text)
                                                    @php
                                                        $userCategory = $userAnswers[$index] ?? 'No Answer';
                                                        $correctCategory = $correctMapping[$index] ?? null;
                                                        if (is_numeric($userCategory))
                                                            $userCategory = chr(65 + intval($userCategory));
                                                        $isAnswerCorrect = $userCategory !== 'No Answer' && $correctCategory && strtoupper(trim($userCategory)) === strtoupper(trim($correctCategory));
                                                    @endphp

                                                    <div class="flex items-center p-3 {{ $isAnswerCorrect ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }} rounded-lg">
                                                        <div class="flex-grow">
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-medium {{ $isAnswerCorrect ? 'text-green-700' : 'text-red-700' }}">{{ chr(65 + $index) }}.</span>
                                                                <span class="text-gray-900">{{ $text }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="flex items-center gap-4">
                                                            <div class="flex items-center gap-2">
                                                                <span class="text-sm {{ $isAnswerCorrect ? 'text-green-600' : 'text-red-600' }}">Your answer:</span>
                                                                <span class="font-medium {{ $isAnswerCorrect ? 'text-green-700' : 'text-red-700' }}">{{ $userCategory === 'No Answer' ? '-' : $userCategory }}</span>
                                                            </div>
                                                            @if (!$isAnswerCorrect && $correctCategory)
                                                                <div class="flex items-center gap-2">
                                                                    <span class="text-sm text-green-600">Correct answer:</span>
                                                                    <span class="font-medium text-green-700">{{ $correctCategory }}</span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Part 4 Results --}}
                @if ($groupedQuestions->has(4) && $groupedQuestions[4]->count() > 0)
                    <div class="mb-6" data-result-part="4">
                        <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 4 - Paragraph Ordering</h3>
                        @foreach ($groupedQuestions[4] as $index => $question)
                            @php
                                $ans = $answers->get($question->id) ?? null;
                                $meta = toArray($question->metadata);
                                $ansMeta = $ans ? toArray($ans->metadata) : [];

                                $correctOrder = $meta['correct'] ?? [];
                                $options = $meta['options'] ?? [];
                                $paragraphs = $meta['paragraphs'] ?? [];
                                $userAnswers = $ansMeta['user_answer'] ?? $ansMeta['selected'] ?? [];

                                $correctCount = 0;
                                $totalCount = count($correctOrder);
                                foreach ($correctOrder as $position => $correctIndex) {
                                    $userIndex = $userAnswers[$position] ?? null;
                                    if ($userIndex !== null && $userIndex == $correctIndex) {
                                        $correctCount++;
                                    }
                                }
                                $isCorrect = ($correctCount === $totalCount && $totalCount > 0);
                            @endphp

                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
                                        @if ($isCorrect)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">✅ Đúng</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">❌ Sai</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="prose mt-2 mb-4">{!! $question->content !!}</div>
                                <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>

                                <div class="mt-6">
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full border border-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 border-b border-r">Đoạn văn</th>
                                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 border-b border-r">Heading đã chọn</th>
                                                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700 border-b">Đáp án</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach ($correctOrder as $position => $correctIndex)
                                                    @php
                                                        $userIndex = $userAnswers[$position] ?? null;
                                                        $userText = $userIndex !== null ? ($options[$userIndex] ?? "Option $userIndex") : "No answer provided";
                                                        $correctText = $options[$correctIndex] ?? "Option $correctIndex";
                                                        $paragraphText = $paragraphs[$position] ?? "Paragraph " . ($position + 1);
                                                        $isPositionCorrect = $userIndex !== null && $userIndex == $correctIndex;
                                                    @endphp
                                                    <tr class="{{ $isPositionCorrect ? 'bg-green-50' : 'bg-red-50' }}">
                                                        <td class="px-4 py-3 text-sm text-gray-700 border-r">
                                                            <div class="max-w-md">
                                                                <div class="font-medium mb-1">Paragraph {{ $position + 1 }}</div>
                                                                <div class="text-gray-600 text-xs leading-relaxed">{{ $paragraphText }}</div>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-sm border-r">
                                                            @if ($userIndex !== null)
                                                                <div class="{{ $isPositionCorrect ? 'text-green-700 font-medium' : 'text-red-700 font-medium' }}">{{ $userText }}</div>
                                                            @else
                                                                <div class="text-gray-500 italic">No answer provided</div>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-center">
                                                            @if ($userIndex !== null)
                                                                @if ($isPositionCorrect)
                                                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-green-500 text-white rounded-full text-xs">✅</span>
                                                                    <div class="text-green-600 text-xs mt-1">Correct</div>
                                                                @else
                                                                    <span class="inline-flex items-center justify-center w-6 h-6 bg-red-500 text-white rounded-full text-xs">❌</span>
                                                                    <div class="text-red-600 text-xs mt-1">Incorrect</div>
                                                                    <div class="text-green-600 text-xs mt-1 font-medium">Correct: {{ $correctText }}</div>
                                                                @endif
                                                            @else
                                                                <span class="text-gray-400 text-sm">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                                        <div class="text-sm text-blue-800 font-medium">Score: {{ $correctCount }}/{{ $totalCount }} correct</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between mt-6">
                    <a href="{{ route('reading.full-random') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Try Again</a>
                    <a href="{{ route('student.reading.dashboard') }}" class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Back to Dashboard</a>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
    <script>
        (function() {
            try {
                const key = 'attempt_answers_{{ $attempt->id }}';
                let answers = null;

                try {
                    const raw = localStorage.getItem(key);
                    if (raw) answers = JSON.parse(raw);
                } catch (e) {}

                if (!answers && typeof window.attemptAnswers !== 'undefined') {
                    answers = window.attemptAnswers;
                }

                answers = answers || {};
                window.attemptAnswers = answers;

                const getMetadataPayload = (metadata) => {
                    if (typeof metadata === 'string') {
                        try {
                            const meta = JSON.parse(metadata);
                            return meta.user_answer || meta.selected || meta.answers || null;
                        } catch (e) {
                            return null;
                        }
                    } else if (typeof metadata === 'object') {
                        return metadata.user_answer || metadata.selected || metadata.answers || null;
                    }
                    return null;
                };

                const processAnswer = (saved, part) => {
                    let payload = saved.selected_option_id || saved.user_answer || saved.selected || null;
                    if (!payload && saved.metadata) {
                        payload = getMetadataPayload(saved.metadata);
                    }
                    if (!payload && saved.metadata?.original) {
                        payload = saved.metadata.original.userAnswer || null;
                    }
                    return payload;
                };

                const qEls = Array.from(document.querySelectorAll('.question-block[data-qid]'));
                qEls.forEach(qEl => {
                    if (!qEl.hasAttribute('data-part')) {
                        const parent = qEl.closest('[data-result-part]');
                        if (parent) {
                            qEl.setAttribute('data-part', parent.getAttribute('data-result-part'));
                        }
                    }
                });

                qEls.forEach(qEl => {
                    try {
                        const qid = qEl.getAttribute('data-qid');
                        const saved = answers[qid];
                        if (!saved) return;

                        let part = parseInt(qEl.getAttribute('data-part')) || (saved.metadata?.part ? parseInt(saved.metadata.part) : 4);
                        const payload = processAnswer(saved, part);
                        const is_correct = saved.is_correct ?? null;

                        if (window.readingPartHelper?.showFeedback) {
                            try {
                                window.readingPartHelper.showFeedback(qid, payload);
                            } catch (e) {
                                try {
                                    window.readingPartHelper.showFeedback(qid, is_correct, { part, is_correct, user_answer: payload });
                                } catch (e) {}
                            }
                        }

                        if (window.readingPartHelper?.saveAnswer) {
                            try {
                                window.readingPartHelper.saveAnswer(qid, payload, { is_correct, part }, {{ $attempt->id }});
                            } catch (e) {}
                        }
                    } catch (e) {}
                });
            } catch (e) {}
        })();
    </script>
@endpush