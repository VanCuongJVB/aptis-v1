<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Reading Dashboard') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Bài thi Reading') }}</h3>

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
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($quiz->skill) }} - Part {{ $quiz->part }}</span>

                                            <span class="text-xs text-gray-500">{{ $quiz->duration_minutes }} {{ __('phút') }}</span>
                                        </div>

                                        <div class="mt-4">
                                            @if($quiz->skill === 'reading')
                                                <a href="{{ route('student.reading.sets.index', ['quiz' => $quiz->id]) }}" class="btn-base btn-primary block text-center w-full">{{ __('Bắt đầu') }}</a>
                                            @else
                                                <a href="{{ route('student.quizzes.show', $quiz) }}" class="btn-base btn-primary block text-center w-full">{{ __('Bắt đầu') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-4">{{ __('Hiện không có bài thi Reading.') }}</div>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Lịch sử làm bài Reading gần đây') }}</h3>

                    @if($recentAttempts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Bài thi') }}</th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Điểm') }}</th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Thời gian') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAttempts as $attempt)
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $attempt->quiz->title }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $attempt->score_percentage }}%</td>
                                            <td class="py-2 px-4 border-b border-gray-200">{{ $attempt->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-4">{{ __('Bạn chưa làm bài Reading nào.') }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
