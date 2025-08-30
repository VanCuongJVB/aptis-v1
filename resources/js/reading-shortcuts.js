// Add to @push('scripts') section in practice.blade.php
document.addEventListener('alpine:init', () => {
    Alpine.data('keyboardShortcuts', () => ({
        init() {
            document.addEventListener('keydown', (e) => {
                // Don't handle shortcuts if in input/textarea
                if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                    return;
                }

                // Option selection (1-4 or A-D)
                if (!this.showAnswer) {
                    if (e.key >= '1' && e.key <= '4') {
                        const optionIndex = parseInt(e.key) - 1;
                        const options = this.$root.querySelectorAll('[x-data] button:not([disabled])');
                        if (options[optionIndex]) {
                            options[optionIndex].click();
                        }
                    } else if (e.key >= 'a' && e.key <= 'd') {
                        const optionIndex = e.key.charCodeAt(0) - 97;
                        const options = this.$root.querySelectorAll('[x-data] button:not([disabled])');
                        if (options[optionIndex]) {
                            options[optionIndex].click();
                        }
                    }
                }

                // Submit answer (Enter)
                if (e.key === 'Enter' && this.selectedOption && !this.showAnswer) {
                    this.submitAnswer();
                }

                // Next question (→ or N)
                if ((e.key === 'ArrowRight' || e.key.toLowerCase() === 'n') && this.showAnswer) {
                    this.nextQuestion();
                }

                // Flag question (F)
                if (e.key.toLowerCase() === 'f') {
                    this.toggleFlag();
                }

                // Exit practice (Esc)
                if (e.key === 'Escape') {
                    if (confirm('Are you sure you want to exit? Your progress will be saved.')) {
                        window.location.href = '{{ route("reading.drill.sets", ["part" => $question->part]) }}';
                    }
                }
            });
        }
    }));
});
