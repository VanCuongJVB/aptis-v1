@extends('layouts.app')
@section('title', 'Create Reading Question - Part 1')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Create Reading Question - Part 1</h1>

        <form action="{{ route('admin.reading.part1.store', $quiz) }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="part" value="1">
            <input type="hidden" name="type" value="reading_gap_fill">

            {{-- Instructions --}}
            <div>
                <label class="block text-sm font-medium mb-1">Instructions</label>
                <textarea name="stem" rows="2" 
                          class="w-full border rounded px-3 py-2">Choose the word that fits in the gap.</textarea>
            </div>

            {{-- Context Text --}}
            <div>
                <label class="block text-sm font-medium mb-1">Reading Text</label>
                <p class="text-sm text-gray-500 mb-1">Use [gap1], [gap2], etc. to mark where words should be filled.</p>
                <textarea name="context_text" rows="8" 
                          class="w-full border rounded px-3 py-2 font-mono">Hey Lewis,

It is a [gap1] day. 
I need the [gap2] of the report. 
Can you print a [gap3] for me?
I am [gap4] with my work.
I will have meetings with my [gap5].

Thanks,
Louis.</textarea>
            </div>

            {{-- Gap Questions --}}
            <div x-data="{ gaps: [] }" x-init="gaps = Array(5).fill().map((_, i) => ({ 
                id: i + 1,
                sentence: '',
                options: ['', '', ''],
                correct_answer: ''
            }))">
                <label class="block text-sm font-medium mb-4">Gap Questions</label>

                <template x-for="(gap, index) in gaps" :key="gap.id">
                    <div class="border rounded p-4 mb-4">
                        <div class="font-medium mb-2" x-text="`Gap ${gap.id}`"></div>
                        
                        {{-- Sentence --}}
                        <div class="mb-3">
                            <label class="block text-sm mb-1">Sentence with Gap</label>
                            <input type="text" :name="`meta[gaps][${index}][sentence]`"
                                   x-model="gap.sentence"
                                   class="w-full border rounded px-3 py-2">
                        </div>

                        {{-- Options --}}
                        <div class="grid grid-cols-3 gap-4 mb-3">
                            <template x-for="(_, optIndex) in [0,1,2]" :key="optIndex">
                                <div>
                                    <label class="block text-sm mb-1" 
                                           x-text="`Option ${optIndex + 1}`"></label>
                                    <input type="text" 
                                           :name="`meta[gaps][${index}][options][]`"
                                           x-model="gap.options[optIndex]"
                                           class="w-full border rounded px-3 py-2">
                                </div>
                            </template>
                        </div>

                        {{-- Correct Answer --}}
                        <div>
                            <label class="block text-sm mb-1">Correct Answer</label>
                            <select :name="`meta[gaps][${index}][correct_answer]`"
                                    x-model="gap.correct_answer"
                                    class="border rounded px-3 py-2">
                                <option value="">Select correct answer</option>
                                <template x-for="opt in gap.options" :key="opt">
                                    <option x-text="opt" :value="opt"
                                            x-show="opt.trim() !== ''"></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Explanation --}}
            <div>
                <label class="block text-sm font-medium mb-1">Explanation (Optional)</label>
                <textarea name="explanation" rows="3" 
                          class="w-full border rounded px-3 py-2"
                          placeholder="Explain why these are the correct answers..."></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Create Question
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gapForm', () => ({
            gaps: Array(5).fill().map((_, i) => ({
                id: i + 1,
                sentence: '',
                options: ['', '', ''],
                correct_answer: ''
            }))
        }))
    })
</script>
@endpush
@endsection
