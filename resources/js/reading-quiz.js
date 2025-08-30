// reading-quiz.js
export default {
    questions: [],
    currentQuestionIndex: 0,
    timeRemaining: 0, // in seconds
    answers: {},
    timer: null,

    init() {
        this.questions = this.$refs.questions ? JSON.parse(this.$refs.questions.value) : [];
        this.timeRemaining = this.$refs.timeLimit ? parseInt(this.$refs.timeLimit.value) * 60 : 3600;
        this.startTimer();
        
        // Handle beforeunload
        window.addEventListener('beforeunload', (e) => {
            if (Object.keys(this.answers).length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    },

    startTimer() {
        this.timer = setInterval(() => {
            if (this.timeRemaining > 0) {
                this.timeRemaining--;
            } else {
                this.submitQuiz();
            }
        }, 1000);
    },

    formatTime(seconds) {
        const hrs = Math.floor(seconds / 3600);
        const mins = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    },

    goToQuestion(index) {
        if (index >= 0 && index < this.questions.length) {
            this.currentQuestionIndex = index;
        }
    },

    nextQuestion() {
        if (this.currentQuestionIndex < this.questions.length - 1) {
            this.currentQuestionIndex++;
        }
    },

    prevQuestion() {
        if (this.currentQuestionIndex > 0) {
            this.currentQuestionIndex--;
        }
    },

    setAnswer(questionId, answer) {
        this.answers[questionId] = answer;
    },

    isQuestionAnswered(index) {
        const questionId = this.questions[index]?.id;
        return questionId && this.answers[questionId] !== undefined;
    },

    async submitQuiz() {
        if (!confirm('Are you sure you want to submit the quiz?')) {
            return;
        }

        try {
            const response = await fetch(this.$refs.submitUrl.value, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    answers: this.answers
                })
            });

            if (response.ok) {
                const result = await response.json();
                window.location.href = result.redirect_url;
            } else {
                alert('Error submitting quiz. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error submitting quiz. Please try again.');
        }
    },

    destroy() {
        if (this.timer) {
            clearInterval(this.timer);
        }
    }
};
