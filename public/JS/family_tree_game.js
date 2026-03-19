document.addEventListener("DOMContentLoaded", () => {
    // Handle intro modal
    const introModal = document.getElementById('intro-modal');
    const nextStoryButton = document.getElementById('nextStoryButton');
    const storyText = document.getElementById('storyText');
    const storyDialogues = [
        "Cô giáo linh dương đã giao cho Peppa một bài tập về cây gia đình nhưng vì mải lo chơi nên Peppa không nhớ những kiến thức cô đã dạy trên lớp. Hãy giúp Peppa hoàn thành bài tập nhé!",
        "Nhiệm vụ của chúng mình: Trên màn hình là một cái cây. Các bạn hãy tinh mắt và kéo thả ảnh của từng người vào đúng thế hệ theo hướng dẫn!",
        "Chúng mình cùng giúp Peppa hoàn thành bài tập về nhà nào! 3... 2... 1... Bắt đầu dán ảnh thôi!"
    ];
    let currentStoryIndex = 0;
    
    if (nextStoryButton && introModal && storyText) {
        nextStoryButton.addEventListener('click', () => {
            currentStoryIndex++;

            if (currentStoryIndex < storyDialogues.length) {
                storyText.textContent = storyDialogues[currentStoryIndex];

                if (currentStoryIndex === storyDialogues.length - 1) {
                    nextStoryButton.innerHTML = '<i class="fas fa-play"></i> Bắt đầu dán ảnh thôi!';
                }

                return;
            }

            introModal.classList.remove('active');
        });
    }

    const BASE_URL = baseUrl; 
    const CURRENT_LEVEL_DATA = currentLevelData; 
    const TOTAL_GAME_LEVELS = totalGameLevels; 

    // DOM Elements
    const characterBank = document.getElementById('character-bank');
    const feedbackMessage = document.getElementById('game-feedback');
    const userFeedback = document.getElementById('userFeedback');
    const livesContainer = document.getElementById('lives-container');
    const currentLevelDisplay = document.getElementById('current-level-display');
    const gameOverModal = document.getElementById('game-over-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const nextLevelBtn = document.getElementById('next-level-btn');
    const restartGameBtn = document.getElementById('restart-game-btn');
    const backToTechnologyBtn = document.getElementById('back-to-technology-btn');
    const backToMenuBtn = document.getElementById('back-to-menu-btn');
    const treeCanvas = document.getElementById('tree-canvas');
    const resetButton = document.getElementById('resetButton');

    // Game Variables
    let draggedCharId = null; 
    let lives = 3; 
    let totalCorrectSlots = 0;
    const totalSlotsToWin = Object.keys(CURRENT_LEVEL_DATA.solution).length;

    // --- KHỞI TẠO ---
    initGame();

    function initGame() {
        lives = 3; 
        totalCorrectSlots = 0;
        updateLivesDisplay();
        if(feedbackMessage) feedbackMessage.style.display = 'none'; 
        if(userFeedback) userFeedback.textContent = '';

        if(currentLevelDisplay) currentLevelDisplay.textContent = CURRENT_LEVEL_DATA.id;

        resetSlotsAndBank();
        
        // Add reset button handler
        if(resetButton) {
            resetButton.onclick = () => {
                window.location.reload();
            };
        }
        
        // Gán sự kiện cho modal
        if(nextLevelBtn) {
            nextLevelBtn.onclick = () => {
                const nextLevel = CURRENT_LEVEL_DATA.id + 1;
                window.location.href = `${BASE_URL}/views/lessons/tech-family-tree?level=${nextLevel}`;
            };
        }
        if(restartGameBtn) {
            restartGameBtn.onclick = () => {
                window.location.reload(); 
            };
        }
        if(backToMenuBtn) {
            backToMenuBtn.onclick = () => {
                window.location.href = `${BASE_URL}/views/lessons/technology.php`;
            };
        }
        if(gameOverModal) gameOverModal.style.display = 'none';
    }

    function resetSlotsAndBank() {
        // 1. Tạo lại Ngân hàng nhân vật (Giữ nguyên)
        characterBank.innerHTML = '';
        CURRENT_LEVEL_DATA.available_characters.forEach(charId => {
            const charDiv = createDraggableCharElement(charId);
            characterBank.appendChild(charDiv);
            addDragEvents(charDiv);
        });

        // 2. Reset các ô trên cây
        const dropSlots = document.querySelectorAll('.drop-slot');
        dropSlots.forEach(slot => {
            emptySlot(slot);
            
            // CHỈ GÁN SỰ KIỆN NẾU CHƯA GÁN (Kiểm tra thuộc tính dataset)
            if (!slot.dataset.eventsAttached) {
                addDropEvents(slot);
                addDragEvents(slot); // Ô trên cây cũng có thể là nguồn kéo
                slot.dataset.eventsAttached = "true"; // Đánh dấu đã gán
            }
        });
    }

    function createDraggableCharElement(charId) {
        const charDiv = document.createElement('div');
        charDiv.className = 'draggable-char';
        charDiv.dataset.charId = charId;
        charDiv.draggable = true; // Bắt buộc
        
        const charName = charId.charAt(0).toUpperCase() + charId.slice(1);
        
        charDiv.innerHTML = `
            <img src="${BASE_URL}/public/images/family_tree/${charId}.png" alt="${charId}">
            <span class="char-name">${charName}</span>
        `;
        return charDiv;
    }

    // --- HÀM GÁN SỰ KIỆN KÉO (DRAG START) ---
    function addDragEvents(element) {
        element.addEventListener('dragstart', (e) => {
            // Nếu là ô trống hoặc bị khóa thì không cho kéo
            if (element.classList.contains('empty') || element.draggable === false) {
                e.preventDefault();
                return;
            }

            draggedCharId = element.dataset.charId;
            e.dataTransfer.setData('text/plain', draggedCharId);
            e.dataTransfer.effectAllowed = "move";
            
            // Làm mờ để biết đang kéo
            setTimeout(() => {
                element.style.opacity = '0.5';
            }, 0);
        });

        element.addEventListener('dragend', (e) => {
            element.style.opacity = '1';
            // Không reset draggedCharId ở đây
        });
    }

    // --- HÀM GÁN SỰ KIỆN THẢ (DROP) ---
    function addDropEvents(slot) {
        // 1. Drag Over (Bắt buộc để cho phép Drop)
        slot.addEventListener('dragover', (e) => {
            e.preventDefault(); // Quan trọng nhất!
            if (!slot.classList.contains('correct')) { 
                e.dataTransfer.dropEffect = "move";
                slot.classList.add('hovered');
            }
        });

        // 2. Drag Leave
        slot.addEventListener('dragleave', (e) => {
            slot.classList.remove('hovered');
        });

        // 3. Drop
        slot.addEventListener('drop', (e) => {
            e.preventDefault();
            slot.classList.remove('hovered');


            // SỬA ĐOẠN NÀY: Lấy ID từ dataTransfer thay vì biến toàn cục
            const transferData = e.dataTransfer.getData('text/plain');
            // Ưu tiên lấy từ dataTransfer, nếu không có mới dùng biến toàn cục (dự phòng)
            const charIdToDrop = transferData ? transferData : draggedCharId;
            // const charIdToDrop = draggedCharId;

            // Nếu ô này đã đúng hoặc không có dữ liệu kéo -> Dừng
            if (slot.classList.contains('correct') || !draggedCharId) return;
            
            const currentSlotCharId = slot.dataset.currentCharId;

            // --- XỬ LÝ LOGIC DI CHUYỂN ---

            // A. Tìm xem nhân vật này đang ở đâu (Ngân hàng hay Ô khác)?
            let sourceElement = null;
            
            // Tìm trong ngân hàng
            const bankChar = document.querySelector(`#character-bank .draggable-char[data-char-id="${charIdToDrop}"]`);
            
            // Nếu tìm thấy trong bank và nó chưa bị ẩn -> Nó đến từ bank
            if (bankChar && bankChar.style.display !== 'none') {
                sourceElement = bankChar;
                sourceElement.style.display = 'none'; // Ẩn khỏi ngân hàng
            } else {
                // Nếu không, tìm trên cây (ô khác đang chứa nó)
                const allSlots = document.querySelectorAll('.drop-slot');
                for (let s of allSlots) {
                    // Tìm ô có chứa ID này, và không phải là ô hiện tại
                    if (s.dataset.currentCharId === charIdToDrop && s !== slot) {
                        sourceElement = s;
                        emptySlot(sourceElement); // Làm rỗng ô cũ
                        break;
                    }
                }
            }

            // B. Nếu ô đích đang có người -> Trả người đó về ngân hàng
            if (currentSlotCharId) {
                const charInBank = document.querySelector(`#character-bank .draggable-char[data-char-id="${currentSlotCharId}"]`);
                if (charInBank) charInBank.style.display = 'flex';
            }

            // C. Điền nhân vật mới vào ô đích
            fillSlot(slot, charIdToDrop);
            
            // --- KIỂM TRA KẾT QUẢ NGAY LẬP TỨC ---
            validateMove(slot, charIdToDrop);
            
            draggedCharId = null;
        });
    }

    function validateMove(slot, charId) {
        const slotKey = slot.id.replace('slot-', ''); // vd: parent1
        const correctCharId = CURRENT_LEVEL_DATA.solution[slotKey];

        // Xóa class sai cũ
        slot.classList.remove('wrong-immediate');

        if (charId === correctCharId) {
            // *** ĐÚNG ***
            slot.classList.add('correct');
            slot.draggable = false; // Khóa ô này lại
            
            // Tăng điểm/số ô đúng
            totalCorrectSlots++;
            showFeedback("Đúng rồi!", 'success');
            
            // Kiểm tra xem đã thắng chưa
            if (totalCorrectSlots === totalSlotsToWin) {
                 setTimeout(() => { showModal('win'); }, 500);
            }
        } else {
            // *** SAI ***
            lives--;
            updateLivesDisplay();
            showFeedback(`Sai vị trí! Bạn mất 1 trái tim.`, 'error');
            
            // Hiệu ứng rung lắc đỏ
            slot.classList.add('wrong-immediate');
            
            // Sau 0.6s thì trả nhân vật về ngân hàng
            setTimeout(() => {
                slot.classList.remove('wrong-immediate');
                // Nếu vẫn chưa đúng (người dùng chưa kéo cái khác vào thay thế nhanh quá)
                if (!slot.classList.contains('correct') && slot.dataset.currentCharId === charId) { 
                    emptySlot(slot); // Xóa khỏi ô sai
                    
                    // Hiện lại trong ngân hàng
                    const charInBank = document.querySelector(`#character-bank .draggable-char[data-char-id="${charId}"]`);
                    if (charInBank) charInBank.style.display = 'flex';
                }
            }, 600);

            if (lives <= 0) {
                showModal('lose');
            }
        }
    }

    function fillSlot(slot, charId) {
        const charName = charId.charAt(0).toUpperCase() + charId.slice(1);
        slot.innerHTML = `
            <img src="${BASE_URL}/public/images/family_tree/${charId}.png" alt="${charId}">
            <span class="char-name">${charName}</span>
        `;
        // CSS Styles cho ô đã điền
        slot.style.backgroundImage = 'none';
        slot.style.backgroundColor = '#f7dc6f'; 
        slot.style.borderColor = '#f39c12';       
        slot.style.borderStyle = 'solid';     
        slot.style.filter = 'none'; 

        slot.classList.add('filled');
        slot.classList.remove('empty');
        
        slot.dataset.currentCharId = charId;
        slot.dataset.charId = charId; 
        slot.draggable = true; 
    }

    function emptySlot(slot) {
        slot.innerHTML = '';
        // CSS Styles cho ô trống (Xám)
        slot.style.backgroundImage = `url('${BASE_URL}/public/images/family_tree/empty_slot.png')`;
        slot.style.backgroundColor = '#e0e0e0'; 
        slot.style.borderColor = '#999';       
        slot.style.borderStyle = 'dashed';     
        slot.style.filter = 'grayscale(100%)'; 

        slot.classList.remove('filled', 'correct', 'wrong', 'wrong-immediate');
        slot.classList.add('empty');
        
        slot.dataset.currentCharId = '';
        slot.dataset.charId = ''; 
        slot.draggable = false;
    }

    function updateLivesDisplay() {
        if (!livesContainer) return;
        const hearts = livesContainer.querySelectorAll('.fa-heart');
        hearts.forEach((heart, index) => {
            if (index < lives) {
                heart.classList.add('live');
                heart.classList.remove('lost');
            } else {
                heart.classList.remove('live');
                heart.classList.add('lost');
            }
        });
    }

    function showFeedback(message, type) {
        // Show in userFeedback div
        if (userFeedback) {
            userFeedback.textContent = message;
            if (type === 'correct') {
                userFeedback.className = 'correct';
            } else if (type === 'wrong') {
                userFeedback.className = 'wrong';
            } else {
                userFeedback.className = '';
            }
            
            setTimeout(() => {
                userFeedback.textContent = '';
                userFeedback.className = '';
            }, 2000);
        }
        
        // Also show in feedbackMessage if exists (for compatibility)
        if (feedbackMessage) {
            feedbackMessage.textContent = message;
            feedbackMessage.className = `feedback-message ${type}`;
            feedbackMessage.style.display = 'block';
            setTimeout(() => {
                feedbackMessage.style.display = 'none';
            }, 2000);
        }
    }

    function showModal(status) {
        if (!gameOverModal) return;
        
        // Khóa tất cả các vật thể khi hiện bảng thông báo
        document.querySelectorAll('.draggable-char, .person-node').forEach(el => el.draggable = false);

        if (status === 'win') {
            modalTitle.textContent = "Chúc Mừng!";
            modalTitle.style.color = "#2ecc71"; 
            modalMessage.textContent = "Bạn đã sắp xếp chính xác gia đình này!";
            
          
            const currentId = parseInt(CURRENT_LEVEL_DATA.id);
            const totalLevels = parseInt(TOTAL_GAME_LEVELS);

            if (currentId < totalLevels) {
                nextLevelBtn.style.display = 'inline-block';
                nextLevelBtn.textContent = "Cấp độ tiếp theo";
                
                nextLevelBtn.onclick = () => { 
                    const nextLevelId = currentId + 1;
                    window.location.href = `${BASE_URL}/views/lessons/technology_family_tree_game?level=${nextLevelId}`;
                };
            } else {
                nextLevelBtn.style.display = 'none';
                modalMessage.textContent = "Xuất sắc! Bạn đã hoàn thành tất cả các gia đình!";
                if (backToTechnologyBtn) {
                    backToTechnologyBtn.style.display = 'inline-block';
                    backToTechnologyBtn.onclick = () => {
                        window.location.href = `${BASE_URL}/views/lessons/technology.php`;
                    };
                }

                try {
                    fetch(`${BASE_URL}/views/lessons/update-family-tree-score`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'commit' })
                    })
                    .then(r => r.json())
                    .then(j => {
                        console.log('family tree commit response', j);
                        if (j && j.success) {
                            const info = document.createElement('div');
                            info.className = 'server-info';
                            info.textContent = 'Điểm đã được lưu: 100%';
                            modalMessage.appendChild(info);
                        }
                    })
                    .catch(err => console.error('Error committing family tree score', err));
                } catch (e) {
                    console.error('Commit error', e);
                }
            }

            restartGameBtn.textContent = "Chơi lại level này";
            restartGameBtn.className = "game-btn";
            restartGameBtn.onclick = () => { 
                window.location.reload(); 
            };
            
            // Đảm bảo nút Quay về Menu luôn hiển thị và có sự kiện
            if(backToMenuBtn) {
                backToMenuBtn.style.display = 'inline-block';
                backToMenuBtn.onclick = () => {
                    window.location.href = `${BASE_URL}/views/lessons/technology.php`;
                };
            }
            
        } else { 
            modalTitle.textContent = "Thất bại...";
            modalTitle.style.color = "#e74c3c"; 
            modalMessage.textContent = "Bạn đã hết lượt thử. Đừng nản chí nhé!";
            
            nextLevelBtn.style.display = 'none'; 
            
            restartGameBtn.textContent = "Thử lại ngay ↺";
            restartGameBtn.className = "game-btn reset"; 
            restartGameBtn.onclick = () => { 
                window.location.reload();
            };
            
            // Ẩn nút Quay về Menu khi thua
            if(backToMenuBtn) {
                backToMenuBtn.style.display = 'none';
            }
        }

        gameOverModal.style.display = 'flex';
    }
});