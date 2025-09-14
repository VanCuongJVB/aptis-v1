<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nhập danh sách học sinh') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-xl font-bold mb-4">Nhập danh sách học sinh từ file</h1>
                    <div class="mt-3">
                        <a href="{{ asset('downloads/students_import_template.csv') }}"
                            class="inline-flex items-center px-3 py-1.5 bg-white border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50"
                            download>
                            <!-- download icon -->
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 3v12m0 0l-4-4m4 4l4-4" />
                            </svg>
                            Tải mẫu CSV
                        </a>
                    </div>
                    <div class="max-w-xl">
                        <form method="POST" action="{{ route('admin.students.import') }}" class="space-y-4"
                            enctype="multipart/form-data">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Chọn file CSV hoặc
                                    Excel</label>
                                <input type="file" name="file" required accept=".csv,.txt,.xlsx"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <h3 class="text-sm font-medium text-blue-800 mb-2">Hướng dẫn:</h3>
                                <p class="text-sm text-blue-700 mb-2">Chuẩn bị file CSV/Excel với các cột sau:</p>
                                <div class="bg-white p-2 rounded text-xs font-mono">
                                    email,name,is_active,access_starts_at,access_ends_at</div>
                                <ul class="mt-2 text-sm text-blue-700 space-y-1 ml-5 list-disc">
                                    <li>email: Bắt buộc, định dạng email hợp lệ</li>
                                    <li>name: Tên học sinh (không bắt buộc)</li>
                                    <li>is_active: 1 (kích hoạt) hoặc 0 (không kích hoạt)</li>
                                    <li>access_starts_at: Thời gian bắt đầu, định dạng YYYY-MM-DD HH:MM:SS</li>
                                    <li>access_ends_at: Thời gian kết thúc, định dạng tương tự</li>
                                </ul>
                            </div>

                            <div class="flex items-center justify-between mt-4">
                                <a href="{{ route('admin.students.index') }}"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Quay lại
                                </a>
                                <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                                    </svg>
                                    Tải lên và xử lý
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>