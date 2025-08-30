@extends('layouts.app')

@section('title', 'Create Reading Question')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Create Reading Question</h1>

        <form action="{{ route('admin.reading.part1.store', $quiz) }}" method="POST" id="readingForm">
            @csrf
            
            {{-- Part Selection --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Reading Part</label>
                <select name="part" class="w-full border rounded px-3 py-2" required v-model="selectedPart">
                    <option value="1">Part 1 - Sentence Matching</option>
                    <option value="2">Part 2 - Notice Matching</option>
                    <option value="3">Part 3 - Long Text Reading</option>
                    <option value="4">Part 4 - Gap Filling</option>
                </select>
            </div>

            <input type="hidden" name="type" :value="questionType">

            {{-- Question Stem --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Question Instructions</label>
                <textarea name="stem" class="w-full border rounded px-3 py-2" rows="3" required>{{ old('stem') }}</textarea>
            </div>

            {{-- Part Specific Inputs --}}
            <div v-if="selectedPart == 1">
                {{-- Part 1: Sentence Matching --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Sentences to Match</label>
                    <div v-for="(sentence, index) in meta.sentences" :key="index" class="flex gap-2 mb-2">
                        <input type="text" :name="'meta[sentences][]'" v-model="meta.sentences[index]" 
                               class="flex-1 border rounded px-3 py-2" placeholder="Enter sentence">
                        <button type="button" @click="removeSentence(index)" 
                                class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                    </div>
                    <button type="button" @click="addSentence" 
                            class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Sentence</button>
                </div>

                {{-- Matching Options --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Matching Options</label>
                    <div v-for="(option, index) in options" :key="index" class="flex gap-2 mb-2">
                        <input type="text" :name="'options['+index+'][label]'" v-model="options[index].label" 
                               class="flex-1 border rounded px-3 py-2" placeholder="Enter matching option">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" :name="'options['+index+'][is_correct]'" 
                                   v-model="options[index].is_correct">
                            Correct
                        </label>
                        <button type="button" @click="removeOption(index)" 
                                class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                    </div>
                    <button type="button" @click="addOption" 
                            class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Option</button>
                </div>
            </div>

            <div v-else-if="selectedPart == 2">
                {{-- Part 2: Notice Matching --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Notices</label>
                    <div v-for="(notice, index) in meta.notices" :key="index" class="flex gap-2 mb-2">
                        <textarea :name="'meta[notices][]'" v-model="meta.notices[index]" 
                                  class="flex-1 border rounded px-3 py-2" rows="2" 
                                  placeholder="Enter notice text"></textarea>
                        <button type="button" @click="removeNotice(index)" 
                                class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                    </div>
                    <button type="button" @click="addNotice" 
                            class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Notice</button>
                </div>

                {{-- Notice Questions --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Questions about Notices</label>
                    <div v-for="(option, index) in options" :key="index" class="flex gap-2 mb-2">
                        <input type="text" :name="'options['+index+'][label]'" v-model="options[index].label" 
                               class="flex-1 border rounded px-3 py-2" placeholder="Enter question">
                        <select :name="'options['+index+'][correct_notice]'" v-model="options[index].correct_notice" 
                                class="border rounded px-3 py-2">
                            <option v-for="(_, i) in meta.notices" :value="i">Notice @{{ i + 1 }}</option>
                        </select>
                        <button type="button" @click="removeOption(index)" 
                                class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                    </div>
                    <button type="button" @click="addOption" 
                            class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Question</button>
                </div>
            </div>

            <div v-else-if="selectedPart == 3">
                {{-- Part 3: Long Text Reading --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Reading Text</label>
                    <textarea name="meta[reading_text]" v-model="meta.reading_text" 
                              class="w-full border rounded px-3 py-2" rows="10" 
                              placeholder="Enter the reading passage"></textarea>
                </div>

                {{-- Multiple Choice Questions --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Questions</label>
                    <div v-for="(question, qIndex) in options" :key="qIndex" class="border p-4 rounded mb-4">
                        <input type="text" :name="'options['+qIndex+'][label]'" v-model="question.label" 
                               class="w-full border rounded px-3 py-2 mb-2" placeholder="Enter question">
                        
                        <div v-for="(choice, cIndex) in question.choices" :key="cIndex" class="flex gap-2 mb-2">
                            <input type="text" :name="'options['+qIndex+'][choices]['+cIndex+'][text]'" 
                                   v-model="choice.text" class="flex-1 border rounded px-3 py-2" 
                                   placeholder="Choice text">
                            <label class="flex items-center gap-2">
                                <input type="radio" :name="'options['+qIndex+'][correct_choice]'" 
                                       :value="cIndex" v-model="question.correct_choice">
                                Correct
                            </label>
                            <button type="button" @click="removeChoice(qIndex, cIndex)" 
                                    class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                        </div>
                        <button type="button" @click="addChoice(qIndex)" 
                                class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Choice</button>
                    </div>
                    <button type="button" @click="addQuestion" 
                            class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Question</button>
                </div>
            </div>

            <div v-else-if="selectedPart == 4">
                {{-- Part 4: Gap Filling --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Text with Gaps</label>
                    <p class="text-sm text-gray-600 mb-2">Use [gap] to mark where words should be filled in</p>
                    <textarea name="meta[gap_text]" v-model="meta.gap_text" 
                              class="w-full border rounded px-3 py-2" rows="10" 
                              placeholder="Enter text with [gap] markers"></textarea>
                </div>

                {{-- Gap Options --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Words for Gaps</label>
                    <div v-for="(word, index) in meta.gap_options" :key="index" class="flex gap-2 mb-2">
                        <input type="text" :name="'meta[gap_options][]'" v-model="meta.gap_options[index]" 
                               class="flex-1 border rounded px-3 py-2" placeholder="Enter word">
                        <button type="button" @click="removeGapOption(index)" 
                                class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                    </div>
                    <button type="button" @click="addGapOption" 
                            class="px-4 py-2 bg-blue-500 text-white rounded mt-2">Add Word</button>
                </div>

                {{-- Correct Answers for Gaps --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Correct Answers</label>
                    <div v-for="(_, index) in getGapCount()" :key="index" class="flex gap-2 mb-2">
                        <span class="py-2">Gap @{{ index + 1 }}:</span>
                        <select :name="'options['+index+'][correct_word]'" v-model="options[index].correct_word" 
                                class="flex-1 border rounded px-3 py-2">
                            <option v-for="word in meta.gap_options" :value="word">@{{ word }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Explanation --}}
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Explanation (Optional)</label>
                <textarea name="explanation" class="w-full border rounded px-3 py-2" rows="3">{{ old('explanation') }}</textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded">
                    Create Question
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const app = {
    data() {
        return {
            selectedPart: 1,
            meta: {
                sentences: [],
                notices: [],
                reading_text: '',
                gap_text: '',
                gap_options: []
            },
            options: []
        }
    },
    computed: {
        questionType() {
            const types = {
                1: 'reading_sentence_matching',
                2: 'reading_notice_matching',
                3: 'reading_long_text',
                4: 'reading_gap_filling'
            };
            return types[this.selectedPart] || '';
        }
    },
    methods: {
        // Part 1 methods
        addSentence() {
            this.meta.sentences.push('');
        },
        removeSentence(index) {
            this.meta.sentences.splice(index, 1);
        },
        addOption() {
            this.options.push({ label: '', is_correct: false });
        },
        removeOption(index) {
            this.options.splice(index, 1);
        },

        // Part 2 methods
        addNotice() {
            this.meta.notices.push('');
        },
        removeNotice(index) {
            this.meta.notices.splice(index, 1);
        },

        // Part 3 methods
        addQuestion() {
            this.options.push({
                label: '',
                choices: [],
                correct_choice: null
            });
        },
        addChoice(questionIndex) {
            this.options[questionIndex].choices.push({ text: '' });
        },
        removeChoice(questionIndex, choiceIndex) {
            this.options[questionIndex].choices.splice(choiceIndex, 1);
        },

        // Part 4 methods
        addGapOption() {
            this.meta.gap_options.push('');
        },
        removeGapOption(index) {
            this.meta.gap_options.splice(index, 1);
        },
        getGapCount() {
            return (this.meta.gap_text.match(/\[gap\]/g) || []).length;
        },

        // Reset form when part changes
        resetForm() {
            this.meta = {
                sentences: [],
                notices: [],
                reading_text: '',
                gap_text: '',
                gap_options: []
            };
            this.options = [];
        }
    },
    watch: {
        selectedPart() {
            this.resetForm();
        }
    },
    mounted() {
        // Initialize with one empty item
        this.addOption();
    }
};

Vue.createApp(app).mount('#readingForm');
</script>
@endpush
