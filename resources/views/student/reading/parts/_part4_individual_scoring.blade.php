{{-- Individual scoring logic for Part 4 --}}
@php
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

{{-- Status badge based on individual scoring --}}
@if($individualTotal > 0)
    @if($individualCorrect == $individualTotal)
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-green-50 text-green-800 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z" clip-rule="evenodd"/></svg>
            <span>Perfect ({{ $individualCorrect }}/{{ $individualTotal }})</span>
        </span>
    @elseif($individualCorrect > 0)
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-yellow-50 text-yellow-800 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <span>Partial ({{ $individualCorrect }}/{{ $individualTotal }} - {{ $individualPercentage }}%)</span>
        </span>
    @else
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-red-50 text-red-800 text-xs">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95A1 1 0 113.636 14.95L8.586 10 3.636 5.05A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/></svg>
            <span>Incorrect (0/{{ $individualTotal }})</span>
        </span>
    @endif
@else
    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-gray-50 text-gray-600 text-xs">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
        <span>No Answer</span>
    </span>
@endif