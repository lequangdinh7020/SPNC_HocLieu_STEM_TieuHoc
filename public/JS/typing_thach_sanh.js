const storyModal = document.getElementById('story-modal');
const gameOverModal = document.getElementById('game-over-modal');
const enemiesContainer = document.getElementById('enemies-container');
const arrowsContainer = document.getElementById('arrows-container');
const gameWrapper = document.getElementById('game-wrapper');
const gameStage = document.getElementById('game-stage');

const scoreEl = document.getElementById('score');
const livesEl = document.getElementById('lives');
const timeDisplayEl = document.getElementById('time-display'); // Mới
const thachSanhEl = document.getElementById('thach-sanh');
const visualFeedback = document.getElementById('visual-feedback');
const finalScoreEl = document.getElementById('final-score');
const endTitleEl = document.getElementById('end-title');
const endMessageEl = document.getElementById('end-message');

// Quick-complete elements
const quickCompleteBtn = document.getElementById('quick-complete-btn');
const quickCompleteModal = document.getElementById('quick-complete-modal');
const qcTotalEl = document.getElementById('qc-total');
const qcMaxEl = document.getElementById('qc-max');
const qcPctEl = document.getElementById('qc-pct');
const qcReplayBtn = document.getElementById('qc-replay');
const qcBackBtn = document.getElementById('qc-back');

let currentLevelWords = [];
let activeEnemies = [];
let score = 0;
const maxLives = 5;
let lives = maxLives;

let spawnedCount = 0;

const GAME_DURATION = 180; // 3 phút
let timeLeft = GAME_DURATION;
let gameTimerInterval;
// ---------------------

let gameInterval;
let spawnRate = 2500;
let isPlaying = false;

function startGame(level) {
    storyModal.style.display = 'none';
    if (!wordData[level]) return;

    currentLevelWords = wordData[level];
    isPlaying = true;
    
    // Reset chỉ số
    score = 0;
    lives = maxLives;
    timeLeft = GAME_DURATION; // Reset thời gian
    spawnedCount = 0;
    
    scoreEl.innerText = score;
    livesEl.innerText = lives;
    updateTimeDisplay(); // Hiện 03:00

    // Clear previous modal/percent displays so each run is fresh
    try {
        if (qcTotalEl) qcTotalEl.innerText = 0;
        if (qcPctEl) qcPctEl.innerText = '0%';
        const finalPctEl = document.getElementById('final-pct');
        if (finalPctEl) finalPctEl.innerText = '0%';
        if (finalScoreEl) finalScoreEl.innerText = 0;
    } catch (e) { /* ignore */ }

    // Reset server-side commit flag so user can submit again after replay
    try {
        fetch(`${baseUrl}/views/lessons/reset-thach-sanh-commit`, { method: 'POST' }).catch(() => {});
    } catch (e) { /* ignore */ }
    
    activeEnemies = [];
    enemiesContainer.innerHTML = '';
    arrowsContainer.innerHTML = '';

    // Loop sinh quái
    gameInterval = setInterval(() => {
        if (!isPlaying) return;
        spawnEnemy();
    }, spawnRate);

    // Loop đếm ngược thời gian
    gameTimerInterval = setInterval(() => {
        if (!isPlaying) return;
        timeLeft--;
        updateTimeDisplay();

        if (timeLeft <= 0) {
            victory(); // Hết giờ -> Chiến thắng and auto-save
        }
    }, 1000);

    requestAnimationFrame(gameLoop);
    document.addEventListener('keydown', handleTyping);
}

