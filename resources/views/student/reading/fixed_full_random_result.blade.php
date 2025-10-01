@extends('layouts.app')

@section('title', 'Kết quả Full Random Reading')

@section('content')
<div class="container mx-auto py-6">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold">Full Random Reading Test</h2>
                <p class="text-sm text-gray-500">Results</p>
            </div>
            <div class="text-right">
                <div class="text-xl font-bold {{ ($attempt->score_percentage ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ number_format($attempt->score_percentage ?? 0, 2) }}%
                </div>
                <div class="text-sm font-medium">
                    {{ $attempt->correct_answers ?? 0 }} / {{ $attempt->total_questions ?? count($questions) }}
                </div>
            </div>
        </div>

        <hr class="my-4">
        
        {{-- Include helper for result page to render inline-feedback --}}
        @includeIf('student.reading.parts._check_helper')

        {{-- Part 1 Results --}}
        @if($groupedQuestions->has(1) && $groupedQuestions[1]->count() > 0)
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 1 - Multiple Choice</h3>
                @foreach($groupedQuestions[1] as $index => $question)
                    @php
                        $ans = $answers->get($question->id) ?? null;
                        $isCorrect = $ans && isset($ans->is_correct) ? $ans->is_correct : null;
                        $meta = null;
                        
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
                        
                        <div class="mt-3">
                            <h4 class="font-semibold mb-2">Results</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="p-3 border rounded {{ $isCorrect ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                    <div class="text-sm font-medium mb-1">Your answer:</div>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full {{ $isCorrect ? 'bg-green-500' : 'bg-red-500' }} text-white text-xs mr-2">
                                            @if($isCorrect)
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-2.293-2.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"></path></svg>
                                            @else
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                            @endif
                                        </span>
                                        <span class="font-medium {{ $isCorrect ? 'text-green-700' : 'text-red-700' }}">
                                            {{ $selectedOptionText ?? 'Not answered' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-3 border rounded bg-green-50 border-green-200">
                                    <div class="text-sm font-medium mb-1">Correct answer:</div>
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-green-500 text-white text-xs mr-2">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586l-2.293-2.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l8-8a1 1 0 000-1.414z" clip-rule="evenodd"></path></svg>
                                        </span>
                                        <span class="font-medium text-green-700">
                                            {{ $correctOptionText ?? 'Unknown' }}
                                        </span>
                                    </div>
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
            <div class="mb-6">
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
                        
                        <div class="mt-4">
                            @if(is_array($sentences) && count($sentences) > 0 && is_array($correctAnswers))
                                <h4 class="font-semibold mb-3">Sentence Ordering Results</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- Correct Order --}}
                                    <div class="border rounded-lg overflow-hidden">
                                        <div class="bg-green-100 p-3 border-b">
                                            <h5 class="font-semibold text-green-800 flex items-center">
                                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                Correct Order
                                            </h5>
                                        </div>
                                        <ol class="divide-y">
                                            @foreach($correctAnswers as $index => $sentenceId)
                                                @php
                                                    $sentenceText = '';
                                                    if (is_numeric($sentenceId) && isset($sentences[$sentenceId])) {
                                                        if (is_array($sentences[$sentenceId]) && isset($sentences[$sentenceId]['text'])) {
                                                            $sentenceText = $sentences[$sentenceId]['text'];
                                                        } elseif (is_string($sentences[$sentenceId])) {
                                                            $sentenceText = $sentences[$sentenceId];
                                                        }
                                                    } elseif (is_string($sentenceId) && isset($sentences[$sentenceId])) {
                                                        if (is_array($sentences[$sentenceId]) && isset($sentences[$sentenceId]['text'])) {
                                                            $sentenceText = $sentences[$sentenceId]['text'];
                                                        } elseif (is_string($sentences[$sentenceId])) {
                                                            $sentenceText = $sentences[$sentenceId];
                                                        }
                                                    }
                                                @endphp
                                                <li class="p-3 bg-white flex">
                                                    <div class="flex-shrink-0 bg-green-100 text-green-600 font-bold rounded-full w-7 h-7 flex items-center justify-center mr-3">
                                                        {{ $index + 1 }}
                                                    </div>
                                                    <div>{{ $sentenceText }}</div>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    
                                    {{-- User's Order --}}
                                    @if(is_array($userAnswers) && count($userAnswers) > 0)
                                        <div class="border rounded-lg overflow-hidden">
                                            <div class="bg-blue-100 p-3 border-b">
                                                <h5 class="font-semibold text-blue-800 flex items-center">
                                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                                                    Your Order
                                                </h5>
                                            </div>
                                            <ol class="divide-y">
                                                @foreach($userAnswers as $index => $sentenceId)
                                                    @php
                                                        $sentenceText = '';
                                                        $isCorrectPosition = false;
                                                        
                                                        // Check if this position has the correct sentence
                                                        if (isset($correctAnswers[$index]) && $correctAnswers[$index] == $sentenceId) {
                                                            $isCorrectPosition = true;
                                                        }
                                                        
                                                        // Get the text of this sentence
                                                        if (is_numeric($sentenceId) && isset($sentences[$sentenceId])) {
                                                            if (is_array($sentences[$sentenceId]) && isset($sentences[$sentenceId]['text'])) {
                                                                $sentenceText = $sentences[$sentenceId]['text'];
                                                            } elseif (is_string($sentences[$sentenceId])) {
                                                                $sentenceText = $sentences[$sentenceId];
                                                            }
                                                        } elseif (is_string($sentenceId) && isset($sentences[$sentenceId])) {
                                                            if (is_array($sentences[$sentenceId]) && isset($sentences[$sentenceId]['text'])) {
                                                                $sentenceText = $sentences[$sentenceId]['text'];
                                                            } elseif (is_string($sentences[$sentenceId])) {
                                                                $sentenceText = $sentences[$sentenceId];
                                                            }
                                                        }
                                                    @endphp
                                                    <li class="p-3 bg-white flex items-start">
                                                        <div class="flex-shrink-0 {{ $isCorrectPosition ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }} font-bold rounded-full w-7 h-7 flex items-center justify-center mr-3">
                                                            {{ $index + 1 }}
                                                        </div>
                                                        <div class="flex-grow">
                                                            {{ $sentenceText }}
                                                            @if($isCorrectPosition)
                                                                <div class="mt-1 text-sm text-green-600 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                                    Correct position
                                                                </div>
                                                            @else
                                                                <div class="mt-1 text-sm text-red-600 flex items-center">
                                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                                                    Incorrect position
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="flex-shrink-0 ml-2">
                                                            @if($isCorrectPosition)
                                                                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                                            @else
                                                                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                                            @endif
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <div class="p-4 border rounded-lg bg-red-50 text-center">
                                            <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                            <p class="text-red-600 font-medium">No answer provided</p>
                                            <p class="text-red-500 text-sm mt-1">You did not submit an answer for this question.</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="p-4 border rounded-lg bg-red-50 text-center">
                                    <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                    <p class="text-red-600 font-medium">Answer data unavailable</p>
                                    <p class="text-red-500 text-sm mt-1">The required data for this question could not be found.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Part 3 Results --}}
        @if($groupedQuestions->has(3) && $groupedQuestions[3]->count() > 0)
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-3 pb-2 border-b">Part 3 - Category Matching</h3>
                @foreach($groupedQuestions[3] as $index => $question)
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
                        
                        // Extract items and options from metadata - items might be from categories
                        $categories = $meta['categories'] ?? [];
                        $options = $meta['options'] ?? [];
                        $items = $meta['items'] ?? [];
                        
                        // In part 3, answers are usually organized as categories mapping to arrays of option indices
                        // Get user answers (mapping of categories to option arrays)
                        $userAnswers = [];
                        if ($ans && isset($ans->metadata) && is_array($ans->metadata)) {
                            if (isset($ans->metadata['user_answer']) && is_array($ans->metadata['user_answer'])) {
                                $userAnswers = $ans->metadata['user_answer'];
                            } elseif (isset($ans->metadata['selected']) && is_array($ans->metadata['selected'])) {
                                $userAnswers = $ans->metadata['selected'];
                            }
                        }
                        
                        // Get the correct answers - first from attempt metadata if available
                        $correctAnswers = [];
                        if ($ans && isset($ans->metadata) && is_array($ans->metadata) && isset($ans->metadata['correct_answer'])) {
                            $correctAnswers = $ans->metadata['correct_answer'];
                        } elseif (isset($ans->metadata['answers']) && is_array($ans->metadata['answers'])) {
                            $correctAnswers = $ans->metadata['answers'];
                        }
                        // Fallback to question metadata if not found in attempt
                        if (empty($correctAnswers)) {
                            if (!empty($meta['answers']) && is_array($meta['answers'])) {
                                $correctAnswers = $meta['answers'];
                            } elseif (!empty($meta['correct_answers']) && is_array($meta['correct_answers'])) {
                                $correctAnswers = $meta['correct_answers'];
                            } elseif (!empty($meta['correct']) && is_array($meta['correct'])) {
                                $correctAnswers = $meta['correct'];
                            }
                        }
                        
                        // Create a text mapping of options for display
                        $optionTextMap = [];
                        foreach ($options as $idx => $option) {
                            if (is_array($option) && isset($option['text'])) {
                                $optionTextMap[$idx] = $option['text'];
                            } elseif (is_string($option)) {
                                $optionTextMap[$idx] = $option;
                            }
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
                        
                        <div class="mt-4">
                            <div class="grid grid-cols-2 gap-6">
                                {{-- Display Categories --}}
                                <div class="space-y-3">
                                    <h4 class="font-semibold">Categories:</h4>
                                    <div class="space-y-2">
                                        @foreach($categories as $catId => $catText)
                                            @php
                                                $categoryText = '';
                                                if (is_array($catText) && isset($catText['text'])) {
                                                    $categoryText = $catText['text'];
                                                } elseif (is_string($catText)) {
                                                    $categoryText = $catText;
                                                }
                                            @endphp
                                            <div class="p-2 border rounded">
                                                <div class="font-medium">{{ $categoryText }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                
                                {{-- Display Options --}}
                                <div class="space-y-3">
                                    <h4 class="font-semibold">Options:</h4>
                                    <div class="space-y-2">
                                        @foreach($options as $optId => $optText)
                                            @php
                                                $optionText = '';
                                                if (is_array($optText) && isset($optText['text'])) {
                                                    $optionText = $optText['text'];
                                                } elseif (is_string($optText)) {
                                                    $optionText = $optText;
                                                }
                                            @endphp
                                            <div class="p-2 border rounded">
                                                <div class="text-sm">{{ $optionText }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Display Results --}}
                            <div class="mt-6">
                                <h4 class="font-semibold mb-3">Results:</h4>
                                <div class="space-y-4">
                                    @foreach($categories as $catId => $catText)
                                        @php
                                            $categoryText = '';
                                            if (is_array($catText) && isset($catText['text'])) {
                                                $categoryText = $catText['text'];
                                            } elseif (is_string($catText)) {
                                                $categoryText = $catText;
                                            }
                                            
                                            $correctItems = isset($correctAnswers[$catId]) ? (array)$correctAnswers[$catId] : [];
                                            $userItems = isset($userAnswers[$catId]) ? (array)$userAnswers[$catId] : [];
                                            
                                            // Determine if the user's answers for this category are correct
                                            $isCatCorrect = count($correctItems) === count($userItems);
                                            if ($isCatCorrect) {
                                                foreach ($correctItems as $item) {
                                                    if (!in_array($item, $userItems)) {
                                                        $isCatCorrect = false;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <div class="p-3 border rounded {{ $isCatCorrect ? 'bg-green-50' : 'bg-red-50' }}">
                                            <div class="font-semibold mb-2">{{ $categoryText }}</div>
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <div class="font-medium text-sm">Correct items:</div>
                                                    <div class="space-y-1 mt-1">
                                                        @foreach($correctItems as $item)
                                                            <div class="p-1.5 bg-white border rounded">
                                                                {{ $optionTextMap[$item] ?? $item }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-sm">Your selections:</div>
                                                    <div class="space-y-1 mt-1">
                                                        @if(count($userItems) > 0)
                                                            @foreach($userItems as $item)
                                                                @php
                                                                    $isItemCorrect = in_array($item, $correctItems);
                                                                @endphp
                                                                <div class="p-1.5 border rounded {{ $isItemCorrect ? 'bg-green-50 border-green-300' : 'bg-red-50 border-red-300' }}">
                                                                    {{ $optionTextMap[$item] ?? $item }}
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="p-1.5 bg-red-50 border border-red-300 rounded">
                                                                No selections
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Part 4 Results --}}
        @if($groupedQuestions->has(4) && $groupedQuestions[4]->count() > 0)
            <div class="mb-6">
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
                        
                        @if($isParagraphOrdering && is_array($paragraphs) && count($paragraphs) > 0)
                            {{-- Paragraph ordering display --}}
                            <div class="mt-4">
                                <div class="grid grid-cols-1 gap-4 mb-4">
                                    {{-- Correct Order --}}
                                    <div class="p-3 border rounded">
                                        <h4 class="font-semibold mb-2">Correct Order:</h4>
                                        <ol class="list-decimal pl-5 space-y-2">
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
                                                    if (strlen($paragraphText) > 150) {
                                                        $paragraphText = substr($paragraphText, 0, 150) . '...';
                                                    }
                                                @endphp
                                                <li class="p-2 bg-green-50 border-l-4 border-green-500">{{ $paragraphText }}</li>
                                            @endforeach
                                        </ol>
                                    </div>
                                    
                                    {{-- User's Order --}}
                                    @if(is_array($userAnswers) && count($userAnswers) > 0)
                                        <div class="p-3 border rounded">
                                            <h4 class="font-semibold mb-2">Your Order:</h4>
                                            <ol class="list-decimal pl-5 space-y-2">
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
                                                        if (strlen($paragraphText) > 150) {
                                                            $paragraphText = substr($paragraphText, 0, 150) . '...';
                                                        }
                                                    @endphp
                                                    <li class="p-2 {{ $isCorrectPosition ? 'bg-green-50 border-l-4 border-green-500' : 'bg-red-50 border-l-4 border-red-500' }}">
                                                        {{ $paragraphText }}
                                                    </li>
                                                @endforeach
                                            </ol>
                                        </div>
                                    @else
                                        <div class="p-3 border rounded bg-red-50">
                                            <p class="text-red-600">No answer provided.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif(is_array($questions) && count($questions) > 0)
                            {{-- Multiple choice display for Part 4 (reading comprehension) --}}
                            <div class="mt-4">
                                <div class="space-y-2">
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
                                        
                                        <div class="p-2 border rounded {{ $isQCorrect ? 'bg-green-50' : 'bg-red-50' }}">
                                            <div class="mb-2">
                                                <div class="font-medium">Question {{ $qidx + 1 }}:</div>
                                                <div class="text-sm text-gray-700">{{ $questionText }}</div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-1 text-sm">
                                                <div>
                                                    <span class="font-medium">Your answer:</span> 
                                                    <span class="{{ $isQCorrect ? 'text-green-700' : 'text-red-700' }}">{{ $userAnswerText ?? 'Not answered' }}</span>
                                                </div>
                                                <div>
                                                    <span class="font-medium">Correct answer:</span> 
                                                    <span class="text-green-700">{{ $correctAnswerText }}</span>
                                                </div>
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
            // Attempt to retrieve saved answers for this attempt
            var key = 'attempt_answers_{{ $attempt->id }}';
            var raw = null;
            try { raw = localStorage.getItem(key); } catch(e) { raw = null; }
            var answers = null;
            if (raw) {
                try { answers = JSON.parse(raw); } catch(e) { answers = null; }
            }
            
            if (!answers && typeof window.attemptAnswers !== 'undefined') {
                answers = window.attemptAnswers;
            }

            // Process each question block in the DOM
            var qEls = Array.from(document.querySelectorAll('.question-block[data-qid]'));
            
            qEls.forEach(function(qEl){
                try {
                    var qid = qEl.getAttribute('data-qid');
                    var saved = (answers && answers[qid]) ? answers[qid] : null;

                    if (!saved) return;

                    var payload = saved.selected ?? saved.metadata ?? saved;
                    var is_correct = (typeof saved.is_correct !== 'undefined') ? saved.is_correct : null;

                    var metaEl = qEl ? qEl.querySelector('[data-meta-json]') : null;
                    var meta = null;
                    if (metaEl) {
                        try { meta = JSON.parse(metaEl.getAttribute('data-meta-json')); } catch(e) { meta = null; }
                    }

                    // Save into helper's memory/store if the helper exists
                    if (window.readingPartHelper && window.readingPartHelper.saveAnswer) {
                        try { 
                            window.readingPartHelper.saveAnswer(qid, payload, { is_correct: is_correct }, {{ $attempt->id }}); 
                        } catch(e){}
                    }

                    // Try to show feedback if helper exists
                    var shown = false;
                    if (window.readingPartHelper && window.readingPartHelper.showFeedback) {
                        try { 
                            window.readingPartHelper.showFeedback(qid, payload); 
                            shown = true; 
                        } catch(e) {
                            try { 
                                window.readingPartHelper.showFeedback(qid, is_correct, meta || (saved.metadata || {})); 
                                shown = true; 
                            } catch(e) { 
                                shown = false; 
                            }
                        }
                    }

                    // Fallback to inline feedback
                    if (!shown && window.inlineFeedback && qEl) {
                        try { 
                            window.inlineFeedback.show(qid, JSON.stringify(payload ?? '(No answer)'), '', ''); 
                        } catch(e){}
                    }
                } catch(e){}
            });
        } catch(e){}
    })();
</script>
@endpush