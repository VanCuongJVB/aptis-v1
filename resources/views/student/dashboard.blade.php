<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Bảng điều khiển học viên') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Thông tin tài khoản -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Thông tin tài khoản') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <div class="mb-4">
                                <div class="text-sm font-medium text-gray-500">{{ __('Tên học viên:') }}</div>
                                <div>{{ $user->name }}</div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="text-sm font-medium text-gray-500">{{ __('Email:') }}</div>
                                <div>{{ $user->email }}</div>
                            </div>
                            
                            <div>
                                <div class="text-sm font-medium text-gray-500">{{ __('Trạng thái tài khoản:') }}</div>
                                <div>
                                    @if($user->is_active)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Đang hoạt động') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('Bị khóa') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="mb-4">
                                <div class="text-sm font-medium text-gray-500">{{ __('Thời hạn truy cập:') }}</div>
                                <div>
                                    @if($accessInfo['expires_at'])
                                        {{ $accessInfo['expires_at']->format('d/m/Y') }}
                                        <span class="text-sm text-gray-500">
                                            ({{ $accessInfo['days_left'] > 0 
                                                ? __('Còn :days ngày', ['days' => $accessInfo['days_left']]) 
                                                : __('Đã hết hạn') }})
                                        </span>
                                    @else
                                        {{ __('Không giới hạn') }}
                                    @endif
                                </div>
                            </div>
                            
                            @if($accessInfo['percentage'] !== null)
                                <div>
                                    <div class="text-sm font-medium text-gray-500">{{ __('Thời gian đã sử dụng:') }}</div>
                                    <div class="mt-1">
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $accessInfo['percentage'] }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $accessInfo['percentage'] }}% {{ __('đã sử dụng') }}</div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mt-4">
                                <a href="{{ route('profile.sessions') }}" class="text-blue-600 hover:text-blue-800">
                                    {{ __('Quản lý thiết bị đăng nhập') }} →
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Lịch sử làm bài gần đây -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">{{ __('Lịch sử làm bài gần đây') }}</h3>
                    
                    @if($recentAttempts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Bài thi') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Kỹ năng') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Điểm') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Thời gian') }}
                                        </th>
                                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Thao tác') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAttempts as $attempt)
                                        <tr>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $attempt->quiz->title }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    {{ $attempt->quiz->skill === 'reading' ? 'bg-purple-100 text-purple-800' : 
                                                       ($attempt->quiz->skill === 'listening' ? 'bg-blue-100 text-blue-800' : 
                                                        'bg-green-100 text-green-800') }}">
                                                    {{ ucfirst($attempt->quiz->skill) }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $attempt->score }} / {{ $attempt->total_questions }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                {{ $attempt->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-2 px-4 border-b border-gray-200">
                                                <a href="#" class="text-blue-600 hover:text-blue-800">
                                                    {{ __('Xem chi tiết') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-gray-500 text-center py-4">
                            {{ __('Bạn chưa làm bài thi nào.') }}
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Bài thi khả dụng -->
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
