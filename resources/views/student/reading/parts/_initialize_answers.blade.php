@once
@push('scripts')
<script>
    // Helper function to ensure answers are loaded and accessible
    (function() {
        
        // Check if we have PHP-rendered attempt data
        var attemptId = {{ $attempt->id }};
        var key = 'attempt_answers_' + attemptId;
        
        // Create a backup of the PHP-rendered answers
        window.phpAnswers = @json($answers->keyBy('question_id')->toArray());
        
        // Check if we have answers in localStorage already
        var hasLocalStorage = false;
        try {
            var storedAnswers = localStorage.getItem(key);
            if (storedAnswers) {
                hasLocalStorage = true;
            }
        } catch (e) {
            console.log("Error reading localStorage:", e);
        }
        
        // If we don't have localStorage answers but we do have PHP-rendered answers,
        // create a localStorage version from the PHP data
        if (!hasLocalStorage && window.phpAnswers) {
            try {
                var formattedAnswers = {};
                Object.keys(window.phpAnswers).forEach(function(qid) {
                    var ans = window.phpAnswers[qid];
                    // Convert the PHP data structure to the expected format
                    formattedAnswers[qid] = {
                        is_correct: ans.is_correct,
                        metadata: ans.metadata,
                        selected_option_id: ans.selected_option_id
                    };
                });
                
                localStorage.setItem(key, JSON.stringify(formattedAnswers));
            } catch (e) {
                console.error("Error creating localStorage answers:", e);
            }
        }
        
        // Make the answers available globally
        window.attemptAnswers = window.phpAnswers;
    })();
</script>
@endpush
@endonce