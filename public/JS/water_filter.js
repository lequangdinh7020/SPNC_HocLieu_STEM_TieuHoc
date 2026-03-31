document.addEventListener("DOMContentLoaded", () => {
    const materials = document.querySelectorAll('.material-item');
    const bottle = document.getElementById('bottle-layers');
    const placeholder = document.querySelector('.layer-placeholder');
    const testBtn = document.getElementById('test-btn');
    const resetBtn = document.getElementById('reset-btn');
    
    const waterContainer = document.getElementById('water-effect');
    const resultWater = document.getElementById('result-water');

    const modal = document.getElementById('result-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMsg = document.getElementById('modal-message');
    const modalExp = document.getElementById('science-explanation');
    const retryBtn = document.getElementById('retry-btn');

    let currentLayers = [];
    const MAX_LAYERS = 4;
    let draggedId = null;
    let draggedImgUrl = null;

    materials.forEach(mat => {
        mat.addEventListener('dragstart', (e) => {
            draggedId = mat.dataset.id;
            const iconStyle = window.getComputedStyle(mat.querySelector('.mat-icon'));
            draggedImgUrl = iconStyle.backgroundImage;
            e.dataTransfer.setData('text/plain', draggedId);
        });
    });

    bottle.addEventListener('dragover', (e) => {
        e.preventDefault();
        bottle.style.borderColor = '#fbbf24'; 
    });
    bottle.addEventListener('dragleave', () => {
        bottle.style.borderColor = '#3b82f6'; 
    });

    bottle.addEventListener('drop', (e) => {
        e.preventDefault();
        bottle.style.borderColor = '#3b82f6';

        if (currentLayers.length >= MAX_LAYERS) {
            alert("Chai đầy rồi! Hãy nhấn nút Đổ Nước Bẩn.");
            return;
        }

        addLayerToBottle(draggedId, draggedImgUrl);
    });

    function addLayerToBottle(type, imgUrl) {
        if (placeholder) placeholder.style.display = 'none';

        currentLayers.push(type);

        const layerDiv = document.createElement('div');
        layerDiv.className = `layer layer-${type}`;
        
        layerDiv.style.backgroundImage = imgUrl;
        
        let name = '';
        if(type === 'gravel') name = 'Sỏi';
        if(type === 'sand') name = 'Cát';
        if(type === 'charcoal') name = 'Than';
        if(type === 'cotton') name = 'Bông';
        
        layerDiv.innerText = name;
        
        bottle.appendChild(layerDiv);
    }

    resetBtn.addEventListener('click', () => {
        window.location.reload();
    });

    testBtn.addEventListener('click', () => {
        if (currentLayers.length === 0) {
            alert("Bạn chưa bỏ vật liệu nào vào cả!");
            return;
        }

        testBtn.disabled = true;

        const maxDrops = 30;
        let count = 0;
        
        const isCorrect = JSON.stringify(currentLayers) === JSON.stringify(correctOrder);

        const dropInterval = setInterval(() => {
            count++;
            const drop = document.createElement('div');
            drop.className = 'water-drop';
            drop.style.left = (40 + Math.random() * 20) + '%';
            
            drop.style.animation = `fall ${1.5 + Math.random()}s linear forwards`;
            
            waterContainer.appendChild(drop);

            setTimeout(() => drop.remove(), 2000);

            if (count >= maxDrops) clearInterval(dropInterval);
        }, 80);

        setTimeout(() => {
            resultWater.style.height = '70%';
            
            if (isCorrect) {
                resultWater.classList.add('clean-water');
                setTimeout(() => showModal(true), 2500);
            } else {
                setTimeout(() => showModal(false), 2500);
            }
        }, 1500);
    });

    function showModal(isWin) {
        modal.style.display = 'flex';
        if (isWin) {
            modalTitle.innerText = "THÀNH CÔNG!";
            modalTitle.style.color = "#2ecc71";
            modalMsg.innerText = "Nước đã trong veo! Bạn đã cứu dân làng.";
            modalExp.innerHTML = `
                <b>Giải thích khoa học:</b><br>
                1. <b>Sỏi (Trên cùng):</b> Chặn rác lớn.<br>
                2. <b>Cát:</b> Giữ lại bụi bẩn.<br>
                3. <b>Than:</b> Khử độc, khử mùi.<br>
                4. <b>Bông (Dưới cùng):</b> Lọc sạch cặn cuối cùng.
            `;
            modalMsg.style.color = '#000';
            modalExp.style.color = '#000';

            fetch(`${baseUrl}/views/lessons/update-water-filter-score`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'commit', score_pct: 100 })
            }).then(r => r.json()).then(json => {
                if (json && json.success && json.xp_awarded) {
                    const add = document.createElement('div');
                    add.style.marginTop = '10px';
                    add.style.color = '#000';
                    add.innerText = `Bạn nhận được +${json.xp_awarded} XP.`;
                    modal.querySelector('.modal-content').appendChild(add);
                }
            }).catch(err => console.error('Water filter commit error', err));
        } else {
            modalTitle.innerText = "THẤT BẠI";
            modalTitle.style.color = "#e74c3c";
            modalMsg.innerText = "Nước vẫn còn đục.";
            
            let hint = "Hãy nhớ nguyên tắc: <b>Thô ở trên, Mịn ở dưới</b>.";
            if (currentLayers[0] !== 'cotton') hint = "Bạn quên lót <b>Bông</b> ở đáy rồi, than và cát sẽ bị trôi ra ngoài!";
            
            modalExp.innerHTML = hint;
        }
    }

    retryBtn.addEventListener('click', () => window.location.reload());
});