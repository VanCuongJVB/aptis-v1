@once
@push('scripts')
<script>
    (function(){
        console.log("📋 SIMPLIFIED DEBUG OUTPUT 📋");
        
        try {
            // Get saved answers
            var attemptId = {{ $attempt->id }};
            var key = 'attempt_answers_' + attemptId;
            var rawAnswers = localStorage.getItem(key);
            var answers = rawAnswers ? JSON.parse(rawAnswers) : null;
            
            if (!answers && window.attemptAnswers) {
                answers = window.attemptAnswers;
            }
            
            if (!answers) {
                console.error("No answers found in any source!");
                return;
            }
            
            // Create simplified debug view
            console.log("ANSWER DATA SUMMARY:");
            console.log("-------------------");
            
            Object.keys(answers).forEach(function(qid) {
                var answer = answers[qid];
                var part = null;
                
                // Find question element 
                var qEl = document.querySelector('.question-block[data-qid="' + qid + '"]');
                if (qEl) {
                    part = qEl.getAttribute('data-part');
                }
                
                var hasMetadata = answer.metadata !== undefined;
                var hasSelectedOptionId = answer.selected_option_id !== undefined && answer.selected_option_id !== null;
                var hasInlineFeedback = document.querySelector('.inline-feedback[data-qid-feedback="' + qid + '"]') !== null;
                
                // Extract user answer based on part
                var userAnswer = null;
                if (answer.metadata) {
                    if (typeof answer.metadata === 'object') {
                        userAnswer = answer.metadata.user_answer || answer.metadata.selected;
                    }
                }
                
                console.log("Question " + qid + ":");
                console.log("  Part: " + part);
                console.log("  Has metadata: " + hasMetadata);
                console.log("  Has selected option ID: " + hasSelectedOptionId);
                console.log("  Has inline feedback container: " + hasInlineFeedback);
                console.log("  User answer:", userAnswer);
                console.log("-------------------");
            });
            
        } catch(e) {
            console.error("Error in simplified debug:", e);
        }
    })();
</script>
@endpush
@endonce