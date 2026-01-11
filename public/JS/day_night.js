let currentQuestionIndex = 0;
let score = 0;
let totalXp = 0;
const attempts = [];
const quizContentEl = document.getElementById('quizContent');
const finalResultEl = document.getElementById('finalResult');
const progressFillEl = document.getElementById('progressFill');
const progressCounter = document.getElementById('progressCounter');

function updateProgressCounter() {
    const totalQuestions = quizData.length;
    if (progressCounter) {
        progressCounter.textContent = `${Math.min(currentQuestionIndex + 1, totalQuestions)}/${totalQuestions}`;
    }
}

function loadQuestion(index) {
    const totalQuestions = quizData.length;
    const progressPercent = ((index) / totalQuestions) * 100;
    progressFillEl.style.width = `${progressPercent}%`;
    updateProgressCounter();

    if (index >= totalQuestions) {
        showFinalResult();
        return;
    }

    const question = quizData[index];
    
    // initialize attempts counter for this question
    attempts[index] = attempts[index] || 0;

    let optionsHTML = '';
    for (const [key, value] of Object.entries(question.options)) {
        optionsHTML += `<button class="option-btn" onclick="checkAnswer(this, '${key}')">${value}</button>`;
    }

    quizContentEl.innerHTML = `
        <div class="question-box active">
            <div class="question-text">Câu ${index + 1}: ${question.question}</div>
            <div class="options-grid">
                ${optionsHTML}
            </div>
            <div class="explanation-box" id="explanation-${index}">
                <div class="explanation-title">Giải thích:</div>
                <span id="explanation-text-${index}"></span>
            </div>
            <button class="next-btn" id="nextBtn-${index}" onclick="nextQuestion()">
                <span class="btn-icon">→</span> Câu tiếp theo
            </button>
        </div>
    `;
}

function checkAnswer(selectedBtn, selectedOption) {
    const currentQuizData = quizData[currentQuestionIndex];
    const correctOption = currentQuizData.correct;
    const allOptions = document.querySelectorAll('.option-btn');

    // increment attempt count for this question
    attempts[currentQuestionIndex] = (attempts[currentQuestionIndex] || 0) + 1;

    if (selectedOption === correctOption) {
        selectedBtn.classList.add('correct');
        score += 10;
        // award XP only if this is the first attempt for the question
        if (attempts[currentQuestionIndex] === 1) {
            totalXp += 4; // 4 XP for first-try correct (5 questions total -> 20 XP)
        }
        showFeedback('correct', 'Chính xác!');
    } else {
        selectedBtn.classList.add('wrong');
        allOptions.forEach(btn => {
            const btnText = btn.textContent.trim();
            const optionKey = currentQuizData.options ? 
                Object.keys(currentQuizData.options).find(key => 
                    btnText === currentQuizData.options[key]
                ) : '';
            if (optionKey === correctOption) {
                btn.classList.add('correct');
            }
        });
        showFeedback('wrong', 'Sai rồi!');
    }

    const explanationBox = document.getElementById(`explanation-${currentQuestionIndex}`);
    const explanationText = document.getElementById(`explanation-text-${currentQuestionIndex}`);
    explanationText.textContent = currentQuizData.explanation;
    explanationBox.style.display = 'block';
    document.getElementById(`nextBtn-${currentQuestionIndex}`).style.display = 'flex';
}

function showFeedback(type, message) {
    const feedbackEl = document.createElement('div');
    feedbackEl.className = `feedback-message ${type}`;
    feedbackEl.innerHTML = `
        <span class="feedback-icon">${type === 'correct' ? '✓' : '✗'}</span>
        <span class="feedback-text">${message}</span>
    `;
    
    const questionBox = document.querySelector('.question-box');
    questionBox.appendChild(feedbackEl);
    
    setTimeout(() => {
        feedbackEl.style.opacity = '0';
        feedbackEl.style.transform = 'translateY(-10px)';
        setTimeout(() => feedbackEl.remove(), 300);
    }, 1500);
}

function nextQuestion() {
    currentQuestionIndex++;
    loadQuestion(currentQuestionIndex);
}

function showFinalResult() {
    const totalQuestions = quizData.length;
    quizContentEl.style.display = 'none';
    finalResultEl.style.display = 'block';

    const scoreText = document.getElementById('finalScoreText');
    const messageText = document.getElementById('finalMessage');
    
    const percentage = Math.round((score / (totalQuestions * 10)) * 100);
    
    scoreText.textContent = `${score}`;
    
    if (score === totalQuestions * 10) {
        messageText.textContent = "Xuất sắc! Bạn đã hoàn thành bài học một cách hoàn hảo!";
    } else if (percentage >= 80) {
        messageText.textContent = "Rất tốt! Bạn đã nắm vững kiến thức về Ngày và Đêm.";
    } else if (percentage >= 60) {
        messageText.textContent = "Tốt! Bạn hiểu phần lớn nội dung bài học.";
    } else {
        messageText.textContent = "Hãy xem lại video và làm bài tập thêm nhé!";
    }

    try {
        fetch('/SPNC_HocLieu_STEM_TieuHoc/science/commit-quiz', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                lesson: 'Ngày và Đêm', 
                score: percentage,
                rawScore: score,
                totalScore: totalQuestions * 10
                ,
                xp: totalXp
            })
        }).then(r => r.json()).then(data => {
            if (data && data.success) {
                console.log('Điểm đã được lưu thành công!');
            } else {
                const msg = document.createElement('p');
                msg.textContent = 'Có lỗi khi lưu điểm: ' + (data && data.message ? data.message : '');
                msg.style.color = '#e74c3c';
                msg.style.marginTop = '10px';
                finalResultEl.appendChild(msg);
            }
        }).catch(err => {
            console.error('Commit error', err);
            const errorMsg = document.createElement('p');
            errorMsg.textContent = 'Không thể kết nối đến máy chủ.';
            errorMsg.style.color = '#e74c3c';
            errorMsg.style.marginTop = '10px';
            finalResultEl.appendChild(errorMsg);
        });
    } catch (e) { 
        console.error(e); 
    }
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof quizData !== 'undefined' && quizData.length > 0) {
        loadQuestion(0);
        updateProgressCounter();
    } else {
        console.error("Không tìm thấy dữ liệu câu hỏi (quizData)!");
        quizContentEl.innerHTML = `
            <div class="error-message">
                <p>Không thể tải câu hỏi. Vui lòng tải lại trang hoặc liên hệ quản trị viên.</p>
            </div>
        `;
    }
    
    const restartBtn = document.querySelector('.restart-btn');
    if (restartBtn) {
        restartBtn.addEventListener('click', () => {
            document.querySelectorAll('.feedback-message').forEach(el => el.remove());
        });
    }
});