// Hàm cập nhật hiển thị đồng hồ
function updateTimeDisplay() {
    const minutes = Math.floor(timeLeft / 60);
    const seconds = timeLeft % 60;
    // Thêm số 0 đằng trước nếu < 10
    timeDisplayEl.innerText = `${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
}

// Hàm Chiến Thắng (Hết giờ)
function victory() {
    isPlaying = false;
    clearInterval(gameInterval);
    clearInterval(gameTimerInterval);
    document.removeEventListener('keydown', handleTyping);

    // Tính điểm thưởng
    const bonusPoints = lives * 50; // 50 điểm cho mỗi mạng còn lại
    const totalScore = score + bonusPoints;

    endTitleEl.innerText = "CHIẾN THẮNG!";
    endTitleEl.style.color = "#2ecc71";
    endMessageEl.innerHTML = `
        Bạn đã bảo vệ buôn làng thành công trong 3 phút!<br>
        Điểm gốc: ${score}<br>
        Thưởng mạng (${lives}): +${bonusPoints}
    `;
    if (finalScoreEl) finalScoreEl.innerText = totalScore;
    
    gameOverModal.style.display = 'flex';

    // Compute percent and save score to server (only on time-up victory)
    try {
        // New rule: percent must be computed from the player's base score only (no lives bonus)
        const pct = Math.min(100, Math.max(0, Math.round((score / 720) * 100)));
        // display percent on result screen (final-pct element)
        const finalPctEl = document.getElementById('final-pct');
        if (finalPctEl) finalPctEl.innerText = pct + '%';

        // send to server
        fetch(`${baseUrl}/views/lessons/update-thach-sanh-score`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'commit', score_pct: pct, game_name: 'Em là người đánh máy' })
        }).then(r => r.json()).then(j => console.log('saved', j)).catch(e => console.error(e));
    } catch (e) { console.error(e); }
}

function gameLoop() {
    if (!isPlaying) return;

    const gameHeight = (gameStage || gameWrapper).offsetHeight;

    activeEnemies.forEach((enemy, index) => {
        // Nếu quái đã chết không cho di chuyển nữa
        if (enemy.isDead) return;

        enemy.top += enemy.speed;
        enemy.el.style.top = enemy.top + 'px';

        if (enemy.top > (gameHeight - 100)) { 
            handleEnemyMissed(index);
        }
    });

    requestAnimationFrame(gameLoop);
}

function handleEnemyMissed(index) {
    // Xóa ngay lập tức khỏi mảng logic
    const enemyEl = activeEnemies[index].el;
    activeEnemies.splice(index, 1);
    enemyEl.remove(); // Xóa khỏi màn hình
    
    lives--;
    livesEl.innerText = lives;

    gameWrapper.classList.add('village-damage');
    setTimeout(() => gameWrapper.classList.remove('village-damage'), 500);
    triggerWrong("Ôi hỏng rồi!"); 

    if (lives <= 0) {
        endGame();
    }
}

function endGame() {
    isPlaying = false;
    clearInterval(gameInterval);
    clearInterval(gameTimerInterval); // Dừng đồng hồ
    document.removeEventListener('keydown', handleTyping);
    
    endTitleEl.innerText = "KẾT THÚC!";
    endTitleEl.style.color = "#e74c3c";
    endMessageEl.innerText = "Bạn đã hết mạng. Buôn làng bị tàn phá!";
    if (finalScoreEl) finalScoreEl.innerText = score;

    // Show a small failure modal (reuse quick-complete modal) that mirrors quick-complete layout
    try {
        if (quickCompleteModal) {
            // set header and message for failure
            const hdr = quickCompleteModal.querySelector('h3');
            if (hdr) hdr.innerText = 'Thất bại';
            const msg = document.getElementById('qc-msg');
            if (msg) msg.innerText = 'Hãy thử lại lần nữa xem!';

            // compute percent from base score only
            const pct = Math.min(100, Math.max(0, Math.round((score / 720) * 100)));
            if (qcPctEl) qcPctEl.innerText = pct + '%';

            // hide big game over modal if visible
            if (gameOverModal) gameOverModal.style.display = 'none';

            quickCompleteModal.style.display = 'flex';
            return;
        }
    } catch (e) { /* ignore and fallback to full modal */ }

    // fallback to original full modal
    gameOverModal.style.display = 'flex';
}

// Quick-complete logic: calculate percentage and show quick modal, then save
function quickComplete() {
    if (!isPlaying) return;
    isPlaying = false;
    clearInterval(gameInterval);
    clearInterval(gameTimerInterval);
    document.removeEventListener('keydown', handleTyping);

    // Don't include lives bonus when computing percent — use current base score only
    const bonusPoints = lives * 50;
    let totalScore = (typeof score === 'number') ? score + bonusPoints : 0;


    // Use requested formula: percent = (current totalScore) / 720 * 100
    const pct = Math.min(100, Math.max(0, Math.round((score / 720) * 100)));

    // Update quick modal
    // set header/message for quick-complete
    try {
        if (quickCompleteModal) {
            const hdr = quickCompleteModal.querySelector('h3');
            if (hdr) hdr.innerText = 'Hoàn thành nhanh';
            const msg = document.getElementById('qc-msg');
            if (msg) msg.innerText = '';
        }
    } catch (e) {}

    if (qcTotalEl) qcTotalEl.innerText = totalScore;
    if (qcMaxEl) qcMaxEl.innerText = 720;
    if (qcPctEl) qcPctEl.innerText = pct + '%';

    if (quickCompleteModal) quickCompleteModal.style.display = 'flex';

    // send to server to save
    try {
        fetch(`${baseUrl}/views/lessons/update-thach-sanh-score`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'commit', score_pct: pct, game_name: 'Em là người đánh máy' })
        }).then(r => r.json()).then(j => console.log('Quick complete saved', j)).catch(e => console.error(e));
    } catch (e) { console.error(e); }
}

// Hook quick-complete button
if (quickCompleteBtn) {
    quickCompleteBtn.addEventListener('click', (e) => {
        quickComplete();
    });
}

// Quick modal buttons
if (qcReplayBtn) qcReplayBtn.addEventListener('click', () => location.reload());
if (qcBackBtn) qcBackBtn.addEventListener('click', () => window.location.href = baseUrl + '/views/lessons/technology.php');

// End modal buttons
const replayBtn = document.getElementById('replay-btn');
const backBtn = document.getElementById('back-btn');
if (replayBtn) replayBtn.addEventListener('click', () => location.reload());
if (backBtn) backBtn.addEventListener('click', () => window.location.href = baseUrl + '/views/lessons/technology.php');

function spawnEnemy() {
    const word = currentLevelWords[Math.floor(Math.random() * currentLevelWords.length)];
    const isBoss = Math.random() > 0.7;
    const enemyDiv = document.createElement('div');
    enemyDiv.className = 'enemy';
    
    const randomLeft = Math.floor(Math.random() * 80) + 10;
    enemyDiv.style.left = randomLeft + '%';
    enemyDiv.style.top = '-120px'; 

    let imgName = isBoss ? 'chan_tinh.png' : 'dai_bang.png';
    if (word.length === 1 && !isBoss) imgName = 'stone.png';
    const imgSrc = `${baseUrl}/public/images/thachsanh/${imgName}`;

    enemyDiv.innerHTML = `<div class="enemy-word">${word}</div><img src="${imgSrc}">`;
    enemiesContainer.appendChild(enemyDiv);

    // increment spawned count for session
    spawnedCount++;

    activeEnemies.push({
        el: enemyDiv,
        word: word,
        remaining: word,
        top: -120,
        speed: isBoss ? 1.2 : 1.4,
        isDead: false 
    });
}

function handleTyping(e) {
    if (!isPlaying) return;
    const key = e.key.toUpperCase();
    let targetIndex = -1; let maxTop = -1000;

    for (let i = 0; i < activeEnemies.length; i++) {
        // Bỏ qua quái đã chết
        if (activeEnemies[i].isDead) continue;

        if (activeEnemies[i].word !== activeEnemies[i].remaining) {
            if (activeEnemies[i].remaining.startsWith(key)) { targetIndex = i; break; } 
            else { triggerWrong(); return; }
        }
    }
    if (targetIndex === -1) {
        for (let i = 0; i < activeEnemies.length; i++) {
            if (activeEnemies[i].isDead) continue; // Bỏ qua quái chết

            if (activeEnemies[i].remaining.startsWith(key)) {
                if (activeEnemies[i].top > maxTop) { maxTop = activeEnemies[i].top; targetIndex = i; }
            }
        }
    }

    if (targetIndex !== -1) {
        const enemy = activeEnemies[targetIndex];
        enemy.remaining = enemy.remaining.substring(1);
        
        const typed = enemy.word.substring(0, enemy.word.length - enemy.remaining.length);
        enemy.el.querySelector('.enemy-word').innerHTML = `<span class="typed">${typed}</span>${enemy.remaining}`;

        // Nếu hoàn thành từ
        if (enemy.remaining === '') {
            score += 10;
            scoreEl.innerText = score;
            
            // Đánh dấu đã chết để không gõ được nữa và không di chuyển
            enemy.isDead = true;

            // *** Bắn tên -> Chờ tên bay tới -> Mới xóa quái ***
            shootArrow(enemy.el, () => {
                // Callback này chạy khi tên đã trúng đích
                removeEnemy(targetIndex);
            });
        }
    } else {
        triggerWrong();
    }
}

// Hàm bắn tên có Callback (hành động sau khi trúng)
function shootArrow(targetEl, onHitCallback) {
    const thachSanhRect = thachSanhEl.getBoundingClientRect();
    const targetRect = targetEl.getBoundingClientRect();
    const gameRect = (gameStage || gameWrapper).getBoundingClientRect();

    const startX = thachSanhRect.left + thachSanhRect.width / 2 - gameRect.left;
    const startY = thachSanhRect.top - gameRect.top;
    const endX = targetRect.left + targetRect.width / 2 - gameRect.left;
    const endY = targetRect.top + targetRect.height / 2 - gameRect.top;

    const deltaX = endX - startX;
    const deltaY = endY - startY;
    const angle = Math.atan2(deltaY, deltaX) * (180 / Math.PI);

    // Xoay nhân vật theo hướng quái vật
    if (deltaX < 0) {
        // Quái vật ở bên trái
        thachSanhEl.classList.remove('face-right');
        thachSanhEl.classList.add('face-left');
    } else {
        // Quái vật ở bên phải
        thachSanhEl.classList.remove('face-left');
        thachSanhEl.classList.add('face-right');
    }

    const arrow = document.createElement('div');
    arrow.className = 'arrow';
    arrow.style.left = startX + 'px';
    arrow.style.top = startY + 'px';
    arrow.style.transform = `rotate(${angle}deg)`;
    arrowsContainer.appendChild(arrow);

    requestAnimationFrame(() => {
        arrow.style.left = endX + 'px';
        arrow.style.top = endY + 'px';
    });

    // Sau 300ms (thời gian tên bay), thực hiện xóa quái
    setTimeout(() => {
        arrow.remove();
        if (onHitCallback) onHitCallback(); // Xóa quái vật
    }, 300);
}

function removeEnemy(index) {
    const enemyObj = activeEnemies[index];
    if (enemyObj && enemyObj.el && enemyObj.el.parentNode) {
         enemyObj.el.remove();
         activeEnemies.splice(index, 1);
    } else {
        // Nếu index bị lệch, tìm và xóa object có isDead = true
        const deadIndex = activeEnemies.findIndex(e => e.isDead);
        if (deadIndex !== -1) {
            activeEnemies[deadIndex].el.remove();
            activeEnemies.splice(deadIndex, 1);
        }
    }
}

function triggerWrong(msg = "Thử lại!") {
    thachSanhEl.classList.add('shake');
    setTimeout(() => thachSanhEl.classList.remove('shake'), 500);
    
    visualFeedback.innerText = msg;
    visualFeedback.className = "show-wrong";
    setTimeout(() => visualFeedback.className = "", 500);
}