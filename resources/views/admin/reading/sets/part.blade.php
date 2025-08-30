@extends('layouts.app')

@section('title', "Reading Part $part Sets")

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">Part {{ $part }} Sets</h1>
                <p class="text-gray-600">Manage reading practice sets for Part {{ $part }}</p>
            </div>
            <button onclick="document.getElementById('createSetModal').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                New Set
            </button>
        </div>

        <!-- Sets List -->
        <div class="bg-white rounded-lg shadow">
            <div class="divide-y">
                @forelse($sets as $quiz)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <h3 class="font-medium">{{ $quiz->title }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ $quiz->questions_count }} questions
                            @if($quiz->is_published)
                            <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs">Published</span>
                            @else
                            <span class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">Draft</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => $part]) }}" 
                           class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.reading.sets.delete', $quiz) }}"
                              onsubmit="return confirm('Are you sure you want to delete this set?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-gray-500">
                    No sets created yet for Part {{ $part }}
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Create Set Modal -->
    <div id="createSetModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <form method="POST" action="{{ route('admin.reading.sets.create') }}" class="p-6">
                    @csrf
                    <h3 class="text-lg font-medium mb-4">Create New Set</h3>
                    
                    <input type="hidden" name="part" value="{{ $part }}">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Set Title</label>
                        <input type="text" name="title" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" 
                                onclick="document.getElementById('createSetModal').classList.add('hidden')"
                                class="px-4 py-2 bg-gray-100 rounded">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
                            Create
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
