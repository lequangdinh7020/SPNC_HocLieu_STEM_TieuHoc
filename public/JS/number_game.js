document.addEventListener('DOMContentLoaded', function() {
    let gameState = {
        correct: 0,
        wrong: 0,
        timeLeft: 300,
        isPlaying: false,
        isPaused: false,
        timerInterval: null,
        answers: {},
        correctAnswers: {},
        checkedItems: {},
        gameSaved: false
    };
    
    initGame();
    
    document.getElementById('startGameButton').addEventListener('click', startGame);
    
    document.getElementById('giveUpButton').addEventListener('click', giveUpGame);
    
    document.getElementById('resetButton').addEventListener('click', resetGame);
    
    document.getElementById('pauseButton').addEventListener('click', togglePause);
    
    document.getElementById('completeButton').addEventListener('click', completeGame);
    
    document.getElementById('checkAnswersButton').addEventListener('click', checkAnswers);
    
    document.getElementById('clearAnswersButton').addEventListener('click', clearAnswers);
    
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
                    showFeedback('⏰ HẾT THỜI GIAN! Đang tự động lưu điểm...', 'wrong');
                    setTimeout(() => {
                        saveGameScore('Hết thời gian!');
                    }, 1000);
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
        
        let newCorrectCount = 0;
        let newWrongCount = 0;
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
                    if (!gameState.checkedItems[i] || gameState.checkedItems[i].result !== 'correct') {
                        newCorrectCount++;
                    }
                    gameState.checkedItems[i] = { result: 'correct', answer: userAnswer };
                } else {
                    input.classList.add('wrong');
                    if (!gameState.checkedItems[i] || gameState.checkedItems[i].result !== 'wrong') {
                        newWrongCount++;
                    }
                    gameState.checkedItems[i] = { result: 'wrong', answer: userAnswer };
                }
            }
        }
        
        if (answeredCount > 0) {
            gameState.correct += newCorrectCount;
            gameState.wrong += newWrongCount;
            
            updateUI();
        }
        
        if (answeredCount === 0) {
            showFeedback('Hãy nhập ít nhất một câu trả lời trước khi kiểm tra!', 'neutral');
        } else {
            let totalCorrect = 0;
            for (let i = 1; i <= 20; i++) {
                if (gameState.checkedItems[i] && gameState.checkedItems[i].result === 'correct') {
                    totalCorrect++;
                }
            }
            showFeedback(`Kiểm tra xong! Hiện tại: ${totalCorrect} ô đúng (từ ${answeredCount} ô đã nhập).`, 
                        totalCorrect === answeredCount ? 'correct' : 'neutral');
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
    
    function giveUpGame() {
        if (confirm('Bạn có chắc chắn muốn thoát về Menu?')) {
            const menuUrl = (window.baseUrl || '') + '/views/lessons/math.php';
            window.location.href = menuUrl;
        }
    }

    function saveGameScore(endMessage) {
        if (gameState.gameSaved) {
            if (endMessage) {
                showFeedback(endMessage + ' (Điểm đã được lưu trước đó)', 'neutral');
            }
            return;
        }

        let correctCount = 0;
        let answeredCount = 0;
        for (let i = 1; i <= 20; i++) {
            const userAnswer = gameState.answers[i];
            const correctAnswer = gameState.correctAnswers[i] || 0;
            
            if (userAnswer !== null && userAnswer !== undefined) {
                answeredCount++;
                if (userAnswer === correctAnswer) {
                    correctCount++;
                }
            }
        }

        const accuracy = Math.round((correctCount / 20) * 100);

        const resultMsg = `Tổng kết: ${correctCount}/20 câu đúng. Độ chính xác: ${accuracy}%`;
        showFeedback(resultMsg, accuracy >= 70 ? 'correct' : 'wrong');

        try {
            const apiUrl = (window.baseUrl || '') + '/views/lessons/update-number-score';
            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'commit', score_pct: accuracy })
            }).then(resp => resp.json()).then(json => {
                if (json && json.success) {
                    gameState.gameSaved = true;
                    if (json.xp_awarded) {
                        showFeedback('✓ Lưu điểm thành công! Bạn nhận được +' + json.xp_awarded + ' XP!', 'correct');
                    } else {
                        showFeedback('✓ Lưu điểm thành công!', 'correct');
                    }
                } else {
                    if (json && json.message) {
                        showFeedback('⚠ Lưu điểm: ' + json.message, 'wrong');
                    }
                }
            }).catch(err => {
                console.error('Commit error', err);
                showFeedback('⚠ Lỗi khi lưu điểm: ' + err.message, 'wrong');
            });
        } catch (e) {
            console.error('Commit exception', e);
            showFeedback('⚠ Lỗi: ' + e.message, 'wrong');
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
        
        saveGameScore('Hoàn thành game!');
    }
    
    function resetGame() {
        if (confirm('Bạn có chắc chắn muốn chơi lại từ đầu?')) {
            gameState = {
                correct: 0,
                wrong: 0,
                timeLeft: 300,
                isPlaying: false,
                isPaused: false,
                timerInterval: null,
                answers: {},
                correctAnswers: gameState.correctAnswers,
                checkedItems: {},
                gameSaved: false
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
                               'checkAnswersButton', 'clearAnswersButton'];
        
        controlButtons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) button.disabled = false;
        });
    }
    
    function disableControls() {
        const controlButtons = ['giveUpButton', 'pauseButton', 'completeButton', 
                               'checkAnswersButton'];
        
        controlButtons.forEach(buttonId => {
            const button = document.getElementById(buttonId);
            if (button) button.disabled = true;
        });
    }
    
    function updateUI() {
        document.getElementById('correctCount').textContent = gameState.correct;
        document.getElementById('wrongCount').textContent = gameState.wrong;
        
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