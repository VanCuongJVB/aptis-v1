{{-- Part 4: Reading for Purpose (Reordering) --}}
<div class="space-y-6" x-data="sentenceReordering">
    {{-- Instructions and Context --}}
    <div class="prose max-w-none mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="text-lg font-medium mb-2">{{ $question->stem }}</h3>
            @if(isset($question->meta['context']))
            <p class="text-gray-600">{{ $question->meta['context'] }}</p>
            @endif
        </div>
    </div>

    {{-- Draggable Sentences List --}}
    <div class="space-y-3">
        <template x-for="(sentence, index) in sentences" :key="sentence.id">
            <div class="flex items-center space-x-3">
                <span class="text-gray-500" x-text="index + 1 + '.'"></span>
                <div class="flex-1 bg-white rounded-lg border p-4 cursor-move"
                     :class="{
                         'border-indigo-500': isDragging === sentence.id,
                         'bg-green-50 border-green-500': showAnswer && isCorrectPosition(index, sentence.id),
                         'bg-red-50 border-red-500': showAnswer && !isCorrectPosition(index, sentence.id)
                     }"
                     draggable="true"
                     @dragstart="startDrag($event, sentence.id)"
                     @dragend="endDrag"
                     @dragover.prevent
                     @dragenter.prevent
                     @drop="drop($event, index)">
                    <div class="prose max-w-none" x-text="sentence.text"></div>
                </div>
            </div>
        </template>
    </div>

    {{-- Original Text (shown after answering) --}}
    <div x-show="showAnswer" x-transition class="mt-6">
        <h4 class="text-lg font-medium mb-3">Original Text:</h4>
        <div class="prose max-w-none bg-green-50 rounded-lg p-4">
            <template x-for="(sentence, index) in correctOrder" :key="sentence.id">
                <p class="mb-2" x-text="sentence.text"></p>
            </template>
        </div>
    </div>

    {{-- Text Structure Analysis (shown after answering) --}}
    @if(isset($question->meta['structure_analysis']))
    <div x-show="showAnswer" x-transition class="mt-4 bg-blue-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-blue-800 mb-2">Text Structure Analysis:</h4>
        <div class="prose max-w-none text-blue-900 text-sm">
            {!! $question->meta['structure_analysis'] !!}
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('sentenceReordering', () => ({
        sentences: @json($question->meta['sentences']),
        correctOrder: @json($question->meta['correct_order']),
        isDragging: null,
        
        init() {
            // Randomize sentences order initially
            if (!this.showAnswer && !this.selectedOption) {
                this.sentences = this.shuffleArray([...this.sentences]);
            }
        },

        startDrag(event, id) {
            this.isDragging = id;
            event.dataTransfer.effectAllowed = 'move';
        },

        endDrag() {
            this.isDragging = null;
        },

        drop(event, targetIndex) {
            event.preventDefault();
            const sourceIndex = this.sentences.findIndex(s => s.id === this.isDragging);
            if (sourceIndex !== -1) {
                const [movedItem] = this.sentences.splice(sourceIndex, 1);
                this.sentences.splice(targetIndex, 0, movedItem);
                this.updateAnswer();
            }
        },

        updateAnswer() {
            // Convert current order to answer format
            const answerOrder = this.sentences.map(s => s.id).join(',');
            this.selectedOption = answerOrder;
        },

        isCorrectPosition(index, sentenceId) {
            return this.correctOrder[index].id === sentenceId;
        },

        shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }
    }));
});
</script>
@endpush
