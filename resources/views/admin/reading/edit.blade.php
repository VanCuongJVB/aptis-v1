<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chỉnh sửa bộ đề Reading') }}: {{ $quiz->title }}
            </h2>
            
            <div class="flex space-x-2">
                <a href="{{ route('admin.reading.sets.part', $quiz->part) }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-500 focus:shadow-outline-gray transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    {{ __('Quay lại') }}
                </a>
                
                <a href="{{ route('admin.reading.questions.create', $quiz) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue transition ease-in-out duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    {{ __('Thêm câu hỏi') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            
            @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            
            <!-- Chi tiết bộ đề -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $quiz->title }}</h3>
                            <p class="text-gray-600 mt-1">{{ $quiz->description }}</p>
                            
                            <div class="mt-2 flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">
                                    {{ __('Reading Part') }} {{ $quiz->part }}
                                </span>
                                
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">
                                    {{ __('Mức độ:') }} 
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $quiz->difficulty)
                                            ★
                                        @else
                                            ☆
                                        @endif
                                    @endfor
                                </span>
                                
                                @if($quiz->time_limit > 0)
                                    <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded-full text-xs">
                                        {{ $quiz->time_limit }} {{ __('phút') }}
                                    </span>
                                @endif
                                
                                <span class="px-2 py-1 {{ $quiz->is_published ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }} rounded-full text-xs">
                                    {{ $quiz->is_published ? __('Đã xuất bản') : __('Bản nháp') }}
                                </span>
                            </div>
                        </div>
                        
                        <button type="button" id="editQuizBtn" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            {{ __('Chỉnh sửa thông tin') }}
                        </button>
                    </div>
                    
                    <!-- Form chỉnh sửa (ẩn ban đầu) -->
                    <div id="editQuizForm" class="border-t pt-4 mt-4 hidden">
                        <form method="POST" action="{{ route('admin.reading.sets.update', $quiz) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="title" :value="__('Tiêu đề bộ đề')" />
                                    <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $quiz->title)" required />
                                    <x-input-error :messages="$errors->get('title')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="difficulty" :value="__('Mức độ khó')" />
                                    <select id="difficulty" name="difficulty" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="1" {{ old('difficulty', $quiz->difficulty) == 1 ? 'selected' : '' }}>{{ __('Dễ (1 sao)') }}</option>
                                        <option value="2" {{ old('difficulty', $quiz->difficulty) == 2 ? 'selected' : '' }}>{{ __('Khá dễ (2 sao)') }}</option>
                                        <option value="3" {{ old('difficulty', $quiz->difficulty) == 3 ? 'selected' : '' }}>{{ __('Trung bình (3 sao)') }}</option>
                                        <option value="4" {{ old('difficulty', $quiz->difficulty) == 4 ? 'selected' : '' }}>{{ __('Khó (4 sao)') }}</option>
                                        <option value="5" {{ old('difficulty', $quiz->difficulty) == 5 ? 'selected' : '' }}>{{ __('Rất khó (5 sao)') }}</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('difficulty')" class="mt-2" />
                                </div>
                                
                                <div class="md:col-span-2">
                                    <x-input-label for="description" :value="__('Mô tả')" />
                                    <textarea id="description" name="description" rows="3" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $quiz->description) }}</textarea>
                                    <x-input-error :messages="$errors->get('description')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="time_limit" :value="__('Thời gian làm bài (phút, 0 = không giới hạn)')" />
                                    <x-text-input id="time_limit" class="block mt-1 w-full" type="number" name="time_limit" :value="old('time_limit', $quiz->time_limit)" min="0" />
                                    <x-input-error :messages="$errors->get('time_limit')" class="mt-2" />
                                </div>
                                
                                <div class="flex items-center mt-8">
                                    <input id="is_published" type="checkbox" name="is_published" value="1" {{ old('is_published', $quiz->is_published) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <label for="is_published" class="ml-2 text-sm text-gray-600">{{ __('Xuất bản') }}</label>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-end mt-6">
                                <x-secondary-button type="button" id="cancelEditBtn" class="mr-3">
                                    {{ __('Hủy') }}
                                </x-secondary-button>
                                
                                <x-primary-button>
                                    {{ __('Cập nhật') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Danh sách câu hỏi -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Danh sách câu hỏi') }} ({{ $questions->count() }})</h3>
                        
                        <a href="{{ route('admin.reading.questions.create', $quiz) }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ __('Thêm câu hỏi') }}
                        </a>
                    </div>
                    
                    @if($questions->count() > 0)
                        <div id="questions-container" class="space-y-6">
                            @foreach($questions as $index => $question)
                                <div class="question-item border rounded-lg overflow-hidden" data-question-id="{{ $question->id }}">
                                    <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                                        <div class="flex items-center">
                                            <span class="font-medium text-gray-700">{{ __('Câu hỏi') }} {{ $index + 1 }}</span>
                                            <span class="ml-3 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">{{ $question->point }} {{ __('điểm') }}</span>
                                            
                                            @if($question->options_count > 0)
                                                <span class="ml-2 px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                                    {{ $question->options_count }} {{ __('đáp án đúng') }}
                                                </span>
                                            @else
                                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                                    {{ __('Chưa có đáp án đúng') }}
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <div class="flex space-x-2">
                                            <a href="{{ route('admin.reading.questions.edit', $question) }}" class="text-indigo-600 hover:text-indigo-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            
                                            <form method="POST" action="{{ route('admin.reading.questions.destroy', $question) }}" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa câu hỏi này?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <div class="p-4">
                                        <div class="prose prose-sm max-w-none mb-4">
                                            {!! nl2br(e($question->content)) !!}
                                        </div>
                                        
                                        @if($question->image)
                                            <div class="mb-4">
                                                <img src="{{ asset('storage/' . $question->image) }}" alt="Question image" class="max-w-md mx-auto rounded">
                                            </div>
                                        @endif
                                        
                                        @if($question->passage)
                                            <div class="bg-gray-50 p-3 rounded mb-4 prose prose-sm max-w-none">
                                                <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('Đoạn văn') }}:</h4>
                                                {!! nl2br(e($question->passage)) !!}
                                            </div>
                                        @endif
                                        
                                        <div class="mt-4">
                                            <h4 class="text-sm font-medium text-gray-700 mb-2">{{ __('Các lựa chọn') }}:</h4>
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                @foreach($question->options as $option)
                                                    <div class="flex items-start p-2 {{ $option->is_correct ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200' }} rounded">
                                                        <div class="flex-shrink-0 h-5 w-5 mt-0.5">
                                                            @if($option->is_correct)
                                                                <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            @else
                                                                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            @endif
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="{{ $option->is_correct ? 'text-green-800' : 'text-gray-700' }}">{{ $option->content }}</p>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        @if($question->explanation)
                                            <div class="mt-4 bg-blue-50 p-3 rounded">
                                                <h4 class="text-sm font-medium text-blue-700 mb-1">{{ __('Giải thích') }}:</h4>
                                                <p class="text-sm text-blue-600">{{ $question->explanation }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Chưa có câu hỏi nào') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Hãy bắt đầu bằng cách thêm câu hỏi mới.') }}</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.reading.questions.create', $quiz) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    {{ __('Thêm câu hỏi đầu tiên') }}
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editQuizBtn = document.getElementById('editQuizBtn');
            const editQuizForm = document.getElementById('editQuizForm');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            
            editQuizBtn.addEventListener('click', function() {
                editQuizForm.classList.remove('hidden');
                editQuizBtn.classList.add('hidden');
            });
            
            cancelEditBtn.addEventListener('click', function() {
                editQuizForm.classList.add('hidden');
                editQuizBtn.classList.remove('hidden');
            });
            
            // Drag and drop reordering could be added here in the future
        });
    </script>
    @endpush
</x-app-layout>
