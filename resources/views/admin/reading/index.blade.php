@extends('layouts.app')

@section('title', 'APTIS Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold">APTIS Management</h1>
        <p class="text-gray-600 mt-2">Manage reading and listening exercises</p>
    </div>

    <!-- Reading Section -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold mb-6">Reading</h2>
        <div class="mb-6">
            <button data-action="create-full-set" 
                    data-type="reading"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-semibold">
                Create Full Reading Set
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Part 1 Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-2">Part 1: Sentence Completion</h2>
                    <p class="text-gray-600 mb-4">Choose the word that best fits the gap</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('admin.reading.sets.part', 1) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            View Sets
                        </a>
                        <button data-action="create-set" 
                                data-type="reading" 
                                data-part="1"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Create New
                        </button>
                    </div>
                </div>
            </div>

            <!-- Part 2 Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-2">Part 2: Notice Matching</h2>
                    <p class="text-gray-600 mb-4">Match notices with their meanings</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('admin.reading.sets.part', 2) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            View Sets
                        </a>
                        <button data-action="create-set" 
                                data-type="reading" 
                                data-part="2"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Create New
                        </button>
                    </div>
                </div>
            </div>

            <!-- Part 3 Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-2">Part 3: Long Text</h2>
                    <p class="text-gray-600 mb-4">Read and answer questions</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('admin.reading.sets.part', 3) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            View Sets
                        </a>
                        <button data-action="create-set" 
                                data-type="reading" 
                                data-part="3"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Create New
                        </button>
                    </div>
                </div>
            </div>

            <!-- Part 4 Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-2">Part 4: Gap Fill</h2>
                    <p class="text-gray-600 mb-4">Fill gaps in a text</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('admin.reading.sets.part', 4) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            View Sets
                        </a>
                        <button data-action="create-set" 
                                data-type="reading" 
                                data-part="4"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Create New
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Listening Section -->
    <div>
        <h2 class="text-2xl font-bold mb-6">Listening</h2>
        <div class="mb-6">
            <button data-action="create-full-set" 
                    data-type="listening"
                    class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-semibold">
                Create Full Listening Set
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Listening Part 1 -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="p-6">
                    <h2 class="text-xl font-semibold mb-2">Part 1: Word List</h2>
                    <p class="text-gray-600 mb-4">Listen and select correct words</p>
                    <div class="mt-4 flex space-x-3">
                        <a href="{{ route('admin.reading.sets.part', ['part' => 1, 'type' => 'listening']) }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            View Sets
                        </a>
                        <button data-action="create-set" 
                                data-type="listening" 
                                data-part="1"
                                class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            Create New
                        </button>
                    </div>
                </div>
            </div>

            <!-- Add more listening parts similarly -->
        </div>
    </div>
</div>

<!-- Create Set Modal -->
<div id="createSetModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium mb-4">Create New Set</h3>
                <form id="createSetForm" method="POST" action="{{ route('admin.reading.sets.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" id="setType">
                    <input type="hidden" name="part" id="setPart">
                    <input type="hidden" name="is_full_set" id="isFullSet" value="false">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Set Title
                        </label>
                        <input type="text" 
                               name="title" 
                               class="w-full rounded-md border-gray-300" 
                               required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" 
                                  class="w-full rounded-md border-gray-300"
                                  rows="3"></textarea>
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
@endsection

@push('scripts')
<script>
// Constants for part names
const PART_NAMES = {
    reading: {
        1: 'Sentence Completion',
        2: 'Notice Matching',
        3: 'Long Text',
        4: 'Gap Fill'
    },
    listening: {
        1: 'Word List',
        2: 'Sentence Completion',
        3: 'Discussion',
        4: 'Monologue'
    }
};

let modal;
let form;
let typeInput;
let partInput;
let isFullSetInput;
let titleInput;
let descInput;

// Initialize modal functions
function showModal() {
    if (modal) {
        modal.classList.remove('hidden');
        titleInput?.focus();
    }
}

function hideModal() {
    if (modal) {
        modal.classList.add('hidden');
        form?.reset();
        typeInput.value = '';
        partInput.value = '';
        isFullSetInput.value = 'false';
    }
}

// Global functions for button clicks
function createSet(type, part) {
    console.log('Creating set:', type, part);
    if (!typeInput || !partInput || !isFullSetInput || !titleInput) return;
    
    typeInput.value = type;
    partInput.value = part;
    isFullSetInput.value = 'false';
    
    titleInput.value = `${type.charAt(0).toUpperCase() + type.slice(1)} Part ${part}: ${PART_NAMES[type][part]}`;
    showModal();
}

function createFullSet(type) {
    console.log('Creating full set:', type);
    if (!typeInput || !isFullSetInput || !titleInput) return;
    
    typeInput.value = type;
    isFullSetInput.value = 'true';
    titleInput.value = `Full ${type.charAt(0).toUpperCase() + type.slice(1)} Test`;
    showModal();
}

function closeCreateModal() {
    hideModal();
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    modal = document.getElementById('createSetModal');
    form = document.getElementById('createSetForm');
    typeInput = document.getElementById('setType');
    partInput = document.getElementById('setPart');
    isFullSetInput = document.getElementById('isFullSet');
    titleInput = form?.querySelector('input[name="title"]');
    descInput = form?.querySelector('textarea[name="description"]');

    if (!modal || !form) {
        console.error('Required elements not found');
        return;
    }

    // Add click handlers for create set buttons
    document.querySelectorAll('[data-action="create-set"]').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            const part = parseInt(this.dataset.part);
            console.log('Create set clicked:', type, part);
            
            typeInput.value = type;
            partInput.value = part;
            isFullSetInput.value = 'false';
            
            titleInput.value = `${type.charAt(0).toUpperCase() + type.slice(1)} Part ${part}: ${PART_NAMES[type][part]}`;
            showModal();
        });
    });

    // Add click handlers for create full set buttons
    document.querySelectorAll('[data-action="create-full-set"]').forEach(button => {
        button.addEventListener('click', function() {
            const type = this.dataset.type;
            console.log('Create full set clicked:', type);
            
            typeInput.value = type;
            isFullSetInput.value = 'true';
            titleInput.value = `Full ${type.charAt(0).toUpperCase() + type.slice(1)} Test`;
            showModal();
        });
    });

    // Initialize ESC key handler
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            hideModal();
        }
    });

    // Click outside modal to close
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            hideModal();
        }
    });

    // Form submission handler
    form.addEventListener('submit', function(e) {
        const type = typeInput.value;
        const part = partInput.value;
        const isFullSet = isFullSetInput.value === 'true';
        
        if (!type) {
            e.preventDefault();
            alert('Please specify the type of set to create');
            return;
        }

        if (!isFullSet && !part) {
            e.preventDefault();
            alert('Please specify which part to create');
            return;
        }

        console.log('Submitting form:', {
            type,
            part,
            isFullSet,
            title: titleInput.value,
            description: descInput.value
        });
    });
});
</script>
@endpush
