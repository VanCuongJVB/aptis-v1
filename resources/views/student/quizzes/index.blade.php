<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Danh sách bài thi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Bài thi khả dụng') }}</h3>
                    
                    @if($quizzes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($quizzes as $quiz)
                                <div class="border rounded-lg overflow-hidden hover:shadow-md transition">
                                    <div class="p-4 border-b bg-gray-50">
                                        <h4 class="font-medium">{{ $quiz->title }}</h4>
                                    </div>
                                    <div class="p-4">
                                        <div class="text-sm text-gray-700 mb-4">
                                            {{ Str::limit($quiz->description, 100) }}
                                        </div>
                                        
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $quiz->skill === 'reading' ? 'bg-purple-100 text-purple-800' : 
                                                   ($quiz->skill === 'listening' ? 'bg-blue-100 text-blue-800' : 
                                                    'bg-green-100 text-green-800') }}">
                                                {{ ucfirst($quiz->skill) }} - Part {{ $quiz->part }}
                                            </span>
                                            
                                            <span class="text-xs text-gray-500">
                                                {{ $quiz->duration_minutes }} {{ __('phút') }}
                                            </span>
                                        </div>
                                        
                                        <div class="mt-4">
                                            <a href="{{ route('student.quizzes.show', $quiz) }}" class="block text-center w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                                {{ __('Bắt đầu') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-4">
                            {{ __('Hiện không có bài thi nào khả dụng.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
