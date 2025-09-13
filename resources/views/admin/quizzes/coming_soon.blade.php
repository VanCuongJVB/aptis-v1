@extends('layouts.app')

@section('title', 'Coming Soon')

@section('content')
<div class="min-h-[80vh] flex items-center justify-center bg-gray-50 py-4 mt-4">
    <div class="w-full max-w-4xl px-6">
        <div class="bg-white rounded-2xl overflow-hidden grid grid-cols-1 md:grid-cols-2">
            <div class="p-10 flex flex-col justify-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 mb-6">
                    <!-- simple SVG icon -->
                    <svg class="w-8 h-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V4m0 16v-4" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-3">Coming Soon: Quizzes Management</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">We're building a richer admin experience for quizzes, sets, question import and batch operations. It'll be fast, reliable and easy to manage.</p>

                <ul class="space-y-3 mb-6">
                    <li class="flex items-start text-gray-700 dark:text-gray-200">
                        <span class="inline-flex items-center justify-center w-6 h-6 mr-3 rounded bg-green-50 text-green-600">✓</span>
                        Bulk import questions (JSON / ZIP)
                    </li>
                    <li class="flex items-start text-gray-700 dark:text-gray-200">
                        <span class="inline-flex items-center justify-center w-6 h-6 mr-3 rounded bg-blue-50 text-blue-600">✓</span>
                        Set & question batch editing
                    </li>
                    <li class="flex items-start text-gray-700 dark:text-gray-200">
                        <span class="inline-flex items-center justify-center w-6 h-6 mr-3 rounded bg-yellow-50 text-yellow-600">✓</span>
                        Preview & validation before publish
                    </li>
                </ul>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('admin.quizzes.index') }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-md shadow">Back to Quizzes</a>
                    <a href="{{ route('admin.quizzes.coming') }}" class="inline-block px-4 py-2 border rounded-md text-gray-700">Notify me</a>
                </div>
            </div>

            <div class="hidden md:flex items-center justify-center bg-gradient-to-br from-indigo-50 to-white p-8">
                <div class="max-w-xs text-center">
                    <svg class="mx-auto mb-6 w-48 h-48 text-indigo-400" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="6" y="10" width="52" height="36" rx="4" stroke="currentColor" stroke-width="2" opacity="0.6"/>
                        <path d="M18 24h28M18 30h28" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity="0.6"/>
                        <circle cx="24" cy="40" r="2" fill="currentColor" opacity="0.6"/>
                    </svg>
                    <p class="text-gray-500">Manage quizzes, group sets, and import questions in one place. We'll let you know when it's ready.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
