document.addEventListener("DOMContentLoaded", () => {

    const TOTAL_PARTS = (typeof totalParts !== 'undefined') ? totalParts : 7;

    const draggables = document.querySelectorAll(".draggable-part");
    const dropzones = document.querySelectorAll(".dropzone");
    const feedback = document.getElementById("game-feedback");
    const winModal = document.getElementById("win-modal");
    const restartBtn = document.getElementById("restart-game-btn");
    const introModal = document.getElementById("intro-modal");
    const startBtn = document.getElementById("start-game-btn");

    let correctDrops = 0;
    let startTime = null;
    let timerInterval = null;
    
    // Xử lý nút bắt đầu game
    if (startBtn) {
        startBtn.addEventListener('click', () => {
            if (introModal) {
                introModal.classList.remove('active');
                setTimeout(() => {
                    introModal.style.display = 'none';
                }, 300);
            }
            startTimer();
        });
    }
    
    // Hàm đếm thời gian
    function startTimer() {
        startTime = Date.now();
        const timerDisplay = document.getElementById('timer-display');
        
        if (timerDisplay) {
            timerInterval = setInterval(() => {
                const elapsed = Math.floor((Date.now() - startTime) / 1000);
                const minutes = Math.floor(elapsed / 60);
                const seconds = elapsed % 60;
                timerDisplay.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }, 1000);
        }
    }
    
    // Cập nhật số lượng
    function updateStats() {
        const placedCount = document.getElementById('placed-count');
        const remainingCount = document.getElementById('remaining-count');
        
        if (placedCount) placedCount.textContent = correctDrops;
        if (remainingCount) remainingCount.textContent = TOTAL_PARTS - correctDrops;
    }
    
    // --- Xử lý KÉO ---
    draggables.forEach(part => {
        part.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', part.dataset.partId);
            part.classList.add('dragging');
        });
        
        part.addEventListener('dragend', () => {
            part.classList.remove('dragging');
        });
    });

    // --- Xử lý THẢ ---
    dropzones.forEach(zone => {
        zone.addEventListener('dragover', (e) => {
            e.preventDefault(); // Cho phép thả
            if (!zone.classList.contains('filled')) {
                 zone.classList.add('hovered');
            }
        });
        
        zone.addEventListener('dragleave', () => {
            zone.classList.remove('hovered');
        });
        
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('hovered');
            
            if (zone.classList.contains('filled')) return;
            
            const partId = e.dataTransfer.getData('text/plain');
            const targetId = zone.dataset.target;
            
            const draggedElement = document.querySelector(`.draggable-part[data-part-id='${partId}']`);

            if (partId === targetId) {
                // --- ĐÚNG ---
                showFeedback("Đúng rồi! Rất chính xác!", "success");
                
                zone.classList.add('filled');
                zone.draggable = false;
                
                const img = draggedElement.querySelector('img').cloneNode(true);
                zone.innerHTML = ''; 
                zone.appendChild(img);

                draggedElement.style.display = 'none';

                correctDrops++;
                updateStats(); // Cập nhật số liệu

                if (correctDrops === TOTAL_PARTS) {
                    if (timerInterval) clearInterval(timerInterval); // Dừng đồng hồ
                    setTimeout(() => {
                        showModal(true);
                        // send commit to server to save completion (100%)
                        try {
                            fetch(`${baseUrl}/views/lessons/update-computer-parts-score`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ action: 'commit' })
                            }).then(r => r.json()).then(j => {
                                if (j && j.success) {
                                    console.log('Computer parts: saved', j);
                                } else {
                                    console.warn('Computer parts save response', j);
                                }
                            }).catch(err => console.error('Save error', err));
                        } catch (err) { console.error(err); }
                    }, 500);
                }
                
            } else {
                // --- SAI ---
                showFeedback("Ôi, sai vị trí rồi! Thử lại nhé.", "error");
                zone.classList.add('shake');
                setTimeout(() => zone.classList.remove('shake'), 500);
            }
        });
    });
    
    // Nút chơi lại
    restartBtn.addEventListener('click', () => {
        window.location.reload();
    });
    
    // Nút chơi lại ở main controls
    const restartBtnMain = document.getElementById('restart-game-btn-main');
    if (restartBtnMain) {
        restartBtnMain.addEventListener('click', () => {
            window.location.reload();
        });
    }
    
    // Nút gợi ý
    const hintBtn = document.getElementById('hint-btn');
    if (hintBtn) {
        hintBtn.addEventListener('click', () => {
            showFeedback("Gợi ý: Màn hình ở giữa, chuột ở phải bàn phím, loa ở góc phải!", "success");
        });
    }

    // Back to technology page button
    const backBtn = document.getElementById('back-to-tech-btn');
    if (backBtn) {
        backBtn.addEventListener('click', () => {
            window.location.href = baseUrl + '/views/lessons/technology.php';
        });
    }

    // Hiển thị thông báo
    function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = type;
        setTimeout(() => {
            feedback.textContent = '';
            feedback.className = '';
        }, 3000); 
    }
    
    function showModal(isWin) {
        if (isWin) {
            winModal.style.display = 'flex';
        }
    }

});