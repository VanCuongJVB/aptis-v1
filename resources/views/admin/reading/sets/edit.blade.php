@extends('layouts.app')

@section('title', "Edit Reading Set")

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold">{{ $quiz->title }}</h1>
                    <p class="text-gray-600">Part {{ $part }} Reading Set</p>
                </div>
                <div class="flex space-x-2">
                    <form method="POST" action="{{ route('admin.reading.sets.update', $quiz) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="title" value="{{ $quiz->title }}">
                        <input type="hidden" name="is_published" value="{{ !$quiz->is_published }}">
                        <button type="submit" 
                                class="px-4 py-2 rounded {{ $quiz->is_published ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                            {{ $quiz->is_published ? 'Unpublish' : 'Publish' }}
                        </button>
                    </form>
                    <button onclick="document.getElementById('addQuestionModal').classList.remove('hidden')"
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Add Question
                    </button>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="bg-white rounded-lg shadow">
            <div class="divide-y" id="questionsList">
                @foreach($quiz->questions as $question)
                <div class="p-4" data-question-id="{{ $question->id }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-grow pr-4">
                            <h3 class="font-medium mb-2">Question {{ $loop->iteration }}</h3>
                            <div class="text-sm text-gray-600">
                                {!! nl2br(e($question->stem)) !!}
                            </div>
                            @if($question->options->isNotEmpty())
                            <div class="mt-2 ml-4">
                                @foreach($question->options as $option)
                                <div class="flex items-center space-x-2 {{ $option->is_correct ? 'text-green-600' : '' }}">
                                    <span>{{ chr(64 + $loop->iteration) }}.</span>
                                    <span>{{ $option->label }}</span>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="editQuestion({{ $question->id }})"
                                    class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.questions.destroy', $question) }}"
                                  onsubmit="return confirm('Are you sure you want to delete this question?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 bg-red-100 text-red-700 rounded hover:bg-red-200">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Question Form Modal -->
    <div id="addQuestionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">Add Reading Part {{ $part }} Question</h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @if($part == 1)
                    <form method="POST" action="{{ route('admin.reading.part1.store', $quiz) }}" class="space-y-6">
                        @csrf
                        <!-- Sentence with Gap -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Sentence (use ___ for the gap)
                            </label>
                            <textarea name="stem" rows="3" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-1"
                                      placeholder="Enter the sentence with ___ for the gap"
                                      required>{{ old('stem') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Example: The company ___ a new manager last month.</p>
                        </div>

                        <!-- Options -->
                        <div class="space-y-4">
                            <label class="block text-sm font-medium text-gray-700">Answer Options</label>
                            
                            @for($i = 0; $i < 3; $i++)
                            <div class="flex items-center space-x-4">
                                <div class="flex-1">
                                    <input type="text" 
                                           name="options[{{ $i }}][label]"
                                           class="w-full rounded-md border-gray-300" 
                                           placeholder="Option {{ chr(65 + $i) }}"
                                           value="{{ old("options.$i.label") }}"
                                           required>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" 
                                           name="correct_option" 
                                           value="{{ $i }}"
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                           {{ old('correct_option') == $i ? 'checked' : '' }}
                                           required>
                                    <label class="ml-2 text-sm text-gray-600">Correct</label>
                                </div>
                            </div>
                            @endfor
                        </div>

                        <!-- Explanation -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Explanation (Optional)
                            </label>
                            <textarea name="explanation" rows="2" 
                                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-1"
                                      placeholder="Explain why this is the correct answer">{{ old('explanation') }}</textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="previewQuestion()"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Preview
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                Create Question
                            </button>
                        </div>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium">Question Preview</h3>
                        <button onclick="closePreview()" class="text-gray-400 hover:text-gray-500">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div id="previewContent" class="prose max-w-none"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Make questions sortable
new Sortable(document.getElementById('questionsList'), {
    animation: 150,
    onEnd: function(evt) {
        const questionIds = Array.from(evt.to.children).map(el => el.dataset.questionId);
        fetch('{{ route("admin.reading.sets.reorder", $quiz) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ orders: questionIds })
        });
    }
});

function editQuestion(id) {
    window.location.href = `/admin/reading/part{{ $part }}/edit/${id}`;
}

function closeModal() {
    document.getElementById('addQuestionModal').classList.add('hidden');
}

function previewQuestion() {
    const sentence = document.querySelector('textarea[name="stem"]').value;
    const options = Array.from(document.querySelectorAll('input[name^="options"]'))
        .map(input => input.value)
        .filter(Boolean);
    const correctIndex = document.querySelector('input[name="correct_option"]:checked')?.value;

    let html = `
        <div class="space-y-4">
            <p class="text-lg">${sentence.replace('___', '<span class="px-8 border-b-2 border-gray-400"></span>')}</p>
            <div class="space-y-2">
                ${options.map((opt, i) => `
                    <div class="flex items-center space-x-2">
                        <input type="radio" name="preview_answer" ${i == correctIndex ? 'checked' : ''} disabled>
                        <label>${opt}</label>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    document.getElementById('previewContent').innerHTML = html;
    document.getElementById('previewModal').classList.remove('hidden');
}

function closePreview() {
    document.getElementById('previewModal').classList.add('hidden');
}
</script>
@endpush
