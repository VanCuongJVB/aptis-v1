@extends('layouts.app')

@section('title', "Reading Part $part Sets")

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-start mb-8">
        <div>
            <h1 class="text-3xl font-bold">Reading Part {{ $part }} Sets</h1>
            <p class="text-gray-600 mt-2">Manage reading sets for Part {{ $part }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.reading.index') }}" 
               class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                Back
            </a>
            <button onclick="createSet({{ $part }})"
                    class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Create New Set
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Title
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Questions
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Created
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($quizzes as $quiz)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $quiz->title }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-500">
                            {{ $quiz->questions_count ?? $quiz->questions->count() }} questions
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($quiz->is_published)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Published
                        </span>
                        @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Draft
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $quiz->created_at->format('Y-m-d') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                        <a href="{{ route('admin.reading.sets.edit', $quiz) }}" 
                           class="text-indigo-600 hover:text-indigo-900">
                            Edit
                        </a>
                        @if($quiz->is_published)
                        <form action="{{ route('admin.reading.sets.unpublish', $quiz) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-yellow-600 hover:text-yellow-900">
                                Unpublish
                            </button>
                        </form>
                        @else
                        <form action="{{ route('admin.reading.sets.publish', $quiz) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-green-600 hover:text-green-900">
                                Publish
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.reading.sets.destroy', $quiz) }}" 
                              method="POST" 
                              class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this set?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        No reading sets found. Click "Create New Set" to add one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $quizzes->links() }}
    </div>
</div>

<!-- Create Set Modal -->
<div id="createSetModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Create New Reading Set</h3>
                <form method="POST" action="{{ route('admin.reading.sets.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="part" value="{{ $part }}">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Set Title
                        </label>
                        <input type="text" 
                               name="title" 
                               class="w-full rounded-md border-gray-300" 
                               required>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeCreateModal()"
                                class="px-4 py-2 text-gray-700 bg-gray-100 rounded hover:bg-gray-200">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Create Set
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createSet(part) {
    document.getElementById('createSetModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createSetModal').classList.add('hidden');
}
</script>
@endpush
@endsection
