{{-- Part 2 Sentence Ordering Results (partial) --}}
<div class="mt-3 text-sm">
    <div class="font-medium">Kết quả Part 2 - Sentence Ordering</div>
    <div class="mt-4">
        <div class="grid grid-cols-2 gap-6">
            {{-- Correct Order --}}
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-green-100 p-3 border-b">
                    <h5 class="font-semibold text-green-800 flex items-center">
                        Thứ tự đúng
                    </h5>
                </div>
                <ol class="divide-y">
                    @foreach ($correctIndices as $pos => $sentIdx)
                        <li class="p-3 bg-white flex">
                            <div
                                class="flex-shrink-0 bg-green-100 text-green-600 font-bold rounded-full w-7 h-7 flex items-center justify-center mr-3">
                                {{ $pos + 1 }}
                            </div>
                            <div>{{ $sentences[$sentIdx] ?? '' }}</div>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- User's Order --}}
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-blue-100 p-3 border-b">
                    <h5 class="font-semibold text-blue-800 flex items-center">
                        Thứ tự của bạn
                    </h5>
                </div>
                <ol class="divide-y">
                    @forelse($userIndices as $pos => $sentIdx)
                        @php
                            $isCorrectPosition = isset($correctIndices[$pos]) && $correctIndices[$pos] === $sentIdx;
                            $sentenceText = $sentences[$sentIdx] ?? '';
                        @endphp
                        <li class="p-3 bg-white flex items-start">
                            <div
                                class="flex-shrink-0 {{ $isCorrectPosition ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }} font-bold rounded-full w-7 h-7 flex items-center justify-center mr-3">
                                {{ $pos + 1 }}
                            </div>
                            <div class="flex-grow">{{ $sentenceText }}</div>
                            <div class="flex-shrink-0 ml-2">
                                @if ($isCorrectPosition)
                                    <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 00-1.414-1.414L8 11.172 4.707 7.879a1 1 0 10-1.414 1.414l4 4a1 1 0 001.414 0l8-8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="p-4 text-center text-gray-500">Bạn chưa trả lời câu này.</li>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</div>
