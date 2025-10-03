@extends('layouts.app')

@section('title', 'Kết quả Full Random Listening')

@section('content')
    <div class="container mx-auto py-6">
        <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-semibold">Full Random Listening Test</h2>
                    <p class="text-sm text-gray-500">Results</p>
                </div>
                <div class="text-right">
                    @php
                        $totalCorrect = 0;
                        $totalQuestions = 0;
                        
                        foreach($answers as $ans) {
                            if ($ans['part'] == 1) {
                                // Part 1: mỗi câu tính 1 điểm
                                if (isset($ans['correct']) && $ans['correct']) {
                                    $totalCorrect++;
                                }
                                $totalQuestions++;
                            }
                            else if ($ans['part'] == 2) {
                                // Part 2: Kiểm tra từng speaker mapping
                                $userAnswers = is_array($ans['userAnswer']) ? $ans['userAnswer'] : [];
                                $correctAnswers = is_array($ans['correctAnswer']) ? $ans['correctAnswer'] : [];
                                foreach ($userAnswers as $index => $userChoice) {
                                    if (isset($correctAnswers[$index]) && 
                                        ((is_numeric($userChoice) && is_numeric($correctAnswers[$index]) && 
                                          intval($userChoice) === intval($correctAnswers[$index])) ||
                                         (is_string($userChoice) && is_string($correctAnswers[$index]) && 
                                          strtoupper(trim($userChoice)) === strtoupper(trim($correctAnswers[$index]))))) {
                                        $totalCorrect++;
                                    }
                                    $totalQuestions++;
                                }
                            }
                            else if ($ans['part'] == 3) {
                                // Part 3: Kiểm tra từng item
                                $userAnswers = is_array($ans['userAnswer']) ? $ans['userAnswer'] : [];
                                $correctAnswers = is_array($ans['correctAnswer']) ? $ans['correctAnswer'] : [];
                                foreach ($userAnswers as $index => $userChoice) {
                                    if (isset($correctAnswers[$index])) {
                                        if ((is_numeric($userChoice) && is_numeric($correctAnswers[$index]) && 
                                             intval($userChoice) === intval($correctAnswers[$index])) ||
                                            (is_string($userChoice) && is_string($correctAnswers[$index]) && 
                                             strtoupper(trim($userChoice)) === strtoupper(trim($correctAnswers[$index])))) {
                                            $totalCorrect++;
                                        }
                                        $totalQuestions++;
                                    }
                                }
                            }
                            else if ($ans['part'] == 4) {
                                // Part 4: Kiểm tra từng sub-question
                                $userAnswers = is_array($ans['userAnswer']) ? $ans['userAnswer'] : [];
                                $correctAnswers = is_array($ans['correctAnswer']) ? $ans['correctAnswer'] : [];
                                foreach ($userAnswers as $index => $userChoice) {
                                    if (isset($correctAnswers[$index])) {
                                        if ((is_numeric($userChoice) && is_numeric($correctAnswers[$index]) && 
                                             intval($userChoice) === intval($correctAnswers[$index])) ||
                                            (is_string($userChoice) && is_string($correctAnswers[$index]) && 
                                             strtoupper(trim($userChoice)) === strtoupper(trim($correctAnswers[$index])))) {
                                            $totalCorrect++;
                                        }
                                        $totalQuestions++;
                                    }
                                }
                            }
                        }
                        
                        $percentage = $totalQuestions > 0 ? ($totalCorrect / $totalQuestions) * 100 : 0;
                    @endphp
                    <div class="text-xl font-bold {{ $percentage > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($percentage, 2) }}%
                    </div>
                    <div class="text-sm font-medium">
                        {{ $totalCorrect }} / {{ $totalQuestions }}
                    </div>
                </div>
            </div>

            <hr class="my-4">

            {{-- Part 1 Results --}}
            @foreach([1, 2, 3, 4] as $partNum)
                @php
                    $partAnswers = collect($answers)->where('part', $partNum);
                    if ($partAnswers->isEmpty())
                        continue;
                @endphp
                <div class="mb-6" data-result-part="{{ $partNum }}">
                    @php
                        // Kiểm tra xem part có đúng hết không
                        $isPartCorrect = false;
                        
                        if ($partNum == 1) {
                            $isPartCorrect = $answer['correct'] ?? false;
                        } else {
                            $allCorrect = true;
                            foreach ($partAnswers as $ans) {
                                $userAnswers = is_array($ans['userAnswer']) ? $ans['userAnswer'] : [];
                                $correctAnswers = is_array($ans['correctAnswer']) ? $ans['correctAnswer'] : [];
                                
                                foreach ($userAnswers as $index => $userChoice) {
                                    if (!isset($correctAnswers[$index]) || 
                                        ((is_numeric($userChoice) && is_numeric($correctAnswers[$index]) && 
                                          intval($userChoice) !== intval($correctAnswers[$index])) ||
                                         (is_string($userChoice) && is_string($correctAnswers[$index]) && 
                                          strtoupper(trim($userChoice)) !== strtoupper(trim($correctAnswers[$index]))))) {
                                        $allCorrect = false;
                                        break 2;
                                    }
                                }
                            }
                            $isPartCorrect = $allCorrect;
                        }
                    @endphp
                    <h3 class="text-lg font-semibold mb-3 pb-2 border-b flex justify-between items-center">
                        <span>Part {{ $partNum }} -
                            @if($partNum == 1) Multiple Choice
                            @elseif($partNum == 2) Matching
                            @elseif($partNum == 3) Category Matching
                            @elseif($partNum == 4) Multiple Choice (Reading)
                            @endif
                        </span>
                        @if($isPartCorrect)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>Đúng</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span>Sai</span>
                            </span>
                        @endif
                    </h3>
                    @if($partNum == 1)
                        {{-- Standard Part 1 layout --}}
                        @foreach($partAnswers as $index => $answer)
                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block"
                                data-qid="{{ $answer['question']['id'] ?? '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $loop->iteration }}</span>
                                        @if($answer['correct'])
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Correct
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Incorrect
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($answer['question'])
                                    @if($answer['question']['stem'])
                                        <div class="prose mt-2 mb-3 text-base text-gray-800 font-medium leading-relaxed">
                                            {{ $answer['question']['stem'] }}
                                        </div>
                                    @endif
                                    @if($answer['question']['content'])
                                        <div class="prose mt-2 mb-4 text-sm text-gray-600 leading-relaxed">
                                            {{ $answer['question']['content'] }}
                                        </div>
                                    @endif
                                @endif

                                <div class="mt-3">
                                    <h4 class="font-semibold mb-2">Results</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-3 border rounded border-gray-200">
                                            <div class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                Your Answer
                                            </div>
                                            <div class="font-medium text-gray-800 leading-relaxed">
                                                {{ $answer['userAnswer'] ?? 'No answer provided' }}
                                            </div>
                                        </div>

                                        @if(!$answer['correct'])
                                            <div class="p-3 border rounded border-green-200 bg-green-50">
                                                <div class="text-sm font-medium text-green-700 mb-2 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Correct Answer
                                                </div>
                                                <div class="font-medium text-green-800 leading-relaxed">
                                                    {{ $answer['correctAnswer'] }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @elseif($partNum == 2)
                        {{-- Part 2 layout - show 4 speakers --}}
                        @foreach($partAnswers as $index => $answer)
                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block"
                                data-qid="{{ $answer['question']['id'] ?? '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $loop->iteration }}</span>
                                        @if($answer['correct'])
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Correct
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Incorrect
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($answer['question'])
                                    @if($answer['question']['stem'])
                                        <div class="prose mt-2 mb-3 text-base text-gray-800 font-medium leading-relaxed">
                                            {{ $answer['question']['stem'] }}
                                        </div>
                                    @endif
                                    @if($answer['question']['content'])
                                        <div class="prose mt-2 mb-4 text-sm text-gray-600 leading-relaxed">
                                            {{ $answer['question']['content'] }}
                                        </div>
                                    @endif
                                @endif

                                {{-- Display 4 speakers --}}
                                <div class="grid gap-4">
                                    @php
                                        // Normalize user answers
                                        $userAnswers = [];
                                        if (!empty($answer['userAnswer']) && is_array($answer['userAnswer'])) {
                                            foreach ($answer['userAnswer'] as $k => $v) {
                                                $userAnswers[intval($k)] = is_numeric($v) ? intval($v) : $v;
                                            }
                                        }

                                        // Get correct answers: prefer top-level 'correctAnswer', fallback to question.metadata.answers
                                        $possibleCorrect = [];
                                        if (!empty($answer['correctAnswer']) && is_array($answer['correctAnswer'])) {
                                            $possibleCorrect = $answer['correctAnswer'];
                                        } elseif (!empty($answer['question']['metadata']['answers']) && is_array($answer['question']['metadata']['answers'])) {
                                            $possibleCorrect = $answer['question']['metadata']['answers'];
                                        }
                                        $correctAnswers = [];
                                        foreach ($possibleCorrect as $k => $v) {
                                            $correctAnswers[intval($k)] = is_numeric($v) ? intval($v) : $v;
                                        }

                                        $questionOptions = $answer['question']['metadata']['options'] ?? [];
                                        $speakers = ['A', 'B', 'C', 'D'];
                                    @endphp

                                    @foreach($speakers as $speakerIndex => $speakerLetter)
                                        @php
                                            $userChoice = array_key_exists($speakerIndex, $userAnswers) ? $userAnswers[$speakerIndex] : null;
                                            $correctChoice = array_key_exists($speakerIndex, $correctAnswers) ? $correctAnswers[$speakerIndex] : null;

                                            // Compare safely (handle numeric indices or string labels)
                                            $isCorrect = false;
                                            if ($userChoice !== null && $correctChoice !== null) {
                                                if (is_numeric($userChoice) && is_numeric($correctChoice)) {
                                                    $isCorrect = intval($userChoice) === intval($correctChoice);
                                                } else {
                                                    $isCorrect = strval($userChoice) === strval($correctChoice);
                                                }
                                            }

                                            // Format user answer text
                                            $userAnswerText = 'Chưa trả lời';
                                            if ($userChoice !== null && is_numeric($userChoice) && isset($questionOptions[intval($userChoice)])) {
                                                $userAnswerText = chr(65 + intval($userChoice)) . ' - ' . $questionOptions[intval($userChoice)];
                                            } elseif ($userChoice !== null && is_string($userChoice) && isset($questionOptions[$userChoice])) {
                                                $userAnswerText = strtoupper($userChoice) . ' - ' . $questionOptions[$userChoice];
                                            }

                                            // Format correct answer text
                                            $correctAnswerText = '';
                                            if ($correctChoice !== null && is_numeric($correctChoice) && isset($questionOptions[intval($correctChoice)])) {
                                                $correctAnswerText = chr(65 + intval($correctChoice)) . ' - ' . $questionOptions[intval($correctChoice)];
                                            } elseif ($correctChoice !== null && is_string($correctChoice) && isset($questionOptions[$correctChoice])) {
                                                $correctAnswerText = strtoupper($correctChoice) . ' - ' . $questionOptions[$correctChoice];
                                            }
                                        @endphp

                                        <div
                                            class="bg-gray-50 rounded-lg p-4 border {{ $isCorrect ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-semibold text-gray-800">Speaker {{ $speakerLetter }}</h4>
                                                @if($isCorrect)
                                                    <div class="flex items-center text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M5 13l4 4L19 7"></path>
                                                        </svg>
                                                        <span class="text-xs font-medium">Đúng</span>
                                                    </div>
                                                @else
                                                    <div class="flex items-center text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                        <span class="text-xs font-medium">Sai</span>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="grid md:grid-cols-2 gap-3">
                                                <div class="bg-white rounded p-3 border">
                                                    <div class="text-xs font-medium text-gray-600 mb-1 flex items-center">
                                                        <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                            </path>
                                                        </svg>
                                                        Đáp án của bạn
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-800">{{ $userAnswerText }}</div>
                                                </div>

                                                @if(!$isCorrect && $correctAnswerText)
                                                    <div class="bg-green-50 rounded p-3 border border-green-200">
                                                        <div class="text-xs font-medium text-green-700 mb-1 flex items-center">
                                                            <svg class="w-3 h-3 mr-1 text-green-600" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            Đáp án đúng
                                                        </div>
                                                        <div class="text-sm font-medium text-green-800">{{ $correctAnswerText }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @elseif($partNum == 3)
                        {{-- Part 3 layout - show 4 items --}}
                        @foreach($partAnswers as $index => $answer)
    <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block"
        data-qid="{{ $answer['question']['id'] ?? '' }}">
        <div class="flex items-start justify-between">
            <div class="font-semibold flex items-center gap-2">
                <span class="text-sm font-medium">Question {{ $loop->iteration }}</span>
                @if($answer['correct'])
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7"></path>
                        </svg>
                        Correct
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Incorrect
                    </span>
                @endif
            </div>
        </div>

        @if($answer['question'])
            @if($answer['question']['stem'])
                <div class="prose mt-2 mb-3 text-base text-gray-800 font-medium leading-relaxed">
                    {{ $answer['question']['stem'] }}
                </div>
            @endif
            @if($answer['question']['content'])
                <div class="prose mt-2 mb-4 text-sm text-gray-600 leading-relaxed">
                    {{ $answer['question']['content'] }}
                </div>
            @endif
        @endif

        {{-- Display 4 items --}}
        <div class="grid gap-4">
            @php
                $userAnswers = is_array($answer['userAnswer']) ? $answer['userAnswer'] : [];
                $correctAnswers = is_array($answer['correctAnswer']) ? $answer['correctAnswer'] : [];
                $questionOptions = $answer['question']['metadata']['options'] ?? [];
                $items = $answer['question']['metadata']['items'] ?? [];

                // helper: lấy text từ choice (index hoặc string)
                $getAnswerText = function($choice, $options) {
                    if ($choice === null) return null;

                    // nếu là số
                    if (is_numeric($choice) && isset($options[intval($choice)])) {
                        return chr(65 + intval($choice)) . ' - ' . $options[intval($choice)];
                    }

                    // nếu là chữ cái (A/B/C/D)
                    if (preg_match('/^[A-Da-d]$/', (string)$choice)) {
                        $idx = ord(strtoupper($choice)) - 65;
                        return isset($options[$idx]) ? chr(65 + $idx) . ' - ' . $options[$idx] : strtoupper($choice);
                    }

                    // nếu là text -> tìm trong options
                    foreach ($options as $i => $opt) {
                        if (mb_strtolower(trim($opt)) === mb_strtolower(trim($choice))) {
                            return chr(65 + $i) . ' - ' . $opt;
                        }
                    }

                    // fallback: trả nguyên text
                    return (string)$choice;
                };
            @endphp

            @foreach($items as $itemIndex => $itemText)
                @php
                    $userChoice = $userAnswers[$itemIndex] ?? null;
                    $correctChoice = $correctAnswers[$itemIndex] ?? null;

                    $userAnswerText = $getAnswerText($userChoice, $questionOptions) ?? 'Chưa trả lời';
                    $correctAnswerText = $getAnswerText($correctChoice, $questionOptions);

                    $isCorrect = $userAnswerText && $correctAnswerText && ($userAnswerText === $correctAnswerText);
                @endphp

                <div class="bg-gray-50 rounded-lg p-4 border {{ $isCorrect ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-800 text-sm leading-relaxed">{{ $itemText }}</h4>
                        @if($isCorrect)
                            <div class="flex items-center text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span class="text-xs font-medium">Đúng</span>
                            </div>
                        @else
                            <div class="flex items-center text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                <span class="text-xs font-medium">Sai</span>
                            </div>
                        @endif
                    </div>

                    <div class="grid md:grid-cols-2 gap-3">
                        <!-- User Answer -->
                        <div class="bg-white rounded p-3 border">
                            <div class="text-xs font-medium text-gray-600 mb-1 flex items-center">
                                <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 18 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                    </path>
                                </svg>
                                Đáp án của bạn
                            </div>
                            <div class="text-sm font-medium text-gray-800">{{ $userAnswerText }}</div>
                        </div>

                        <!-- Correct Answer (only show if wrong) -->
                        @if(!$isCorrect && $correctAnswerText)
                            <div class="bg-green-50 rounded p-3 border border-green-200">
                                <div class="text-xs font-medium text-green-700 mb-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1 text-green-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Đáp án đúng
                                </div>
                                <div class="text-sm font-medium text-green-800">{{ $correctAnswerText }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

                    @elseif($partNum == 4)
                        {{-- Part 4 layout - show sub-questions --}}
                        @foreach($partAnswers as $index => $answer)
                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block"
                                data-qid="{{ $answer['question']['id'] ?? '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $loop->iteration }}</span>
                                        @if($answer['correct'])
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Correct
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Incorrect
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($answer['question'])
                                    @if($answer['question']['stem'])
                                        <div class="prose mt-2 mb-3 text-base text-gray-800 font-medium leading-relaxed">
                                            {{ $answer['question']['stem'] }}
                                        </div>
                                    @endif
                                    @if($answer['question']['content'])
                                        <div class="prose mt-2 mb-4 text-sm text-gray-600 leading-relaxed">
                                            {{ $answer['question']['content'] }}
                                        </div>
                                    @endif
                                @endif

                                {{-- Display sub-questions --}}
                                <div class="grid gap-4">
                                    @php
                                        $userAnswers = is_array($answer['userAnswer']) ? $answer['userAnswer'] : [];
                                        $correctAnswers = is_array($answer['correctAnswer']) ? $answer['correctAnswer'] : [];
                                        $questionOptions = $answer['question']['metadata']['options'] ?? [];
                                        $subQuestions = $answer['question']['metadata']['questions'] ?? [];

                                        // Ensure userAnswers has the same length as correctAnswers for proper iteration
                                        if (count($userAnswers) < count($correctAnswers)) {
                                            $userAnswers = array_pad($userAnswers, count($correctAnswers), null);
                                        }
                                    @endphp

                                    @foreach($subQuestions as $subIndex => $subQuestion)
                                        @php
                                            $userChoice = $userAnswers[$subIndex] ?? null;
                                            $correctChoice = $correctAnswers[$subIndex] ?? null;
                                            $isCorrect = $userChoice !== null && $correctChoice !== null && intval($userChoice) === intval($correctChoice);

                                            // Get sub-question options (each sub-question might have different options)
                                            $subOptions = $subQuestion['options'] ?? $questionOptions;

                                            // Format user answer
                                            $userAnswerText = 'Chưa trả lời';
                                            if ($userChoice !== null && is_numeric($userChoice) && isset($subOptions[$userChoice])) {
                                                $userAnswerText = chr(65 + intval($userChoice)) . ' - ' . $subOptions[$userChoice];
                                            }

                                            // Format correct answer
                                            $correctAnswerText = '';
                                            if ($correctChoice !== null && is_numeric($correctChoice) && isset($subOptions[$correctChoice])) {
                                                $correctAnswerText = chr(65 + intval($correctChoice)) . ' - ' . $subOptions[$correctChoice];
                                            }
                                        @endphp

                                        <div
                                            class="bg-gray-50 rounded-lg p-4 border {{ $isCorrect ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                                            <div class="flex items-start justify-between mb-3">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-2 mb-2">
                                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-medium">
                                                            {{ $subQuestion['sub'] ?? ($subIndex + 1) }}
                                                        </span>
                                                        @if($isCorrect)
                                                            <div class="flex items-center text-green-600 bg-green-100 px-2 py-1 rounded-full">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M5 13l4 4L19 7"></path>
                                                                </svg>
                                                                <span class="text-xs font-medium">Đúng</span>
                                                            </div>
                                                        @else
                                                            <div class="flex items-center text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                                </svg>
                                                                <span class="text-xs font-medium">Sai</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <h4 class="font-semibold text-gray-800 text-sm leading-relaxed mb-3">
                                                        {{ $subQuestion['stem'] ?? '' }}
                                                    </h4>
                                                </div>
                                            </div>

                                            <div class="grid md:grid-cols-2 gap-3">
                                                <!-- User Answer -->
                                                <div class="bg-white rounded p-3 border">
                                                    <div class="text-xs font-medium text-gray-600 mb-1 flex items-center">
                                                        <svg class="w-3 h-3 mr-1 text-blue-500" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                                            </path>
                                                        </svg>
                                                        Đáp án của bạn
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-800">{{ $userAnswerText }}</div>
                                                </div>

                                                <!-- Correct Answer (only show if wrong) -->
                                                @if(!$isCorrect && $correctAnswerText)
                                                    <div class="bg-green-50 rounded p-3 border border-green-200">
                                                        <div class="text-xs font-medium text-green-700 mb-1 flex items-center">
                                                            <svg class="w-3 h-3 mr-1 text-green-600" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            Đáp án đúng
                                                        </div>
                                                        <div class="text-sm font-medium text-green-800">{{ $correctAnswerText }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @else
                        {{-- Standard layout for other parts --}}
                        @foreach($partAnswers as $index => $answer)
                            <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block"
                                data-qid="{{ $answer['question']['id'] ?? '' }}">
                                <div class="flex items-start justify-between">
                                    <div class="font-semibold flex items-center gap-2">
                                        <span class="text-sm font-medium">Question {{ $loop->iteration }}</span>
                                        @if($answer['correct'])
                                            <span
                                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Correct
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                                Incorrect
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($answer['question'])
                                    @if($answer['question']['stem'])
                                        <div class="prose mt-2 mb-3 text-base text-gray-800 font-medium leading-relaxed">
                                            {{ $answer['question']['stem'] }}
                                        </div>
                                    @endif
                                    @if($answer['question']['content'])
                                        <div class="prose mt-2 mb-4 text-sm text-gray-600 leading-relaxed">
                                            {{ $answer['question']['content'] }}
                                        </div>
                                    @endif
                                @endif

                                <div class="mt-3">
                                    <h4 class="font-semibold mb-2">Results</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div class="p-3 border rounded border-gray-200">
                                            <div class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                                Your Answer
                                            </div>
                                            <div class="font-medium text-gray-800 leading-relaxed">
                                                @php
                                                    $userAnswerFormatted = $answer['userAnswer'];
                                                    $questionOptions = $answer['question']['metadata']['options'] ?? [];

                                                    if ($answer['part'] == 1 && is_numeric($answer['userAnswer'])) {
                                                        // Format Part 1: 0->A, 1->B, 2->C with option text
                                                        $optionIndex = intval($answer['userAnswer']);
                                                        $optionText = $questionOptions[$optionIndex] ?? '';
                                                        $userAnswerFormatted = chr(65 + $optionIndex) . ($optionText ? ' - ' . $optionText : '');
                                                    } elseif (is_array($answer['userAnswer'])) {
                                                        // Format arrays
                                                        $formattedAnswers = [];
                                                        foreach ($answer['userAnswer'] as $answerIndex) {
                                                            if (is_numeric($answerIndex)) {
                                                                $optionText = $questionOptions[$answerIndex] ?? '';
                                                                $formattedAnswers[] = chr(65 + intval($answerIndex)) . ($optionText ? ' - ' . $optionText : '');
                                                            }
                                                        }
                                                        $userAnswerFormatted = implode(', ', $formattedAnswers);
                                                    }
                                                @endphp
                                                {{ $userAnswerFormatted ?? 'No answer provided' }}
                                            </div>
                                        </div>

                                        @if(!$answer['correct'])
                                            <div class="p-3 border rounded border-green-200 bg-green-50">
                                                <div class="text-sm font-medium text-green-700 mb-2 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    Correct Answer
                                                </div>
                                                <div class="font-medium text-green-800 leading-relaxed">
                                                    @php
                                                        $correctAnswerFormatted = $answer['correctAnswer'];
                                                        $questionOptions = $answer['question']['metadata']['options'] ?? [];

                                                        if ($answer['part'] == 1 && is_numeric($answer['correctAnswer'])) {
                                                            // Format Part 1: 0->A, 1->B, 2->C with option text
                                                            $optionIndex = intval($answer['correctAnswer']);
                                                            $optionText = $questionOptions[$optionIndex] ?? '';
                                                            $correctAnswerFormatted = chr(65 + $optionIndex) . ($optionText ? ' - ' . $optionText : '');
                                                        } elseif (is_array($answer['correctAnswer'])) {
                                                            // Format arrays
                                                            $formattedAnswers = [];
                                                            foreach ($answer['correctAnswer'] as $answerIndex) {
                                                                if (is_numeric($answerIndex)) {
                                                                    $optionText = $questionOptions[$answerIndex] ?? '';
                                                                    $formattedAnswers[] = chr(65 + intval($answerIndex)) . ($optionText ? ' - ' . $optionText : '');
                                                                }
                                                            }
                                                            $correctAnswerFormatted = implode(', ', $formattedAnswers);
                                                        }
                                                    @endphp
                                                    {{ $correctAnswerFormatted }}
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endforeach

            <div class="flex justify-between mt-6">
                <a href="{{ route('listening.full-random') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Try
                    Again</a>
                <a href="{{ route('student.dashboard') }}"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Back
                    to Dashboard</a>
            </div>
        </div>
    </div>
@endsection