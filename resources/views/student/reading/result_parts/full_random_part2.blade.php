{{-- Part 2 Sentence Ordering Results --}}
<div class="mt-3 text-sm">
    <div class="font-medium">Kết quả Part 2 - Sentence Ordering</div>
    <div class="mt-4">
        <div class="grid grid-cols-2 gap-6">
            {{-- Correct Order --}}
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-green-100 p-3 border-b">
                    <h5 class="font-semibold text-green-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Thứ tự đúng
                    </h5>
                </div>
                <ol class="divide-y">
                    @foreach($correctAnswers as $index => $sentenceId)
                        <li class="p-3 bg-white flex">
                            <div class="flex-shrink-0 bg-green-100 text-green-600 font-bold rounded-full w-7 h-7 flex items-center justify-center mr-3">
                                {{ $index + 1 }}
                            </div>
                            <div>{{ $sentences[$sentenceId] ?? '' }}</div>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- User's Order --}}
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-blue-100 p-3 border-b">
                    <h5 class="font-semibold text-blue-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                        Thứ tự của bạn
                    </h5>
                </div>
                <ol class="divide-y">
                    @foreach($userAnswers as $index => $sentenceId)
    @php
        // Nếu $sentenceId là text thì tìm index trong $sentences
        if (!is_numeric($sentenceId)) {
            $sentenceId = array_search($sentenceId, $sentences, true);
        }

        $isCorrectPosition = isset($correctAnswers[$index]) && $correctAnswers[$index] == $sentenceId;
        $sentenceText = $sentences[$sentenceId] ?? '';
    @endphp
    <li class="p-3 bg-white flex items-start">
        <div class="flex-shrink-0 {{ $isCorrectPosition ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }} font-bold rounded-full w-7 h-7 flex items-center justify-center mr-3">
            {{ $index + 1 }}
        </div>
        <div class="flex-grow">{{ $sentenceText }}</div>
        <div class="flex-shrink-0 ml-2">
            @if($isCorrectPosition)
                <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            @else
                <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            @endif
        </div>
    </li>
@endforeach

                </ol>
            </div>
        </div>
    </div>
</div>
