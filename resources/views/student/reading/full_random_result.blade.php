@extends('layouts.app')

@section('title', 'Kết quả Full Random Reading')

@section('content')
@php
    // Simple calculation: 1 question = 1 point for all parts
    $totalCorrectAnswers = 0;
    $totalQuestions = 0;
    $debugInfo = [];
    
    foreach($groupedQuestions as $part => $questions) {
        foreach($questions as $question) {
            $ans = $answers->get($question->id) ?? null;
            $questionCorrect = 0;
            $questionTotal = 1; // Always 1 question = 1 point
            
            // Simple check: is this question correct?
            if ($ans && isset($ans->is_correct) && $ans->is_correct) {
                $questionCorrect = 1;
            }
            
            $totalCorrectAnswers += $questionCorrect;
            $totalQuestions += $questionTotal;
            
            $debugInfo[] = "Part $part Q{$question->id}: $questionCorrect/$questionTotal";
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
        
        {{-- Include helpers for result page to render answers --}}
        @includeIf('student.reading.parts._initialize_answers')
        @includeIf('student.reading.parts._check_helper')
        @includeIf('student.reading.parts._add_data_part_fix')
        @includeIf('student.reading.parts._simplified_debug')

        {{-- Part 1 Results --}}
        @if($groupedQuestions->has(1) && $groupedQuestions[1]->count() > 0)
            <div class="mb-6" data-result-part="1">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 1 - Multiple Choice</h3>
                @foreach($groupedQuestions[1] as $index => $question)
                    @php
                        $ans = $answers->get($question->id) ?? null;
                        $isCorrect = $ans && isset($ans->is_correct) ? $ans->is_correct : null;
                        $meta = null;
                        
                        // Debug output
                        if ($ans) {
                            error_log("Debug Part 1 Answer for question {$question->id}:");
                            error_log("Metadata: " . json_encode($ans->metadata));
                            error_log("Selected Option ID: " . $ans->selected_option_id);
                            error_log("Is Correct: " . ($isCorrect ? 'true' : 'false'));
                        }
                        
                        if (is_string($question->metadata) && !empty($question->metadata)) {
                            $meta = json_decode($question->metadata, true) ?: null;
                        } elseif (is_array($question->metadata) || is_object($question->metadata)) {
                            $meta = (array) $question->metadata;
                        }
                        
                        // Improved user answer retrieval - check multiple possible storage locations
                        $selectedOptionId = null;
                        $userAnswer = null;
                        
                        // First try direct option selection
                        if ($ans && isset($ans->selected_option_id) && $ans->selected_option_id) {
                            $selectedOptionId = $ans->selected_option_id;
                        }
                        
                        // Then try metadata
                        if (!$selectedOptionId && $ans && isset($ans->metadata)) {
                            if (is_string($ans->metadata)) {
                                try {
                                    $ansMeta = json_decode($ans->metadata, true);
                                    if (isset($ansMeta['user_answer'])) {
                                        $userAnswer = $ansMeta['user_answer'];
                                    } elseif (isset($ansMeta['selected'])) {
                                        $userAnswer = $ansMeta['selected'];
                                    }
                                } catch (\Exception $e) {
                                    // Parsing failed, continue with other methods
                                }
                            } elseif (is_array($ans->metadata)) {
                                $ansMeta = $ans->metadata;
                                if (isset($ansMeta['user_answer'])) {
                                    $userAnswer = $ansMeta['user_answer'];
                                } elseif (isset($ansMeta['selected'])) {
                                    $userAnswer = $ansMeta['selected'];
                                }
                            }
                            
                            // If we found a numeric user answer, use it as the selected option ID
                            if (is_numeric($userAnswer)) {
                                $selectedOptionId = $userAnswer;
                            }
                        }
                        
                        // Get selected option text
                        $selectedOptionText = null;
                        
                        // First check if we have a related option model
                        if ($ans && $ans->selectedOption) {
                            $selectedOptionText = $ans->selectedOption->text ?? 
                                                $ans->selectedOption->content ?? 
                                                $ans->selectedOption->label ?? 
                                                $ans->selectedOption->title ?? null;
                        }
                        
                        // If no text found via relation, try to find directly from question options
                        if (!$selectedOptionText && $selectedOptionId && $question->options) {
                            $selectedOption = $question->options->where('id', $selectedOptionId)->first();
                            if ($selectedOption) {
                                $selectedOptionText = $selectedOption->text ?? 
                                                    $selectedOption->content ?? 
                                                    $selectedOption->label ?? 
                                                    $selectedOption->title ?? null;
                            }
                        }
                        
                        // Determine correct option
                        $correctOption = null;
                        if (isset($meta['correct_answer']) && is_numeric($meta['correct_answer'])) {
                            $correctOption = $question->options->where('id', $meta['correct_answer'])->first();
                        } elseif (isset($meta['correct']) && is_numeric($meta['correct'])) {
                            $correctOption = $question->options->where('id', $meta['correct'])->first();
                        } else {
                            $correctOption = $question->options->where('is_correct', true)->first();
                        }
                        
                        $correctOptionText = $correctOption ? ($correctOption->text ?? 
                                                            $correctOption->content ?? 
                                                            $correctOption->label ?? 
                                                            $correctOption->title ?? null) : null;
                    @endphp
                    
                    <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                        <div class="flex items-start justify-between">
                            <div class="font-semibold flex items-center gap-2">
                                <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
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
                                @endif
                            </div>
                        </div>
                        
                        <div class="prose mt-2">{!! $question->content !!}</div>
                        
                        <!-- Add inline feedback container -->
                        <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>
                        
                        <div class="mt-3">
                            <h4 class="font-semibold mb-2">Results</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="p-3 border rounded border-gray-200">
                                    <div class="text-sm font-medium mb-2">Word-by-word comparison:</div>
                                    @php
                                        $userAnswers = [];
                                        $correctAnswers = [];
                                        
                                        // Get user answers
                                        if ($ans && isset($ans->metadata)) {
                                            if (is_string($ans->metadata)) {
                                                try {
                                                    $meta = json_decode($ans->metadata, true);
                                                    if (isset($meta['selected'])) {
                                                        $userAnswers = is_array($meta['selected']) ? $meta['selected'] : [$meta['selected']];
                                                    } elseif (isset($meta['user_answer'])) {
                                                        $userAnswers = is_array($meta['user_answer']) ? $meta['user_answer'] : [$meta['user_answer']];
                                                    }
                                                } catch (\Exception $e) {}
                                            } elseif (is_array($ans->metadata)) {
                                                if (isset($ans->metadata['selected'])) {
                                                    $userAnswers = is_array($ans->metadata['selected']) ? $ans->metadata['selected'] : [$ans->metadata['selected']];
                                                } elseif (isset($ans->metadata['user_answer'])) {
                                                    $userAnswers = is_array($ans->metadata['user_answer']) ? $ans->metadata['user_answer'] : [$ans->metadata['user_answer']];
                                                }
                                            }
                                        }
                                        
                                        // Get correct answers
                                        if ($ans && isset($ans->metadata)) {
                                            if (is_string($ans->metadata)) {
                                                try {
                                                    $meta = json_decode($ans->metadata, true);
                                                    if (isset($meta['correct_answer'])) {
                                                        $correctAnswers = is_array($meta['correct_answer']) ? $meta['correct_answer'] : [$meta['correct_answer']];
                                                    }
                                                } catch (\Exception $e) {}
                                            } elseif (is_array($ans->metadata)) {
                                                if (isset($ans->metadata['correct_answer'])) {
                                                    $correctAnswers = is_array($ans->metadata['correct_answer']) ? $ans->metadata['correct_answer'] : [$ans->metadata['correct_answer']];
                                                }
                                            }
                                        }
                                    @endphp
                                    
                                    @if(!empty($userAnswers) && !empty($correctAnswers))
                                        <div class="space-y-2">
                                            @foreach($userAnswers as $index => $userAnswer)
                                                @php
                                                    $correctAnswer = $correctAnswers[$index] ?? null;
                                                    $isWordCorrect = $correctAnswer && strtolower(trim($userAnswer)) === strtolower(trim($correctAnswer));
                                                @endphp
                                                <div class="flex items-center space-x-4">
                                                    <div class="w-24 text-sm font-medium">Word {{ $index + 1}}:</div>
                                                    <div class="flex items-center">
                                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full {{ $isWordCorrect ? 'bg-green-500' : 'bg-red-500' }} text-white text-xs mr-2">
                                                            @if($isWordCorrect)
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-2.293-2.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/></svg>
                                                            @else
                                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                                            @endif
                                                        </span>
                                                        <span class="font-medium {{ $isWordCorrect ? 'text-green-700' : 'text-red-700' }}">
                                                            {{ $userAnswer }}
                                                            @if(!$isWordCorrect)
                                                                <span class="text-gray-500 ml-2">→</span>
                                                                <span class="text-green-700 ml-2">{{ $correctAnswer }}</span>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        <div class="mt-3 text-sm">
                                            @php
                                                $correctCount = 0;
                                                foreach($userAnswers as $index => $userAnswer) {
                                                    if (isset($correctAnswers[$index]) && strtolower(trim($userAnswer)) === strtolower(trim($correctAnswers[$index]))) {
                                                        $correctCount++;
                                                    }
                                                }
                                            @endphp
                                            <span class="font-medium">Score: </span>
                                            <span class="font-medium {{ $correctCount === count($correctAnswers) ? 'text-green-700' : 'text-red-700' }}">
                                                {{ $correctCount }}/{{ count($correctAnswers) }} correct
                                            </span>
                                        </div>
                                    @else
                                        <div class="text-red-600">No answer provided</div>
                                    @endif
                                </div>
                            </div>
                            
                            @if(!$isCorrect && $selectedOptionText && $correctOptionText)
                                <div class="mt-3 p-3 border border-yellow-200 rounded bg-yellow-50 text-sm">
                                    <div class="font-medium text-yellow-800 mb-1">Explanation:</div>
                                    <p>You selected "<span class="font-medium">{{ $selectedOptionText }}</span>" but the correct answer is "<span class="font-medium">{{ $correctOptionText }}</span>".</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Part 2 Results --}}
        @if($groupedQuestions->has(2) && $groupedQuestions[2]->count() > 0)
            <div class="mb-6" data-result-part="2">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 2 - Sentence Ordering</h3>
                @foreach($groupedQuestions[2] as $index => $question)
                    @php
                        $ans = $answers->get($question->id) ?? null;
                        $isCorrect = $ans && isset($ans->is_correct) ? $ans->is_correct : null;
                        
                        // Normalize question metadata
                        $meta = null;
                        if (is_string($question->metadata) && !empty($question->metadata)) {
                            $meta = json_decode($question->metadata, true) ?: null;
                        } elseif (is_array($question->metadata) || is_object($question->metadata)) {
                            $meta = (array) $question->metadata;
                        }
                        
                        // Extract user's answers and correct answers from metadata
                        $userAnswers = null;
                        $correctAnswers = null;
                        
                        // Get the user answer - based on part 2 format (array of ordered sentences)
                        if ($ans) {
                            // Try multiple metadata locations
                            if (isset($ans->metadata)) {
                                if (is_string($ans->metadata)) {
                                    try {
                                        $ansMeta = json_decode($ans->metadata, true);
                                        if (isset($ansMeta['user_answer']) && is_array($ansMeta['user_answer'])) {
                                            $userAnswers = $ansMeta['user_answer'];
                                        } elseif (isset($ansMeta['selected']) && is_array($ansMeta['selected'])) {
                                            $userAnswers = $ansMeta['selected'];
                                        } elseif (isset($ansMeta['answers']) && is_array($ansMeta['answers'])) {
                                            $userAnswers = $ansMeta['answers'];
                                        }
                                    } catch (\Exception $e) {
                                        // Parsing failed, continue with other methods
                                    }
                                } elseif (is_array($ans->metadata)) {
                                    if (isset($ans->metadata['user_answer']) && is_array($ans->metadata['user_answer'])) {
                                        $userAnswers = $ans->metadata['user_answer'];
                                    } elseif (isset($ans->metadata['selected']) && is_array($ans->metadata['selected'])) {
                                        $userAnswers = $ans->metadata['selected'];
                                    } elseif (isset($ans->metadata['answers']) && is_array($ans->metadata['answers'])) {
                                        $userAnswers = $ans->metadata['answers'];
                                    }
                                }
                            }
                        }
                        
                        // Get the correct order from metadata
                        if ($ans && isset($ans->metadata)) {
                            if (is_array($ans->metadata)) {
                                if (isset($ans->metadata['correct_answer']) && is_array($ans->metadata['correct_answer'])) {
                                    $correctAnswers = $ans->metadata['correct_answer'];
                                } elseif (isset($ans->metadata['correct']) && is_array($ans->metadata['correct'])) {
                                    $correctAnswers = $ans->metadata['correct'];
                                }
                            } elseif (is_string($ans->metadata)) {
                                try {
                                    $ansMeta = json_decode($ans->metadata, true);
                                    if (isset($ansMeta['correct_answer']) && is_array($ansMeta['correct_answer'])) {
                                        $correctAnswers = $ansMeta['correct_answer'];
                                    } elseif (isset($ansMeta['correct']) && is_array($ansMeta['correct'])) {
                                        $correctAnswers = $ansMeta['correct'];
                                    }
                                } catch (\Exception $e) {
                                    // Parsing failed, continue with other methods
                                }
                            }
                        }
                        
                        // Fallback to question metadata if we couldn't find from the answer
                        if ($correctAnswers === null) {
                            if (isset($meta['correct_order']) && is_array($meta['correct_order'])) {
                                // Use the correct_order from question metadata (new format)
                                $correctAnswers = $meta['correct_order'];
                            } elseif (isset($meta['correct']) && is_array($meta['correct'])) {
                                // Older format may use "correct" key
                                $correctAnswers = $meta['correct'];
                            } elseif (isset($meta['correct_answers']) && is_array($meta['correct_answers'])) {
                                // Another possible key
                                $correctAnswers = $meta['correct_answers'];
                            } elseif (isset($meta['answers']) && is_array($meta['answers'])) {
                                // Yet another possible key
                                $correctAnswers = $meta['answers'];
                            }
                        }
                        
                        // Get sentences/options for display
                        $sentences = $meta['sentences'] ?? [];
                        if (empty($sentences) && isset($meta['items']) && is_array($meta['items'])) {
                            $sentences = $meta['items'];
                        }
                        
                        // For Part 2 display badge: check if ALL positions are correct
                        // (This is just for display - scoring remains 1 question = 1 point)
                        $displayCorrect = $isCorrect; // Default to database value
                        if (is_array($userAnswers) && is_array($correctAnswers) && count($userAnswers) > 0 && count($correctAnswers) > 0) {
                            $allPositionsCorrect = true;
                            foreach($userAnswers as $pos => $userAnswer) {
                                if (!isset($correctAnswers[$pos]) || $correctAnswers[$pos] != $userAnswer) {
                                    $allPositionsCorrect = false;
                                    break;
                                }
                            }
                            $displayCorrect = $allPositionsCorrect;
                        }
                    @endphp
                    
                    <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                        <div class="flex items-start justify-between">
                            <div class="font-semibold flex items-center gap-2">
                                <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
                                @if($displayCorrect == true)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
                                        <span>Đúng</span>
                                    </span>
                                @elseif($displayCorrect == false)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95A1 1 0 013.636 14.95L8.586 10 3.636 5.05A1 1 0 015.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
                                        <span>Sai</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="prose mt-2">{!! $question->content !!}</div>
                        
                        <!-- Add inline feedback container -->
                        <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>
                        
                        <div class="mt-4">
                            @if(is_array($sentences) && count($sentences) > 0 && is_array($correctAnswers))
                                <h4 class="font-semibold mb-3">Kết quả sắp xếp câu</h4>
                                @include('student.reading.result_parts.full_random_part2', [
                                    'sentences' => $sentences,
                                    'correctAnswers' => $correctAnswers, 
                                    'userAnswers' => $userAnswers
                                ])
                            @else
                                <div class="p-4 border rounded-lg bg-red-50 text-center">
                                    <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                    <p class="text-red-600 font-medium">No answer provided</p>
                                    <p class="text-red-500 text-sm mt-1">You did not submit an answer for this question.</p>
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">

        {{-- Part 3 Results --}}
        @if($groupedQuestions->has(3) && $groupedQuestions[3]->count() > 0)
            <div class="mb-6" data-result-part="3">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 3 - Category Matching</h3>
                <div class="text-gray-600 text-sm mb-4">
                    <p class="mb-2">Đọc văn bản và ghép các câu trả lời vào nhóm phù hợp A, B, C hoặc D.</p>
                </div>
                @foreach($groupedQuestions[3] as $index => $question)
                    @php
                        $ans = $answers->get($question->id);
                        $meta = [];
                        if (is_string($question->metadata)) {
                            $meta = json_decode($question->metadata, true) ?: [];
                        } elseif (is_array($question->metadata)) {
                            $meta = $question->metadata;
                        }

                        // Part 3: Kiểm tra tất cả các câu con có đúng không
                        $isCorrect = false;
                        if ($ans) {
                            $userAnswers = [];
                            $correctAnswers = [];
                            
                            // Get user answers
                            if (isset($ans->metadata)) {
                                if (is_string($ans->metadata)) {
                                    try {
                                        $ansMeta = json_decode($ans->metadata, true);
                                        if (isset($ansMeta['user_answer'])) {
                                            $userAnswers = is_array($ansMeta['user_answer']) ? $ansMeta['user_answer'] : [$ansMeta['user_answer']];
                                        } elseif (isset($ansMeta['selected'])) {
                                            $userAnswers = is_array($ansMeta['selected']) ? $ansMeta['selected'] : [$ansMeta['selected']];
                                        }
                                    } catch (\Exception $e) {}
                                } elseif (is_array($ans->metadata)) {
                                    if (isset($ans->metadata['user_answer'])) {
                                        $userAnswers = is_array($ans->metadata['user_answer']) ? $ans->metadata['user_answer'] : [$ans->metadata['user_answer']];
                                    } elseif (isset($ans->metadata['selected'])) {
                                        $userAnswers = is_array($ans->metadata['selected']) ? $ans->metadata['selected'] : [$ans->metadata['selected']];
                                    }
                                }
                            }

                            // Get correct answers
                            if (isset($ans->metadata)) {
                                if (is_string($ans->metadata)) {
                                    try {
                                        $ansMeta = json_decode($ans->metadata, true);
                                        if (isset($ansMeta['correct_answer'])) {
                                            $correctAnswers = is_array($ansMeta['correct_answer']) ? $ansMeta['correct_answer'] : [$ansMeta['correct_answer']];
                                        }
                                    } catch (\Exception $e) {}
                                } elseif (is_array($ans->metadata)) {
                                    if (isset($ans->metadata['correct_answer'])) {
                                        $correctAnswers = is_array($ans->metadata['correct_answer']) ? $ans->metadata['correct_answer'] : [$ans->metadata['correct_answer']];
                                    }
                                }
                            }

                            // Kiểm tra từng câu
                            $allCorrect = true;
                            foreach ($userAnswers as $index => $userAnswer) {
                                if (!isset($correctAnswers[$index]) || 
                                    (is_numeric($userAnswer) && is_numeric($correctAnswers[$index]) && 
                                     intval($userAnswer) !== intval($correctAnswers[$index])) ||
                                    (is_string($userAnswer) && is_string($correctAnswers[$index]) && 
                                     strtoupper(trim($userAnswer)) !== strtoupper(trim($correctAnswers[$index])))) {
                                    $allCorrect = false;
                                    break;
                                }
                            }
                            $isCorrect = $allCorrect && count($userAnswers) > 0 && count($userAnswers) === count($correctAnswers);
                        }
                        
                        $categories = isset($meta['categories']) ? $meta['categories'] : [];
                        $options = isset($meta['options']) ? $meta['options'] : [];
                        
                        $userAnswers = [];
                        if ($ans && isset($ans->metadata) && is_array($ans->metadata)) {
                            if (isset($ans->metadata['user_answer'])) {
                                $userAnswers = $ans->metadata['user_answer'];
                            } elseif (isset($ans->metadata['selected'])) {
                                $userAnswers = $ans->metadata['selected'];
                            }
                        }
                        
                        // Get correct answers
                        $correctAnswers = [];
                        if ($ans && isset($ans->metadata) && is_array($ans->metadata)) {
                            if (isset($ans->metadata['correct_answer'])) {
                                $correctAnswers = $ans->metadata['correct_answer'];
                            } elseif (isset($ans->metadata['correct'])) {
                                $correctAnswers = $ans->metadata['correct'];
                            } elseif (isset($meta['correct_answer'])) {
                                $correctAnswers = $meta['correct_answer'];
                            } elseif (isset($meta['correct'])) {
                                $correctAnswers = $meta['correct'];
                            }
                        }
                        
                        // Create option text mapping
                        $optionTextMap = [];
                        foreach ($options as $idx => $opt) {
                            $optionTextMap[$idx] = is_array($opt) ? (isset($opt['text']) ? $opt['text'] : '') : $opt;
                        }
                    @endphp
                    
                    <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                        <div class="flex items-start justify-between">
                            <div class="font-semibold flex items-center gap-2">
                                <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
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
                                @endif
                            </div>
                        </div>
                        
                        <div class="prose mt-2 mb-4">
                            {!! $question->content !!}
                        </div>
                        
                        <!-- Add inline feedback container -->
                        <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>
                        
                        <div class="mt-4">
                            <h4 class="font-semibold mb-3">Question Content</h4>
                            
                            {{-- Display Question and Options --}}
                            <div class="space-y-6">
                                <div class="bg-white border rounded-lg overflow-hidden">
                                    <div class="px-4 py-3 bg-gray-50 border-b">
                                        <h5 class="font-medium text-gray-700">Categories</h5>
                                    </div>
                                    <div class="p-4">
                                        <div class="max-w-3xl mx-auto grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            @foreach(['A', 'B', 'C', 'D'] as $cat)
                                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                                                    <div class="font-medium text-blue-900 mb-1">Category {{ $cat }}</div>
                                                    <div class="text-sm text-blue-800">{{ $meta['items'][$loop->index]['text'] ?? '' }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                {{-- Display Answers --}}
                                <div class="bg-white border rounded-lg overflow-hidden">
                                    <div class="px-4 py-3 bg-gray-50 border-b">
                                        <h5 class="font-medium text-gray-700">Your Answers</h5>
                                    </div>
                                    <div class="p-4">
                                        <div class="max-w-3xl mx-auto space-y-3">
                                            @foreach($options as $index => $text)
                                                @php
                                                    // Get user's answer
                                                    $selectedCategory = $ans->metadata['user_answer'][$index] ?? 'No Answer';
                                                    
                                                    // Get correct category
                                                    $correctCategory = null;
                                                    foreach(['A', 'B', 'C', 'D'] as $cat) {
                                                        if (isset($ans->metadata['answers'][$cat]) && in_array($index, $ans->metadata['answers'][$cat])) {
                                                            $correctCategory = $cat;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    // Check if answer is correct
                                                    $isCorrect = $selectedCategory === $correctCategory;

                                                    // Convert user's numeric answer to A,B,C,D
                                                    $userAnswer = $selectedCategory === 'No Answer' ? '-' : chr(65 + (int)$ans->metadata['user_answer'][$index]);
                                                @endphp
                                            
                                            @php
                                                // Chuyển numeric index thành chữ cái A/B/C/D
                                                if (is_numeric($selectedCategory)) {
                                                    $selectedCategory = chr(65 + intval($selectedCategory));
                                                }

                                                // So sánh chính xác (bỏ qua case)
                                                $isAnswerCorrect = $selectedCategory !== 'No Answer' && 
                                                                $correctCategory && 
                                                                strtoupper($selectedCategory) === strtoupper($correctCategory);
                                            @endphp

                                            <div class="flex items-center p-3 {{ $isAnswerCorrect ? 'bg-green-50 border border-green-200' : 'bg-white border border-gray-200' }} rounded-lg">
                                                {{-- Option number and text --}}
                                                <div class="flex-grow">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-medium {{ $isAnswerCorrect ? 'text-green-700' : 'text-gray-700' }}">{{ chr(65 + $index) }}.</span>
                                                        <span class="text-gray-900">{{ $text }}</span>
                                                    </div>
                                                </div>

                                                {{-- Answer comparison --}}
                                                <div class="flex items-center gap-4">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-sm {{ $isAnswerCorrect ? 'text-green-600' : 'text-gray-600' }}">Your answer:</span>
                                                        <span class="font-medium {{ $isAnswerCorrect ? 'text-green-700' : 'text-gray-700' }}">
                                                            {{ $selectedCategory === 'No Answer' ? '-' : $selectedCategory }}
                                                        </span>
                                                    </div>
                                                    
                                                    @if(!$isAnswerCorrect)
                                                        <div class="flex items-center gap-2">
                                                            <span class="text-sm text-green-600">Correct answer:</span>
                                                            <span class="font-medium text-green-700">
                                                                {{ $correctCategory }}
                                                            </span>
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
        @if($groupedQuestions->has(4) && $groupedQuestions[4]->count() > 0)
            <div class="mb-6" data-result-part="4">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 4 - Paragraph Ordering</h3>
                @foreach($groupedQuestions[4] as $index => $question)
                    @php
                        $ans = $answers->get($question->id) ?? null;
                        $isCorrect = $ans && isset($ans->is_correct) ? $ans->is_correct : null;
                        
                        // Normalize question metadata
                        $meta = null;
                        if (is_string($question->metadata) && !empty($question->metadata)) {
                            $meta = json_decode($question->metadata, true) ?: null;
                        } elseif (is_array($question->metadata) || is_object($question->metadata)) {
                            $meta = (array) $question->metadata;
                        }
                        
                        // Get the paragraph text content
                        $paragraphs = $meta['paragraphs'] ?? [];
                        
                        // Get user's answers and correct answers for paragraph ordering
                        $userAnswers = null;
                        if ($ans && isset($ans->metadata) && is_array($ans->metadata)) {
                            if (isset($ans->metadata['user_answer']) && is_array($ans->metadata['user_answer'])) {
                                $userAnswers = $ans->metadata['user_answer'];
                            } elseif (isset($ans->metadata['selected']) && is_array($ans->metadata['selected'])) {
                                $userAnswers = $ans->metadata['selected'];
                            }
                        }
                        
                        // Get correct order - from various possible metadata formats
                        $correctAnswers = null;
                        if ($ans && isset($ans->metadata) && is_array($ans->metadata) && isset($ans->metadata['correct_answer'])) {
                            $correctAnswers = $ans->metadata['correct_answer'];
                        }
                        // Fallback to question metadata
                        if ($correctAnswers === null) {
                            if (isset($meta['correct']) && is_array($meta['correct'])) {
                                $correctAnswers = $meta['correct'];
                            } elseif (isset($meta['answers']) && is_array($meta['answers'])) {
                                $correctAnswers = $meta['answers'];
                            } elseif (isset($meta['correct_answers']) && is_array($meta['correct_answers'])) {
                                $correctAnswers = $meta['correct_answers'];
                            } elseif (isset($meta['correct_order']) && is_array($meta['correct_order'])) {
                                $correctAnswers = $meta['correct_order'];
                            }
                        }
                        
                        // Determine if this is a paragraph ordering question or a multiple-choice question
                        $isParagraphOrdering = is_array($paragraphs) && count($paragraphs) > 0;
                        
                        // If not paragraph ordering, get multiple choice question data
                        $questions = $meta['questions'] ?? [];
                        $options = $meta['options'] ?? [];
                        
                        // Calculate individual score for this question
                        $individualCorrect = 0;
                        $individualTotal = 0;
                        
                        if ($isParagraphOrdering && is_array($paragraphs) && count($paragraphs) > 0) {
                            // For paragraph ordering, count correct positions
                            if (is_array($userAnswers) && is_array($correctAnswers)) {
                                $individualTotal = count($userAnswers);
                                foreach($userAnswers as $pos => $paragraphId) {
                                    if (isset($correctAnswers[$pos]) && $correctAnswers[$pos] == $paragraphId) {
                                        $individualCorrect++;
                                    }
                                }
                            }
                        } elseif (is_array($questions) && count($questions) > 0) {
                            // For multiple choice questions, count correct answers
                            if (is_array($userAnswers) && is_array($correctAnswers)) {
                                $individualTotal = count($questions);
                                foreach($questions as $qidx => $qtext) {
                                    $userAnswer = isset($userAnswers[$qidx]) ? $userAnswers[$qidx] : null;
                                    $correctAnswer = isset($correctAnswers[$qidx]) ? $correctAnswers[$qidx] : null;
                                    if ($userAnswer !== null && $correctAnswer !== null && (string)$userAnswer === (string)$correctAnswer) {
                                        $individualCorrect++;
                                    }
                                }
                            }
                        }
                        
                        $individualPercentage = $individualTotal > 0 ? round(($individualCorrect / $individualTotal) * 100) : 0;
                    @endphp
                    
                    <div class="mb-4 p-4 border rounded border-gray-200 bg-white question-block" data-qid="{{ $question->id }}">
                        <div class="flex items-start justify-between">
                            <div class="font-semibold flex items-center gap-2">
                                <span class="text-sm font-medium">Question {{ $index + 1 }}</span>
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
                                @endif
                            </div>
                        </div>
                        
                        <div class="prose mt-2 mb-4">
                            {!! $question->content !!}
                        </div>
                        
                        <!-- Add inline feedback container -->
                        <div class="inline-feedback mt-2" data-qid-feedback="{{ $question->id }}"></div>
                        
                        @if($isParagraphOrdering && is_array($paragraphs) && count($paragraphs) > 0)
                            {{-- Paragraph ordering display --}}
                            <div class="mt-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                                    {{-- Correct Order --}}
                                    <div class="p-4 border rounded bg-green-50 border-green-200">
                                        <h4 class="font-semibold mb-3 text-green-800 flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Correct Order
                                        </h4>
                                        <ol class="list-decimal pl-5 space-y-3">
                                            @foreach($correctAnswers as $index => $paragraphId)
                                                @php
                                                    $paragraphText = '';
                                                    // Get paragraph text based on the ID
                                                    if (is_numeric($paragraphId) && isset($paragraphs[$paragraphId])) {
                                                        if (is_array($paragraphs[$paragraphId]) && isset($paragraphs[$paragraphId]['text'])) {
                                                            $paragraphText = $paragraphs[$paragraphId]['text'];
                                                        } elseif (is_string($paragraphs[$paragraphId])) {
                                                            $paragraphText = $paragraphs[$paragraphId];
                                                        }
                                                    }
                                                    
                                                    // Truncate text if it's too long
                                                    if (strlen($paragraphText) > 120) {
                                                        $paragraphText = substr($paragraphText, 0, 120) . '...';
                                                    }
                                                @endphp
                                                <li class="p-3 bg-white rounded border border-green-300 shadow-sm">
                                                    <div class="text-sm text-gray-700 leading-relaxed">{{ $paragraphText }}</div>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    
                                    {{-- User's Order --}}
                                    @if(is_array($userAnswers) && count($userAnswers) > 0)
                                        @php
                                            // Calculate individual correctness for paragraph ordering
                                            $correctPositions = 0;
                                            $totalPositions = count($userAnswers);
                                            foreach($userAnswers as $pos => $paragraphId) {
                                                if (isset($correctAnswers[$pos]) && $correctAnswers[$pos] == $paragraphId) {
                                                    $correctPositions++;
                                                }
                                            }
                                            $isOrderingCorrect = $correctPositions == $totalPositions;
                                        @endphp
                                        
                                        <div class="p-4 border rounded {{ $isOrderingCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                            <h4 class="font-semibold mb-3 {{ $isOrderingCorrect ? 'text-green-800' : 'text-red-800' }} flex items-center">
                                                @if($isOrderingCorrect)
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                @endif
                                                Your Order ({{ $correctPositions }}/{{ $totalPositions }} correct)
                                            </h4>
                                            <ol class="list-decimal pl-5 space-y-3">
                                                @foreach($userAnswers as $index => $paragraphId)
                                                    @php
                                                        $paragraphText = '';
                                                        $isCorrectPosition = false;
                                                        
                                                        // Check if this position has the correct paragraph
                                                        if (isset($correctAnswers[$index]) && $correctAnswers[$index] == $paragraphId) {
                                                            $isCorrectPosition = true;
                                                        }
                                                        
                                                        // Get paragraph text
                                                        if (is_numeric($paragraphId) && isset($paragraphs[$paragraphId])) {
                                                            if (is_array($paragraphs[$paragraphId]) && isset($paragraphs[$paragraphId]['text'])) {
                                                                $paragraphText = $paragraphs[$paragraphId]['text'];
                                                            } elseif (is_string($paragraphs[$paragraphId])) {
                                                                $paragraphText = $paragraphs[$paragraphId];
                                                            }
                                                        }
                                                        
                                                        // Truncate text if it's too long
                                                        if (strlen($paragraphText) > 120) {
                                                            $paragraphText = substr($paragraphText, 0, 120) . '...';
                                                        }
                                                    @endphp
                                                    <li class="p-3 rounded border shadow-sm {{ $isCorrectPosition ? 'bg-white border-green-300' : 'bg-white border-red-300' }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="text-sm text-gray-700 leading-relaxed flex-1">{{ $paragraphText }}</div>
                                                            <div class="ml-2 flex-shrink-0">
                                                                @if($isCorrectPosition)
                                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 text-white text-xs">
                                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-2.293-2.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                    </span>
                                                                @else
                                                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-500 text-white text-xs">
                                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <div class="p-4 border rounded bg-gray-50 border-gray-200">
                                            <h4 class="font-semibold mb-3 text-gray-600 flex items-center">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                                </svg>
                                                Your Order (0/0 answered)
                                            </h4>
                                            <div class="flex items-center justify-center py-8">
                                                <div class="text-center">
                                                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <p class="text-gray-600 font-medium">No answer provided</p>
                                                    <p class="text-gray-500 text-sm mt-1">You did not submit an answer for this question.</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif(is_array($questions) && count($questions) > 0)
                            {{-- Multiple choice display for Part 4 (reading comprehension) --}}
                            <div class="mt-4">
                                <h4 class="font-semibold mb-4 text-gray-800">Reading Comprehension Questions</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($questions as $qidx => $qtext)
                                        @php
                                            $userAnswer = is_array($userAnswers) && isset($userAnswers[$qidx]) ? $userAnswers[$qidx] : null;
                                            $correctAnswer = is_array($correctAnswers) && isset($correctAnswers[$qidx]) ? $correctAnswers[$qidx] : null;
                                            $isQCorrect = $userAnswer !== null && $correctAnswer !== null && (string)$userAnswer === (string)$correctAnswer;
                                            
                                            // Get text representations
                                            $userAnswerText = null;
                                            if (is_numeric($userAnswer) && isset($options[$userAnswer])) {
                                                $userAnswerText = $options[$userAnswer];
                                            } elseif (is_string($userAnswer)) {
                                                $userAnswerText = $userAnswer;
                                            }
                                            
                                            $correctAnswerText = null;
                                            if (is_numeric($correctAnswer) && isset($options[$correctAnswer])) {
                                                $correctAnswerText = $options[$correctAnswer];
                                            } elseif (is_string($correctAnswer)) {
                                                $correctAnswerText = $correctAnswer;
                                            }
                                            
                                            // If qtext is an array or object with 'text' property, use that
                                            $questionText = $qtext;
                                            if (is_array($qtext) && isset($qtext['text'])) {
                                                $questionText = $qtext['text'];
                                            } elseif (is_object($qtext) && isset($qtext->text)) {
                                                $questionText = $qtext->text;
                                            }
                                        @endphp
                                        
                                        <div class="p-4 border rounded shadow-sm {{ $isQCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                            <div class="mb-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-semibold text-sm {{ $isQCorrect ? 'text-green-800' : 'text-red-800' }}">
                                                        Question {{ $qidx + 1 }}
                                                    </span>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $isQCorrect ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                        @if($isQCorrect)
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-2.293-2.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Correct
                                                        @else
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Incorrect
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="text-sm text-gray-700 leading-relaxed">{{ $questionText }}</div>
                                            </div>
                                            <div class="space-y-2">
                                                <div class="p-2 bg-white rounded border">
                                                    <div class="text-xs font-medium text-blue-600 mb-1">Your Answer:</div>
                                                    <div class="text-sm {{ $isQCorrect ? 'text-green-700' : 'text-red-700' }} font-medium">
                                                        {{ $userAnswerText ?? 'Not answered' }}
                                                    </div>
                                                </div>
                                                @if(!$isQCorrect)
                                                <div class="p-2 bg-green-50 rounded border border-green-200">
                                                    <div class="text-xs font-medium text-green-700 mb-1">Correct Answer:</div>
                                                    <div class="text-sm text-green-800 font-medium">{{ $correctAnswerText }}</div>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
    (function(){
        try {
            // Get answers from localStorage or window global
            const key = 'attempt_answers_{{ $attempt->id }}';
            let answers = null;
            
            try {
                const raw = localStorage.getItem(key);
                if (raw) {
                    answers = JSON.parse(raw); 
                }
            } catch(e) {}
            
            if (!answers && typeof window.attemptAnswers !== 'undefined') {
                answers = window.attemptAnswers;
            }
            
            answers = answers || {};
            window.attemptAnswers = answers;
            
            // Helper functions
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

            // Process questions
            const qEls = Array.from(document.querySelectorAll('.question-block[data-qid]'));
            
            // Add part attributes if missing
            qEls.forEach(qEl => {
                if (!qEl.hasAttribute('data-part')) {
                    const parent = qEl.closest('[data-result-part]');
                    if (parent) {
                        qEl.setAttribute('data-part', parent.getAttribute('data-result-part')); 
                    }
                }
            });

            // Process answers
            qEls.forEach(qEl => {
                try {
                    const qid = qEl.getAttribute('data-qid');
                    const saved = answers[qid];
                    if (!saved) return;

                    let part = parseInt(qEl.getAttribute('data-part')) || 
                             (saved.metadata?.part ? parseInt(saved.metadata.part) : 4);
                             
                    const payload = processAnswer(saved, part);
                    const is_correct = saved.is_correct ?? null;
                    
                    // Show feedback if helpers exist
                    if (window.readingPartHelper?.showFeedback) {
                        try {
                            window.readingPartHelper.showFeedback(qid, payload);
                        } catch(e) {
                            try {
                                window.readingPartHelper.showFeedback(qid, is_correct, {
                                    part,
                                    is_correct,
                                    user_answer: payload
                                });
                            } catch(e) {}
                        }
                    }
                    
                    // Save to helper if exists
                    if (window.readingPartHelper?.saveAnswer) {
                        try {
                            window.readingPartHelper.saveAnswer(qid, payload, {
                                is_correct,
                                part
                            }, {{ $attempt->id }});
                        } catch(e) {}
                    }
                    
                } catch(e) {}
            });

        } catch(e) {}
    })();
</script>
@endpush