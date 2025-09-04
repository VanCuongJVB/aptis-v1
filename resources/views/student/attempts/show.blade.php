<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kết quả bài thi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Thông tin tổng quan') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="border rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('Bài thi:') }}</div>
                            <div class="text-lg font-semibold">{{ $attempt->quiz->title }}</div>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('Điểm số:') }}</div>
                            <div class="text-lg font-semibold">{{ $attempt->score_raw }} / {{ $attempt->quiz->questions->count() }}</div>
                        </div>
                        
                        <div class="border rounded-lg p-4">
                            <div class="text-sm font-medium text-gray-500">{{ __('Phần trăm:') }}</div>
                            <div class="flex items-center">
                                <div class="text-lg font-semibold">{{ number_format($attempt->score_percent, 2) }}%</div>
                                <div class="ml-3 flex-grow">
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $attempt->score_percent }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-medium mb-4">{{ __('Chi tiết đáp án') }}</h3>
                    
                    <div class="space-y-4">
                        @foreach($attempt->quiz->questions as $q)
                            @php
                                $item = $itemsByQid[$q->id] ?? null;
                                $selected = collect($item?->selected_option_ids ?? []);
                                $correctIds = $q->options->where('is_correct', true)->pluck('id');
                            @endphp
                            <div class="border rounded-lg overflow-hidden">
                                <div class="flex justify-between items-center p-4 bg-gray-50 border-b">
                                    <div class="font-medium">{{ __('Câu') }} {{ $loop->iteration }}</div>
                                    <div class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $item && $item->is_correct ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $item && $item->is_correct ? __('Đúng') : __('Sai') }}
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div class="text-gray-800 mb-3">{{ $q->stem }}</div>
                                    <div class="space-y-2">
                                        @foreach($q->options as $opt)
                                            <div class="flex items-center p-2 rounded 
                                                {{ $correctIds->contains($opt->id) ? 'bg-green-50' : 
                                                   ($selected->contains($opt->id) && !$correctIds->contains($opt->id) ? 'bg-red-50' : '') }}">
                                                <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center mr-2 
                                                    {{ $correctIds->contains($opt->id) ? 'text-green-600' : 
                                                       ($selected->contains($opt->id) && !$correctIds->contains($opt->id) ? 'text-red-600' : 'text-gray-400') }}">
                                                    @if($correctIds->contains($opt->id))
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                        </svg>
                                                    @elseif($selected->contains($opt->id))
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                        </svg>
                                                    @else
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                                                        </svg>
                                                    @endif
                                                </div>
                                                <div>
                                                    <span class="text-sm">{{ $opt->label }}. {{ $opt->content }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 flex justify-between">
                        <a href="{{ route('student.dashboard') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Quay lại bảng điều khiển') }}
                        </a>
                        
                        <a href="{{ route('student.quizzes.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Làm bài khác') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
