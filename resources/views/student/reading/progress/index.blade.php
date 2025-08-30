<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tiến độ học tập') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Thống kê tổng quát -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Tổng quan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-sm text-blue-600">Số lần luyện tập</div>
                                <div class="text-2xl font-bold">{{ $stats['total_attempts'] }}</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg">
                                <div class="text-sm text-green-600">Điểm trung bình</div>
                                <div class="text-2xl font-bold">{{ $stats['average_score'] }}%</div>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <div class="text-sm text-purple-600">Thời gian học tập</div>
                                <div class="text-2xl font-bold">{{ $stats['total_time'] }} phút</div>
                            </div>
                        </div>
                    </div>

                    <!-- Thống kê theo phần -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">Theo từng phần</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($stats['by_part'] as $part => $partStats)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium mb-2">Phần {{ $part }}</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Số lần làm:</span>
                                        <span class="font-medium">{{ $partStats['attempts'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Điểm trung bình:</span>
                                        <span class="font-medium">{{ $partStats['average_score'] }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Hoàn thành:</span>
                                        <span class="font-medium">{{ $partStats['completed_sets'] }} bộ</span>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Các lần luyện tập gần đây -->
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Luyện tập gần đây</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thời gian</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phần</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chế độ</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Điểm</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentAttempts as $attempt)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ $attempt->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            Phần {{ optional($attempt->items->first())->question->part ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ ucfirst($attempt->mode) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            {{ number_format($attempt->score * 100, 1) }}%
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Links to detailed stats -->
                    <div class="mt-8 flex space-x-4">
                        <a href="{{ route('reading.progress.stats') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Thống kê chi tiết
                        </a>
                        <a href="{{ route('reading.progress.history') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Lịch sử làm bài
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
