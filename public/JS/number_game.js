document.addEventListener('DOMContentLoaded', function() {
    let gameState = {
        correct: 0,
        wrong: 0,
        remaining: 197,
        timeLeft: 231, 
        currentSet: 0,
        totalSets: 5,
        isPlaying: false,
        isPaused: false,
        timerInterval: null,
        answers: {},
        correctAnswers: {}
    };
    
    initGame();
    
    document.getElementById('startGameButton').addEventListener('click', startGame);
    
    document.getElementById('giveUpButton').addEventListener('click', giveUpGame);
    
    document.getElementById('resetButton').addEventListener('click', resetGame);
    
    document.getElementById('pauseButton').addEventListener('click', togglePause);
    
    document.getElementById('completeButton').addEventListener('click', completeGame);
    
    document.getElementById('checkAnswersButton').addEventListener('click', checkAnswers);
    
    document.getElementById('clearAnswersButton').addEventListener('click', clearAnswers);
    
    document.getElementById('prevButton').addEventListener('click', prevSet);
    document.getElementById('nextButton').addEventListener('click', nextSet);
    
    function initGame() {
        const introModal = document.getElementById('intro-modal');
        if (introModal.classList.contains('active')) {}
        
        createNumberGrid();
        
        createAnswerGrid();
        
        calculateCorrectAnswers();
        
        updateUI();
    }
    
    function createNumberGrid() {
        const numberGrid = document.getElementById('numberGrid');
        numberGrid.innerHTML = '';

        const numberData = window.numberData || [];
        
        numberData.forEach(row => {
            row.forEach(number => {
                const cell = document.createElement('div');
                cell.className = 'number-cell';
                cell.textContent = number;
                cell.dataset.number = number;
                
                cell.addEventListener('click', function() {
                    highlightAnswerInput(number);
                });
                
                numberGrid.appendChild(cell);
            });
        });
    }
    
    function createAnswerGrid() {
        const answerGrid = document.getElementById('answerGrid');
        answerGrid.innerHTML = '';
        
        for (let i = 1; i <= 20; i++) {
            const answerItem = document.createElement('div');
            answerItem.className = 'answer-item';
            
            const label = document.createElement('span');
            label.className = 'answer-label';
            label.textContent = `Số ${i}`;
            
            const input = document.createElement('input');
            input.type = 'number';
            input.className = 'answer-input';
            input.id = `answer-${i}`;
            input.dataset.number = i;
            input.min = 0;
            input.max = 100;
            input.value = gameState.answers[i] || '';
            
            input.addEventListener('input', function() {
                const number = parseInt(this.dataset.number);
                const value = this.value.trim() === '' ? null : parseInt(this.value);
                gameState.answers[number] = value;
                
                this.classList.remove('correct', 'wrong');
            });
            
            answerItem.addEventListener('click', function() {
                input.focus();
            });
            
            answerItem.appendChild(label);
            answerItem.appendChild(input);
            answerGrid.appendChild(answerItem);
        }
    }
    
    function calculateCorrectAnswers() {
        const numberData = window.numberData || [];
        const counts = {};
        
        numberData.forEach(row => {
            row.forEach(number => {
                counts[number] = (counts[number] || 0) + 1;
            });
        });
        
        gameState.correctAnswers = counts;
    }
    
    function startGame() {
        const introModal = document.getElementById('intro-modal');
        introModal.classList.remove('active');
        
        gameState.isPlaying = true;
        gameState.isPaused = false;
        
        startTimer();
        
        enableControls();
        
        showFeedback('Bắt đầu! Hãy đếm số thật nhanh và chính xác!', 'neutral');
    }
    
    function startTimer() {
        if (gameState.timerInterval) {
            clearInterval(gameState.timerInterval);
        }
        
        gameState.timerInterval = setInterval(function() {
            if (!gameState.isPaused && gameState.isPlaying) {
                gameState.timeLeft--;
                
                updateTimerDisplay();
                
                if (gameState.timeLeft <= 0) {
                    clearInterval(gameState.timerInterval);
                    endGame();
                    showFeedback('Hết thời gian! Game kết thúc.', 'wrong');
                }
            }
        }, 1000);
    }
    
    function updateTimerDisplay() {
        const minutes = Math.floor(gameState.timeLeft / 60);
        const seconds = gameState.timeLeft % 60;
        const timerDisplay = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        document.getElementById('timer').textContent = timerDisplay;
    }
    
    function checkAnswers() {
        if (!gameState.isPlaying) {
            showFeedback('Hãy bắt đầu game trước khi kiểm tra!', 'neutral');
            return;
        }
        
        let allCorrect = true;
        let answeredCount = 0;
        
        for (let i = 1; i <= 20; i++) {
            const input = document.getElementById(`answer-${i}`);
            const userAnswer = gameState.answers[i];
            const correctAnswer = gameState.correctAnswers[i] || 0;
        
            input.classList.remove('correct', 'wrong');
            
            if (userAnswer !== null && userAnswer !== undefined) {
                answeredCount++;
                
                if (userAnswer === correctAnswer) {
                    input.classList.add('correct');
                } else {
                    input.classList.add('wrong');
                    allCorrect = false;
                }
            }
        }
        
        if (answeredCount > 0) {
            const correctCount = Object.keys(gameState.answers).filter(num => 
                gameState.answers[num] === gameState.correctAnswers[num]
            ).length;
            
            const wrongCount = answeredCount - correctCount;
            
            gameState.correct += correctCount;
            gameState.wrong += wrongCount;
            gameState.remaining = Math.max(0, gameState.remaining - answeredCount);
            
            updateUI();
        }
        
        if (answeredCount === 0) {
            showFeedback('Hãy nhập ít nhất một câu trả lời trước khi kiểm tra!', 'neutral');
        } else if (allCorrect) {
            showFeedback('Tuyệt vời! Tất cả câu trả lời đều chính xác!', 'correct');
        } else {
            showFeedback('Còn một số câu trả lời chưa đúng. Hãy thử lại!', 'wrong');
        }
    }
    
    function clearAnswers() {
        for (let i = 1; i <= 20; i++) {
            const input = document.getElementById(`answer-${i}`);
            input.value = '';
            input.classList.remove('correct', 'wrong');
            
            gameState.answers[i] = null;
        }
        
        showFeedback('Đã xóa tất cả câu trả lời.', 'neutral');
    }
    
    function togglePause() {
        if (!gameState.isPlaying) return;
        
        gameState.isPaused = !gameState.isPaused;
        
        const pauseButton = document.getElementById('pauseButton');
        const pauseIcon = pauseButton.querySelector('i');
        
        if (gameState.isPaused) {
            pauseButton.innerHTML = '<i class="fas fa-play"></i> Tiếp tục';
            showFeedback('Game đã tạm dừng.', 'neutral');
        } else {
            pauseButton.innerHTML = '<i class="fas fa-pause"></i> Tạm dừng';
            showFeedback('Game đã tiếp tục.', 'neutral');
        }
    }
    
    function prevSet() {
        if (gameState.currentSet > 0) {
            gameState.currentSet--;
            showFeedback(`Đã chuyển đến set ${gameState.currentSet + 1}`, 'neutral');
        }
    }
    
    function nextSet() {
        if (gameState.currentSet < gameState.totalSets - 1) {
            gameState.currentSet++;
            showFeedback(`Đã chuyển đến set ${gameState.currentSet + 1}`, 'neutral');
        }
    }
    
    function giveUpGame() {
        if (confirm('Bạn có chắc chắn muốn bỏ cuộc? Tất cả tiến trình sẽ bị mất.')) {
            endGame();
            showFeedback('Bạn đã bỏ cuộc. Hãy thử lại lần sau!', 'wrong');
        }
    }
    
    function completeGame() {
        if (!gameState.isPlaying) {
            showFeedback('Hãy bắt đầu game trước!', 'neutral');
            return;
        }
        
        const unanswered = Object.keys(gameState.answers).filter(num => 
            gameState.answers[num] === null || gameState.answers[num] === undefined
        ).length;
        
        if (unanswered > 0 && !confirm(`Bạn còn ${unanswered} câu chưa trả lời. Bạn có chắc chắn muốn nộp bài?`)) {
            return;
        }
        
        endGame();
        
        const accuracy = gameState.correct + gameState.wrong > 0 
            ? Math.round((gameState.correct / (gameState.correct + gameState.wrong)) * 100) 
            : 0;
        
        showFeedback(`Hoàn thành! Điểm số: ${gameState.correct} đúng, ${gameState.wrong} sai. Độ chính xác: ${accuracy}%`, 
                    accuracy >= 70 ? 'correct' : 'wrong');

        // Commit to server: allow repeated saves and award XP (server will return xp_awarded)
        try {
            fetch('/views/lessons/update-number-score', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'commit', score_pct: accuracy })
            }).then(resp => resp.json()).then(json => {
                if (json && json.success) {
                    if (json.xp_awarded) {
                        showFeedback('Hoàn thành - Bạn nhận được +' + json.xp_awarded + ' XP!', 'correct');
                    }
                } else {
                    // show server message if any
                    if (json && json.message) showFeedback('Lưu điểm: ' + json.message, 'neutral');
                }
            }).catch(err => {
                console.error('Commit error', err);
            });
        } catch (e) {
            console.error('Commit exception', e);
        }
    }
    
    function resetGame() {
        if (confirm('Bạn có chắc chắn muốn chơi lại từ đầu?')) {
            gameState = {
                correct: 0,
                wrong: 0,
                remaining: 197,
                timeLeft: 231,
                currentSet: 0,
                totalSets: 5,
                isPlaying: false,
                isPaused: false,
                timerInterval: null,
                answers: {},
                correctAnswers: gameState.correctAnswers
            };
            
            if (gameState.timerInterval) {
                clearInterval(gameState.timerInterval);
            }
            
            clearAnswers();
            updateUI();
            
            document.getElementById('intro-modal').classList.add('active');
            
            showFeedback('Game đã được reset. Hãy bắt đầu lại!', 'neutral');
        }
    }
    
    function endGame() {
        gameState.isPlaying = false;
        
        if (gameState.timerInterval) {
            clearInterval(gameState.timerInterval);
        }
        
        disableControls();
    }
    
    function enableControls() {
        const controlButtons = ['giveUpButton', 'resetButton', 'pauseButton', 'completeButton', 
                               'checkAnswersButton', 'clearAnswersButton', 'prevButton', 'nextButton'];
        
        controlButtons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) button.disabled = false;
        });
    }
    
    function disableControls() {
        const controlButtons = ['giveUpButton', 'pauseButton', 'completeButton', 
                               'checkAnswersButton', 'prevButton', 'nextButton'];
        
        controlButtons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) button.disabled = true;
        });
    }
    
    function updateUI() {
        document.getElementById('correctCount').textContent = gameState.correct;
        document.getElementById('wrongCount').textContent = gameState.wrong;
        document.getElementById('questionsRemaining').textContent = gameState.remaining;
        document.getElementById('progressValue').textContent = `${gameState.currentSet + 1}/${gameState.totalSets}`;
        
        updateTimerDisplay();
    }
    
    function showFeedback(message, type) {
        const feedbackElement = document.getElementById('resultFeedback');
    
        feedbackElement.textContent = message;
        feedbackElement.className = 'result-feedback';
        feedbackElement.classList.add(type);
        
        if (type !== 'wrong' || !message.includes('Hết thời gian') && !message.includes('bỏ cuộc')) {
            setTimeout(() => {
                feedbackElement.classList.add('hidden');
            }, 5000);
        }
    }
    
    function highlightAnswerInput(number) {
        const input = document.getElementById(`answer-${number}`);
        if (input) {
            input.focus();
            
            const answerItem = input.closest('.answer-item');
            answerItem.classList.add('focused');
            
            setTimeout(() => {
                answerItem.classList.remove('focused');
            }, 1000);
        }
    }
    function initAdditionalFeatures() {
        const numberCells = document.querySelectorAll('.number-cell');
        numberCells.forEach(cell => {
            cell.addEventListener('mouseenter', function() {
                const number = this.dataset.number;
                highlightNumberInGrid(number);
            });
            
            cell.addEventListener('mouseleave', function() {
                removeNumberHighlight();
            });
        });
        
        document.addEventListener('keydown', function(event) {
            if (event.key >= '0' && event.key <= '9' && document.activeElement.classList.contains('answer-input')) {}
        });
    }
    
    function highlightNumberInGrid(number) {
        const cells = document.querySelectorAll(`.number-cell[data-number="${number}"]`);
        cells.forEach(cell => {
            cell.style.backgroundColor = '#cce5ff';
            cell.style.boxShadow = '0 0 10px rgba(0, 123, 255, 0.5)';
        });
    }
    
    function removeNumberHighlight() {
        const cells = document.querySelectorAll('.number-cell');
        cells.forEach(cell => {
            cell.style.backgroundColor = '';
            cell.style.boxShadow = '';
        });
    }
    
    initAdditionalFeatures();
});