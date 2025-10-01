@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-red-50 border border-red-200 rounded-lg p-6">
        <div class="flex items-center mb-4">
            <svg class="w-8 h-8 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h1 class="text-xl font-bold text-red-800">Lỗi hệ thống</h1>
        </div>
        
        <div class="text-red-700 mb-4">
            {{ $message ?? 'Đã xảy ra lỗi không mong muốn. Vui lòng thử lại sau.' }}
        </div>
        
        <div class="flex gap-4">
            <a href="{{ url()->previous() }}" 
               class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition duration-200">
                Quay lại
            </a>
            <a href="{{ route('student.dashboard') }}"
               class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition duration-200">
                Về trang chủ
            </a>
        </div>
    </div>
</div>
@endsection