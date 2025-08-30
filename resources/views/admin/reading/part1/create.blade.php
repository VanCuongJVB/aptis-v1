@extends('layouts.app')

@section('title', 'Create Reading Part 1')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold">Create Reading Part 1</h1>
                <p class="text-gray-600">Quiz: {{ $quiz->title }}</p>
            </div>
            <a href="{{ route('admin.reading.sets.edit', ['quiz' => $quiz, 'part' => 1]) }}" 
               class="text-gray-600 hover:text-gray-900">
                Back to Set
            </a>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.reading.part1.store', $quiz) }}" class="space-y-6">
                @csrf
                <!-- Passage Input -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">
                            Passage Text
                        </label>
                        <textarea name="passage" rows="6" 
                                  class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-1"
                                  required>{{ old('passage', "Hey Lewis,\n1.It is a ........ (long/red/tall) day.\n2.I need the ........ (work/details/job) of the report.\n3.Can you print a ........ (information/disk/copy) for me?\n4.I am ........ (easy/busy/difficult) with my work.\n5.I will have meetings with my ........ (client/table/report.)\nThanks,\nLouis.") }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            Enter the passage text. Use ........ (8 dots) to indicate gaps and (option1/option2/option3) for choices.
                        </p>
                        @error('passage')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Questions Container -->
                <div id="questionsContainer" class="space-y-6">
                    <!-- Question template will be added here by JavaScript -->
                </div>

                <!-- Buttons -->
                <div class="flex justify-between space-x-4">
                    <button type="button" onclick="parsePassage()" 
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        Generate Questions from Passage
                    </button>
                    <div class="flex space-x-3">
                        <button type="button" onclick="previewAll()"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                            Preview All
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Save Questions
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium mb-4">Question Preview</h3>
                    <div id="previewContent" class="prose max-w-none space-y-6"></div>
                    <div class="mt-6 flex justify-end">
                        <button onclick="closePreview()"
                                class="px-4 py-2 bg-gray-100 rounded text-gray-700">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function addQuestion(stem, options = [], correctOption = 0) {
    const container = document.getElementById('questionsContainer');
    const questionNumber = ++questionCount;

    const template = `
        <div class="bg-white rounded-lg shadow p-6" id="question${questionNumber}">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium">Question ${questionNumber}</h3>
                <button type="button" onclick="removeQuestion(${questionNumber})" 
                        class="text-red-600 hover:text-red-800">
                    Remove
                </button>
            </div>

            <div class="space-y-4">
                <!-- Question Text -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Question Text
                    </label>
                    <input type="text" 
                           name="questions[${questionNumber}][stem]"
                           class="mt-1 w-full rounded-md border-gray-300"
                           value="${stem || ''}"
                           required>
                </div>

                <!-- Options -->
                <div class="space-y-3">
                    <label class="block text-sm font-medium text-gray-700">Answer Options</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        ${options.map((opt, i) => `
                            <div class="flex items-center space-x-2">
                                <input type="radio" 
                                       name="questions[${questionNumber}][correct_option]" 
                                       value="${i}"
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                       ${i === correctOption ? 'checked' : ''}
                                       required>
                                <input type="text" 
                                       name="questions[${questionNumber}][options][]"
                                       class="flex-1 rounded-md border-gray-300" 
                                       value="${opt}"
                                       required>
                            </div>
                        `).join('')}
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', template);
}

function removeQuestion(number) {
    const question = document.getElementById(`question${number}`);
    question.remove();
}

function parsePassage() {
    const passage = document.querySelector('textarea[name="passage"]').value;
    const questions = passage.match(/\d+\.\s*.*?\.\.\.\.....\s*\((.*?)\)/g) || [];
    
    // Clear existing questions
    document.getElementById('questionsContainer').innerHTML = '';
    questionCount = 0;
    
    // Add a question for each match
    questions.forEach(questionText => {
        const stem = questionText.replace(/\.\.\.\.....\s*\(.*?\)/, '___');
        const optionsMatch = questionText.match(/\((.*?)\)/);
        const options = optionsMatch ? optionsMatch[1].split('/') : [];
        addQuestion(stem, options);
    });
}

function previewAll() {
    const passage = document.querySelector('textarea[name="passage"]').value;
    const questions = Array.from(document.querySelectorAll('#questionsContainer > div')).map(div => {
        const stem = div.querySelector('input[name$="[stem]"]').value;
        const options = Array.from(div.querySelectorAll('input[name$="[options][]"]')).map(input => input.value);
        const correctIndex = div.querySelector('input[name$="[correct_option]"]:checked')?.value;
        return { stem, options, correctIndex };
    });

    const previewText = passage.replace(/\.\.\.\.....\s*\((.*?)\)/g, '<span class="px-8 border-b-2 border-gray-400"></span>');
    
    let html = `
        <div class="space-y-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="whitespace-pre-line">${previewText}</p>
            </div>
            <div class="space-y-6">
                ${questions.map((q, i) => `
                    <div class="border-t pt-4">
                        <p class="font-medium mb-2">Question ${i + 1}</p>
                        <p class="mb-4">${q.stem}</p>
                        <div class="space-y-2">
                            ${q.options.map((opt, j) => `
                                <div class="flex items-center space-x-2">
                                    <input type="radio" ${j == q.correctIndex ? 'checked' : ''} disabled>
                                    <label>${opt}</label>
                                </div>
                            `).join('')}
                        </div>
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

// Parse passage on load
document.addEventListener('DOMContentLoaded', parsePassage);
</script>
@endpush
@endsection
