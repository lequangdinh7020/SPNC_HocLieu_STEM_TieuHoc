document.addEventListener("DOMContentLoaded", () => {
    const gridEl = document.getElementById('pipe-grid');
    const checkBtn = document.getElementById('check-flow-btn');
    const gridSize = levelData.grid_size;
    
    const resultModal = document.getElementById('result-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMsg = document.getElementById('modal-message');
    const nextBtn = document.getElementById('next-btn');
    const retryBtn = document.getElementById('retry-btn'); 

    const storyModal = document.getElementById('story-modal');

    if (levelData.id === 1 && !sessionStorage.getItem('hasSeenPipeIntro')) {
        if(storyModal) storyModal.style.display = 'flex';
    } else {
        if(storyModal) storyModal.style.display = 'none';
    }

    window.startGame = function() {
        if(storyModal) storyModal.style.display = 'none';
        sessionStorage.setItem('hasSeenPipeIntro', 'true');
    }

    let gridState = [];
    let targets = [];  
    let startPos = -1;

    const PIPES = {
        'I': [0, 2], 'L': [0, 1], 'T': [1, 2, 3], 'S': [1], 'E': [3], '0': []            
    };

    initGame();

    function initGame() {
        gridEl.innerHTML = '';
        gridState = [];
        targets = [];

        levelData.layout.forEach((type, index) => {
            const cell = document.createElement('div');
            cell.className = 'cell';
            
            let rotation = 0;
            if (type !== '0' && type !== 'S' && type !== 'E') {
                rotation = Math.floor(Math.random() * 4) * 90;
            }
            if (levelData.id === 1 && type === 'I') rotation = 90;

            gridState.push({ type: type, rotation: rotation, index: index });

            if (type === 'S') startPos = index;
            if (type === 'E') targets.push(index);

            const pipeDiv = document.createElement('div');
            pipeDiv.className = `pipe pipe-${type}`;
            pipeDiv.style.transform = `rotate(${rotation}deg)`;

            cell.appendChild(pipeDiv);
            cell.dataset.index = index;

            if (type !== '0' && type !== 'S' && type !== 'E') {
                cell.addEventListener('click', () => rotatePipe(index, pipeDiv));
            }

            gridEl.appendChild(cell);
        });
    }

    function rotatePipe(index, element) {
        if (checkBtn.disabled) return; 
        gridState[index].rotation = (gridState[index].rotation + 90) % 360;
        element.style.transform = `rotate(${gridState[index].rotation}deg)`;
    }

    checkBtn.addEventListener('click', () => {
        checkBtn.disabled = true;
        simulateFlow();
    });

    async function simulateFlow() {
        document.querySelectorAll('.water-filled').forEach(el => el.classList.remove('water-filled'));
        document.querySelectorAll('.leak-spot').forEach(el => el.remove());

        let queue = [startPos];
        let visited = new Set();
        visited.add(startPos);
        
        let reachedTargets = 0;
        let leakFound = false;

        highlightPipe(startPos);

        while (queue.length > 0) {
            let currIdx = queue.shift();
            await new Promise(r => setTimeout(r, 200));

            let currObj = gridState[currIdx];
            let currExits = getExits(currObj.type, currObj.rotation);

            const neighbors = [
                { dir: 0, idx: currIdx - gridSize }, 
                { dir: 1, idx: currIdx + 1 },        
                { dir: 2, idx: currIdx + gridSize }, 
                { dir: 3, idx: currIdx - 1 }         
            ];

            for (let nb of neighbors) {
                if (!isValidNeighbor(currIdx, nb.idx, nb.dir)) continue;

                if (currExits.includes(nb.dir)) {
                    let nextObj = gridState[nb.idx];
                    let requiredInput = (nb.dir + 2) % 4;
                    let nextExits = getExits(nextObj.type, nextObj.rotation);

                    if (nextExits.includes(requiredInput)) {
                        if (!visited.has(nb.idx)) {
                            visited.add(nb.idx);
                            queue.push(nb.idx);
                            highlightPipe(nb.idx);

                            if (nextObj.type === 'E') {
                                reachedTargets++;
                            }
                        }
                    } else {
                        showLeak(currIdx, nb.dir);
                        leakFound = true;
                    }
                }
            }
        }

        setTimeout(() => {
            if (leakFound) {
                showModal(false, "Ối! Nước bị rò rỉ ra ngoài rồi. Kiểm tra lại các khớp nối nhé!");
            } else if (reachedTargets === targets.length) {
                showModal(true, "Tuyệt vời! Cây đã được tưới nước mát lành.");
            } else {
                showModal(false, "Nước vẫn chưa chảy tới được cây. Hãy thử đường khác xem.");
            }
            checkBtn.disabled = false;
        }, 500);
    }

    function getExits(type, rotation) {
        let steps = rotation / 90;
        let baseExits = PIPES[type];
        return baseExits.map(dir => (dir + steps) % 4);
    }

    function isValidNeighbor(currIdx, nextIdx, dir) {
        if (nextIdx < 0 || nextIdx >= gridSize * gridSize) return false;
        let currCol = currIdx % gridSize;
        let nextCol = nextIdx % gridSize;
        if (dir === 1 && nextCol === 0) return false; 
        if (dir === 3 && currCol === gridSize - 1) return false;
        if (dir === 3 && currCol === 0) return false;
        return true;
    }

    function highlightPipe(index) {
        const cell = gridEl.children[index];
        const pipe = cell.querySelector('.pipe');
        if(pipe) pipe.classList.add('water-filled');
    }

    function showLeak(index, dir) {
        const cell = gridEl.children[index];
        const leak = document.createElement('div');
        leak.className = 'leak-spot';
        if(dir === 0) leak.style.cssText = "top: 0; left: 50%;";
        if(dir === 1) leak.style.cssText = "top: 50%; right: 0;";
        if(dir === 2) leak.style.cssText = "bottom: 0; left: 50%;";
        if(dir === 3) leak.style.cssText = "top: 50%; left: 0;";
        cell.appendChild(leak);
    }

    function showModal(isWin, msg) {
        resultModal.style.display = 'flex';
        modalTitle.innerText = isWin ? "THÀNH CÔNG!" : "CỐ LÊN!";
        modalTitle.style.color = isWin ? "#2ecc71" : "#e74c3c";
        modalMsg.innerText = msg;
        
        nextBtn.style.display = 'inline-block';
        retryBtn.style.display = 'none'; 

        if (isWin) {
            if (levelData.id < totalLevels) {
                nextBtn.innerText = "Màn tiếp theo";
                nextBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/engineering_water_pipe?level=${levelData.id + 1}`;
            } else {
                    nextBtn.innerText = "Về Trang Chủ";
                    nextBtn.onclick = () => window.location.href = `${baseUrl}/views/main_lesson.php`;

                    fetch(`${baseUrl}/views/lessons/update-water-pipe-score`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'commit', score_pct: 100 })
                    }).then(r => r.json()).then(json => {
                        if (json && json.success && json.xp_awarded) {
                            const add = document.createElement('div');
                            add.style.marginTop = '10px';
                            add.style.color = '#000';
                            add.innerText = `Bạn nhận được +${json.xp_awarded} XP.`;
                            resultModal.querySelector('.modal-content').appendChild(add);
                        }
                    }).catch(err => console.error('Water pipe commit error', err));

                    retryBtn.style.display = 'none';
            }
        } else {
            nextBtn.style.display = 'none';

            retryBtn.style.display = 'inline-block';
            retryBtn.innerText = "Thử lại";
            retryBtn.onclick = () => {
                resultModal.style.display = 'none';
                document.querySelectorAll('.water-filled').forEach(el => el.classList.remove('water-filled'));
                document.querySelectorAll('.leak-spot').forEach(el => el.remove());
            };
        }
    }
});