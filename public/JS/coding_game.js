document.addEventListener("DOMContentLoaded", () => {
    const originalMapData = levelData.map; // Dữ liệu gốc từ PHP
    const blockLimit = levelData.limit;
    const timeLimit = levelData.time;
    const targetImg = levelData.target_img;

    const gridMap = document.getElementById('grid-map');
    const programList = document.getElementById('program-list');
    const runBtn = document.getElementById('run-btn');
    const resetBtn = document.getElementById('reset-btn');
    const blockCountSpan = document.getElementById('block-count');
    const timerBar = document.getElementById('timer-bar');
    const timerDisplay = document.getElementById('timer-display');

    // Modal elements
    const storyModal = document.getElementById('story-modal');
    const startBtn = document.getElementById('start-game-btn');
    const resultModal = document.getElementById('result-modal');
    const resultTitle = document.getElementById('result-title');
    const resultMsg = document.getElementById('result-message');
    const nextBtn = document.getElementById('next-level-btn');
    const retryBtn = document.getElementById('retry-btn');

    let robotState = { x: 0, y: 0, dir: 0 }; 
    let startState = {};
    let isRunning = false;
    let timerInterval;
    let timeLeft = 0;
    
    // *** Biến lưu trạng thái bản đồ hiện tại ***
    let currentMapState = []; 

    // --- KHỞI TẠO & CỐT TRUYỆN ---
    
    // Kiểm tra xem đã xem cốt truyện chưa
    const hasSeenIntro = sessionStorage.getItem('hasSeenCodingIntro');

    if (levelData.id === 1 && !hasSeenIntro) {
        // Nếu là Màn 1 và chưa xem -> Hiện modal cốt truyện
        if (storyModal) storyModal.style.display = 'flex';
    } else {
        // Các trường hợp khác (Màn 2, 3... hoặc đã xem) -> Ẩn modal và Tự động bắt đầu
        if (storyModal) storyModal.style.display = 'none';
        startTimer(); 
    }

    // Sự kiện nút "Giúp Sơn Tinh ngay!"
    if (startBtn) {
        startBtn.onclick = () => {
            if (storyModal) storyModal.style.display = 'none';
            // Lưu lại trạng thái đã xem
            sessionStorage.setItem('hasSeenCodingIntro', 'true');
            startTimer();
        };
    }

    function initMap() {
        gridMap.innerHTML = '';
        
        // *** Tạo bản sao của bản đồ để có thể chỉnh sửa (xây cầu) ***
        currentMapState = JSON.parse(JSON.stringify(originalMapData));

        for (let r = 0; r < 5; r++) {
            for (let c = 0; c < 5; c++) {
                const cell = document.createElement('div');
                cell.classList.add('grid-cell');
                cell.dataset.row = r;
                cell.dataset.col = c;

                const val = currentMapState[r][c];
                if (val === 1) cell.classList.add('wall');
                if (val === 4) cell.classList.add('water');
                if (val === 3) {
                    cell.classList.add('goal');
                    const style = document.createElement('style');
                    style.innerHTML = `.grid-cell.goal::after { background-image: url('${baseUrl}/public/images/coding/${targetImg}'); }`;
                    document.head.appendChild(style);
                }
                if (val === 2) {
                    startState = { x: c, y: r, dir: 0 }; 
                    robotState = { ...startState };
                    const robot = document.createElement('div');
                    robot.id = 'robot';
                    cell.appendChild(robot);
                }
                gridMap.appendChild(cell);
            }
        }
    }

    function startTimer() {
        // Nếu game đang chạy hoặc đã kết thúc thì không start lại timer chồng chéo
        clearInterval(timerInterval);
        
        timeLeft = 0;
        const maxTime = timeLimit;
        if (timerBar) timerBar.style.width = '0%';
        if (timerDisplay) timerDisplay.innerText = '00:00';
        
        timerInterval = setInterval(() => {
            timeLeft++;
            const percentage = (timeLeft / maxTime) * 100;
            if (timerBar) timerBar.style.width = `${percentage}%`;
            
            // Hiển thị thời gian dạng MM:SS
            if (timerDisplay) {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerDisplay.innerText = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            }
            
            if (timeLeft >= maxTime) {
                gameOver("Nước lũ đã dâng ngập! Thủy Tinh đã thắng.");
            }
        }, 1000);
    }

    // --- KÉO THẢ ---
    
    const sidebarBlocks = document.querySelectorAll('#block-sidebar .command-block');
    sidebarBlocks.forEach(block => {
        block.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('command', block.dataset.command);
            e.dataTransfer.setData('html', block.innerHTML);
            e.dataTransfer.setData('type', 'new'); 
        });
    });

    setupDropZone(programList);

    function setupDropZone(element) {
        element.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            element.classList.add('drag-over');
            
            // Xử lý sắp xếp lại khi kéo khối đã có trong program-list
            if (element === programList || element.classList.contains('container-block')) {
                const draggingElement = document.querySelector('.dragging');
                const afterElement = getDragAfterElement(element, e.clientY);
                
                if (draggingElement) {
                    if (afterElement == null) {
                        element.appendChild(draggingElement);
                    } else {
                        element.insertBefore(draggingElement, afterElement);
                    }
                }
            }
        });

        element.addEventListener('dragleave', (e) => {
            e.stopPropagation();
            element.classList.remove('drag-over');
        });

        element.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            element.classList.remove('drag-over');

            if (isRunning) return;
            
            // Kiểm tra xem có phải đang kéo khối mới từ sidebar không
            const blockId = e.dataTransfer.getData('block-id');
            if (blockId) {
                // Đang kéo khối đã có (chỉ di chuyển, không tạo mới)
                return;
            }
            
            const currentBlocks = document.querySelectorAll('#program-list .command-block').length;
            if (currentBlocks >= blockLimit) {
                alert("Túi phép thuật đã đầy! (Giới hạn: " + blockLimit + ")");
                return;
            }

            const cmdType = e.dataTransfer.getData('command');
            const cmdHtml = e.dataTransfer.getData('html');

            if (cmdType) {
                createBlockElement(cmdType, cmdHtml, element);
                updateBlockCount();
            }
        });
    }
    
    // Hàm tìm vị trí chèn khi kéo thả
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.command-block:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    function createBlockElement(type, html, container) {
        const div = document.createElement('div');
        div.className = `command-block ${type === 'repeat' ? 'loop' : (type === 'if-water' ? 'condition' : 'move')}`;
        div.dataset.command = type;
        div.innerHTML = html + '<span class="remove-block">×</span>';
        
        // Cho phép kéo thả để sắp xếp lại
        div.draggable = true;
        
        // Xử lý khi bắt đầu kéo khối đã thả
        div.addEventListener('dragstart', (e) => {
            if (isRunning) {
                e.preventDefault();
                return;
            }
            e.stopPropagation();
            div.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('block-id', div.dataset.blockId || Date.now());
            div.dataset.blockId = e.dataTransfer.getData('block-id') || Date.now();
        });
        
        // Xử lý khi kết thúc kéo
        div.addEventListener('dragend', (e) => {
            div.classList.remove('dragging');
        });

        div.querySelector('.remove-block').onclick = (e) => {
            e.stopPropagation();
            if (!isRunning) {
                div.remove();
                updateBlockCount();
            }
        };

        if (type === 'repeat') {
            const innerContainer = document.createElement('div');
            innerContainer.className = 'container-block';
            innerContainer.innerHTML = '<div class="container-label">Thả lệnh cần lặp vào đây:</div>';
            setupDropZone(innerContainer); 
            div.appendChild(innerContainer);
        }

        container.appendChild(div);
        
        const placeholder = document.querySelector('.placeholder-text');
        if (placeholder) placeholder.style.display = 'none';
    }

    function updateBlockCount() {
        const count = document.querySelectorAll('#program-list .command-block').length; 
        blockCountSpan.innerText = count;
    }

    // --- XỬ LÝ CHẠY LỆNH ---
    
    function parseCommands(container) {
        let cmds = [];
        const blocks = Array.from(container.children).filter(el => el.classList.contains('command-block'));
        
        blocks.forEach(block => {
            const type = block.dataset.command;
            if (type === 'repeat') {
                const innerContainer = block.querySelector('.container-block');
                const innerCmds = parseCommands(innerContainer);
                cmds.push({ type: 'loop', count: 3, body: innerCmds });
            } else {
                cmds.push({ type: type });
            }
        });
        return cmds;
    }

    runBtn.addEventListener('click', async () => {
        if (isRunning) return;
        const rootCommands = parseCommands(programList);
        if (rootCommands.length === 0) return;

        isRunning = true;
        runBtn.disabled = true;
        resetBtn.disabled = true;
        
        // Reset vị trí trước khi chạy để map sạch sẽ
        resetRobotPosition();
        await sleep(500);

        await executeCommandList(rootCommands);

        if (isRunning) { 
            checkWinCondition();
        }
        
        isRunning = false;
        runBtn.disabled = false;
        resetBtn.disabled = false;
    });

    async function executeCommandList(cmds) {
        for (const cmd of cmds) {
            if (!isRunning) break;

            if (cmd.type === 'loop') {
                for (let k = 0; k < cmd.count; k++) {
                    await executeCommandList(cmd.body);
                }
            } else {
                await executeSingleCommand(cmd.type);
            }
        }
    }

    async function executeSingleCommand(type) {
        await sleep(500); 

        if (type === 'forward') {
            moveRobot();
        } else if (type === 'turn-left') {
            robotState.dir = (robotState.dir - 1 + 4) % 4;
            updateRobotRotation();
        } else if (type === 'turn-right') {
            robotState.dir = (robotState.dir + 1) % 4;
            updateRobotRotation();
        } else if (type === 'if-water') {
            const target = getTargetCell();
            // Kiểm tra xem ô trước mặt có phải Nước không (theo map hiện tại)
            if (isWater(target.r, target.c)) {
                buildBridge(target.r, target.c);
                await sleep(300);
                moveRobot(); // Tự động đi lên cầu
            }
        }
        
        // Kiểm tra va chạm
        if (checkCollision()) {
            gameOver("Ouch! Sơn Tinh đụng phải chướng ngại vật rồi!");
        }
    }

    function getTargetCell() {
        let r = robotState.y;
        let c = robotState.x;
        if (robotState.dir === 0) r--; // Up
        else if (robotState.dir === 1) c++; // Right
        else if (robotState.dir === 2) r++; // Down
        else if (robotState.dir === 3) c--; // Left
        return { r, c };
    }

    function moveRobot() {
        const target = getTargetCell();
        robotState.x = target.c;
        robotState.y = target.r;
        updateRobotDOM();
    }

    function isWater(r, c) {
        if (r < 0 || r >= 5 || c < 0 || c >= 5) return false;
        // Kiểm tra trên bản đồ hiện tại
        return currentMapState[r][c] === 4; 
    }

    function buildBridge(r, c) {
        const cell = document.querySelector(`.grid-cell[data-row="${r}"][data-col="${c}"]`);
        if (cell) {
            cell.classList.remove('water');
            cell.classList.add('bridge');
            currentMapState[r][c] = 5; 
        }
    }

    function updateRobotDOM() {
        const cell = document.querySelector(`.grid-cell[data-row="${robotState.y}"][data-col="${robotState.x}"]`);
        const robot = document.getElementById('robot');
        if (cell) cell.appendChild(robot);
    }

    function updateRobotRotation() {
        const robot = document.getElementById('robot');
        robot.style.transform = `rotate(${robotState.dir * 90}deg)`;
    }

    function resetRobotPosition() {
        robotState = { ...startState };
        updateRobotDOM();
        updateRobotRotation();
        
        // Reset lại toàn bộ bản đồ về trạng thái gốc
        initMap(); 
    }

    function checkCollision() {
        const r = robotState.y;
        const c = robotState.x;
        
        if (r < 0 || r >= 5 || c < 0 || c >= 5) return true;

        // Kiểm tra trên bản đồ hiện tại
        const val = currentMapState[r][c];
        
        if (val === 1) return true; // Đá
        if (val === 4) return true; // Nước (Chưa có cầu) -> Chết
        // Nếu val === 5 (Cầu) hoặc 0 (Đất) hoặc 3 (Đích) -> An toàn
        
        return false;
    }

    function checkWinCondition() {
        const val = currentMapState[robotState.y][robotState.x];
        if (val === 3) {
            clearInterval(timerInterval);
            showWin();
        } else {
            alert("Sơn Tinh chưa lấy được sính lễ. Thử lại nhé!");
        }
    }

    function gameOver(msg) {
        isRunning = false;
        clearInterval(timerInterval);
        resultTitle.innerText = "THẤT BẠI";
        resultTitle.style.color = "#e74c3c";
        resultMsg.innerText = msg;
        nextBtn.style.display = 'none';
        resultModal.style.display = 'flex';
    }

    function showWin() {
        resultTitle.innerText = "THÀNH CÔNG!";
        resultTitle.style.color = "#2ecc71";
        
        // Lấy đường dẫn ảnh
        const imgSrc = `${baseUrl}/public/images/coding/${levelData.target_img}`;
        
        // Lấy tên vật phẩm từ mission (Ví dụ: "Tìm Voi chín ngà" -> "Voi chín ngà")
        const itemName = levelData.mission ? levelData.mission.replace('Tìm ', '') : 'Sính lễ';

        // Hiển thị cả Chữ và Ảnh
        resultMsg.innerHTML = `
            <p>Bạn đã lấy được <strong>${itemName}</strong>!</p>
            <img src="${imgSrc}" alt="${itemName}" 
                 style="width: 100px; height: auto; margin-top: 10px; filter: drop-shadow(0 5px 5px rgba(0,0,0,0.3));">
        `;
        
        if (levelData.id < totalLevels) {
            nextBtn.style.display = 'inline-block';
            nextBtn.onclick = () => {
                // Chuyển màn bằng cách thay đổi tham số GET 'level' trên URL hiện tại
                const currentPath = window.location.pathname; 
                window.location.href = `${currentPath}?level=${levelData.id + 1}`;
            };
        } else {
            nextBtn.style.display = 'none';
            resultMsg.innerHTML += "<p style='margin-top:10px; color:#e67e22'>Chúc mừng bạn đã hoàn thành tất cả thử thách!</p>";

            // Commit final completion to server and award XP
            try {
                fetch(`${baseUrl}/views/lessons/update-coding-score`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'commit' })
                })
                .then(r => r.json())
                .then(j => {
                    console.log('coding game commit response', j);
                    if (j && j.success) {
                        const info = document.createElement('div');
                        info.className = 'server-info';
                        info.textContent = 'Điểm đã được lưu: 100% - XP +' + (j.xp_awarded || 20);
                        resultMsg.appendChild(info);
                    }
                })
                .catch(err => console.error('Error committing coding score', err));
            } catch (e) {
                console.error('Commit error', e);
            }
        }
        resultModal.style.display = 'flex';
    }

    retryBtn.onclick = () => {
        resultModal.style.display = 'none';
        resetRobotPosition();
        startTimer(); 
    };

    resetBtn.onclick = () => {
        programList.innerHTML = '<div class="placeholder-text">Kéo phép thuật vào đây...</div>';
        updateBlockCount();
        resetRobotPosition();
    };

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    initMap();
});