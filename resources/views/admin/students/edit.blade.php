<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chỉnh sửa học sinh') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-xl font-bold mb-4">Chỉnh sửa thông tin học sinh</h1>
                    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="space-y-3 max-w-xl">
                        @csrf @method('PUT')
                        @include('admin.students.partials.form', ['student' => $student])
                        <div class="text-sm text-slate-500">Mật khẩu cố định: <code>123456</code></div>
                        
                        <div class="mt-4">
                            <h2 class="text-md font-medium mb-2">Gia hạn truy cập nhanh</h2>
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.students.extend', $student) }}?days=30">
                                    @csrf
                                    <button class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">+30 ngày</button>
                                </form>
                                <form method="POST" action="{{ route('admin.students.extend', $student) }}?days=90">
                                    @csrf
                                    <button class="inline-flex items-center px-4 py-2 bg-emerald-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">+90 ngày</button>
                                </form>
                                <form method="POST" action="{{ route('admin.students.extend', $student) }}?days=180">
                                    @csrf
                                    <button class="inline-flex items-center px-4 py-2 bg-emerald-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-900 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">+180 ngày</button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between mt-4">
                            <a href="{{ route('admin.students.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Quay lại
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">Lưu thay đổi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
