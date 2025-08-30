{{-- Reading Part 1: Sentence Matching --}}
<div class="space-y-6">
    <div>
        <label for="stem" class="block text-sm font-medium text-gray-700">Instructions</label>
        <div class="mt-1">
            <textarea id="stem" name="stem" rows="2" 
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                required>{{ old('stem', $question->stem) }}</textarea>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Sentences</label>
        <div class="mt-1">
            <textarea name="meta[sentences]" rows="5" 
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                placeholder="Enter one sentence per line"
                required>{{ old('meta.sentences', implode("\n", $question->meta['sentences'] ?? [])) }}</textarea>
            <p class="mt-2 text-sm text-gray-500">Enter each sentence on a new line. These are the sentences students will match.</p>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Answer Options</label>
        <div class="mt-1 space-y-2">
            <div id="options-container">
                @foreach($question->options as $index => $option)
                    <div class="flex items-center gap-2 option-row">
                        <input type="hidden" name="options[{{ $index }}][id]" value="{{ $option->id }}">
                        <input type="text" 
                               name="options[{{ $index }}][label]" 
                               value="{{ $option->label }}"
                               class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               required>
                        <label class="inline-flex items-center">
                            <input type="radio" 
                                   name="correct_option" 
                                   value="{{ $index }}"
                                   {{ $option->is_correct ? 'checked' : '' }}
                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                            <span class="ml-2 text-sm text-gray-600">Correct</span>
                        </label>
                        <button type="button" class="remove-option text-red-600 hover:text-red-800">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-option" 
                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Add Option
            </button>
        </div>
    </div>

    <div>
        <label for="explanation" class="block text-sm font-medium text-gray-700">Explanation</label>
        <div class="mt-1">
            <textarea id="explanation" name="explanation" rows="3"
                class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('explanation', $question->explanation) }}</textarea>
            <p class="mt-2 text-sm text-gray-500">This will be shown to students after they answer.</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('options-container');
    const addButton = document.getElementById('add-option');

    addButton.addEventListener('click', function() {
        const index = container.children.length;
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 option-row';
        div.innerHTML = `
            <input type="text" 
                   name="options[${index}][label]" 
                   class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"
                   required>
            <label class="inline-flex items-center">
                <input type="radio" 
                       name="correct_option" 
                       value="${index}"
                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                <span class="ml-2 text-sm text-gray-600">Correct</span>
            </label>
            <button type="button" class="remove-option text-red-600 hover:text-red-800">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        container.appendChild(div);
    });

    container.addEventListener('click', function(e) {
        if (e.target.closest('.remove-option')) {
            const row = e.target.closest('.option-row');
            row.remove();
            updateOptionIndexes();
        }
    });

    function updateOptionIndexes() {
        const rows = container.querySelectorAll('.option-row');
        rows.forEach((row, index) => {
            const inputs = row.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                }
                if (input.type === 'radio') {
                    input.value = index;
                }
            });
        });
    }

    // Handle form submission
    const form = container.closest('form');
    form.addEventListener('submit', function(e) {
        const rows = container.querySelectorAll('.option-row');
        rows.forEach((row, index) => {
            // Add hidden field for is_correct based on radio selection
            const radio = row.querySelector('input[type="radio"]');
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `options[${index}][is_correct]`;
            hiddenInput.value = radio.checked ? '1' : '0';
            row.appendChild(hiddenInput);
        });
    });
});
</script>
@endpush
