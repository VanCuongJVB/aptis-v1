@extends('layouts.app')

@section('title', 'Quản lý Reading')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Quản lý Reading APTIS</h1>
        <p class="text-gray-600 mt-2">Quản lý các bài tập Reading cho kỳ thi APTIS</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        @foreach($parts as $part => $name)
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Part {{ $part }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats[$part]['quizzes'] }}</p>
                </div>
            </div>
            <div class="mt-4">
                <p class="text-sm text-gray-600">{{ $stats[$part]['questions'] }} câu hỏi</p>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Reading Parts Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Part 1: Sentence Comprehension -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl font-bold text-blue-600">1</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Sentence Comprehension</h3>
                        <p class="text-sm text-gray-600">{{ $stats[1]['quizzes'] }} bộ đề</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-4 text-sm">
                    Hiểu nghĩa câu và chọn từ phù hợp để hoàn thành câu
                </p>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reading.sets.part', 1) }}" 
                       class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-blue-700 transition-colors">
                        Xem bộ đề
                    </a>
                    <a href="{{ route('admin.reading.sets.create', 1) }}" 
                       class="flex-1 bg-green-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-green-700 transition-colors">
                        Tạo mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Part 2: Text Cohesion -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl font-bold text-green-600">2</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Text Cohesion</h3>
                        <p class="text-sm text-gray-600">{{ $stats[2]['quizzes'] }} bộ đề</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-4 text-sm">
                    Hiểu mối liên kết giữa các câu trong đoạn văn
                </p>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reading.sets.part', 2) }}" 
                       class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-blue-700 transition-colors">
                        Xem bộ đề
                    </a>
                    <a href="{{ route('admin.reading.sets.create', 2) }}" 
                       class="flex-1 bg-green-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-green-700 transition-colors">
                        Tạo mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Part 3: Reading Comprehension -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl font-bold text-yellow-600">3</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Reading Comprehension</h3>
                        <p class="text-sm text-gray-600">{{ $stats[3]['quizzes'] }} bộ đề</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-4 text-sm">
                    Đọc hiểu đoạn văn ngắn và trả lời câu hỏi
                </p>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reading.sets.part', 3) }}" 
                       class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-blue-700 transition-colors">
                        Xem bộ đề
                    </a>
                    <a href="{{ route('admin.reading.sets.create', 3) }}" 
                       class="flex-1 bg-green-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-green-700 transition-colors">
                        Tạo mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Part 4: Long Text Reading -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <span class="text-xl font-bold text-red-600">4</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Long Text Reading</h3>
                        <p class="text-sm text-gray-600">{{ $stats[4]['quizzes'] }} bộ đề</p>
                    </div>
                </div>
                <p class="text-gray-600 mb-4 text-sm">
                    Đọc hiểu văn bản dài và trả lời nhiều câu hỏi
                </p>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.reading.sets.part', 4) }}" 
                       class="flex-1 bg-blue-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-blue-700 transition-colors">
                        Xem bộ đề
                    </a>
                    <a href="{{ route('admin.reading.sets.create', 4) }}" 
                       class="flex-1 bg-green-600 text-white text-center py-2 px-3 rounded text-sm font-medium hover:bg-green-700 transition-colors">
                        Tạo mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Thao tác nhanh</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <button onclick="createFullReadingTest()" 
                    class="bg-purple-600 text-white py-3 px-6 rounded-lg hover:bg-purple-700 transition-colors font-medium">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Tạo bộ đề đầy đủ
            </button>
            
            <a href="{{ route('admin.reading.sets.part', ['part' => 'all']) }}" 
               class="bg-gray-600 text-white py-3 px-6 rounded-lg hover:bg-gray-700 transition-colors font-medium text-center">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                Xem tất cả bộ đề
            </a>
            
            <button onclick="importQuestions()" 
                    class="bg-orange-600 text-white py-3 px-6 rounded-lg hover:bg-orange-700 transition-colors font-medium">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                Import câu hỏi
            </button>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Hoạt động gần đây</h2>
        <div class="text-gray-600 text-center py-8">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p>Chưa có hoạt động nào gần đây</p>
            <p class="text-sm mt-2">Bắt đầu tạo bộ đề Reading để xem hoạt động ở đây</p>
        </div>
    </div>
</div>

<!-- Modal for Full Test Creation -->
<div id="fullTestModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Tạo bộ đề Reading đầy đủ</h3>
                <form method="POST" action="{{ route('admin.reading.sets.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="skill" value="reading">
                    <input type="hidden" name="is_full_set" value="true">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tiêu đề bộ đề
                        </label>
                        <input type="text" 
                               name="title" 
                               value="APTIS Reading Full Test - {{ date('d/m/Y') }}"
                               class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Mô tả
                        </label>
                        <textarea name="description" 
                                  class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                  rows="3"
                                  placeholder="Mô tả về bộ đề này..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Thời gian làm bài (phút)
                        </label>
                        <input type="number" 
                               name="duration_minutes" 
                               value="45"
                               min="15"
                               max="120"
                               class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500" 
                               required>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" 
                               name="is_published" 
                               id="is_published"
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_published" class="ml-2 text-sm text-gray-700">
                            Xuất bản ngay
                        </label>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeFullTestModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                            Hủy
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Tạo bộ đề
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function createFullReadingTest() {
    document.getElementById('fullTestModal').classList.remove('hidden');
}

function closeFullTestModal() {
    document.getElementById('fullTestModal').classList.add('hidden');
}

function importQuestions() {
    alert('Chức năng import câu hỏi đang được phát triển');
}

// Close modal when clicking outside
document.getElementById('fullTestModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeFullTestModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeFullTestModal();
    }
});
</script>
@endpush
