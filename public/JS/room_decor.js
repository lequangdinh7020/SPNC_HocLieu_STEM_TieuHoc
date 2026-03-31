const itemsGrid = document.getElementById('items-grid');
const furnitureLayer = document.getElementById('furniture-layer');
const rugLayer = document.getElementById('rug-layer');

const bgMain = document.getElementById('bg-main'); 

const trashCan = document.getElementById('trash-can');
const roomContainer = document.getElementById('room-container');

let draggedItem = null;
let offset = { x: 0, y: 0 };
let currentCategory = Object.keys(categories)[0]; 

document.addEventListener("DOMContentLoaded", () => {
    renderItems(currentCategory);
});

window.switchCategory = function(catKey, btn) {
    currentCategory = catKey;
    
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    renderItems(catKey);
}

function renderItems(catKey) {
    itemsGrid.innerHTML = ''; 
    const catData = categories[catKey];
    
    if (catData && catData.items) {
        catData.items.forEach(item => {
            const div = document.createElement('div');
            div.className = 'item-card';
            
            div.innerHTML = `
                <div class="img-wrapper">
                    <img src="${baseUrl}/public/images/room_decor/${item.img}" style="max-width:100%; max-height:100%;">
                </div>
                <span>${item.name}</span>
            `;

            if (item.type === 'room') {
                div.onclick = () => changeRoomBackground(item.img);
            } else {
                const isRug = (catKey === 'rug');
                div.onclick = () => spawnItem(item.id, item.img, item.w, catKey, isRug);
            }
            
            itemsGrid.appendChild(div);
        });
    }
}
function changeRoomBackground(imgSrc) {
    const fullPath = `${baseUrl}/public/images/room_decor/${imgSrc}`;
    
    if(bgMain) {
        bgMain.src = fullPath;
    } else {
        console.error("Không tìm thấy element #bg-main. Hãy kiểm tra lại file View.");
    }
}

function spawnItem(id, imgSrc, width, category, isRug = false) {
    const el = document.createElement('img');
    el.src = `${baseUrl}/public/images/room_decor/${imgSrc}`;
    el.className = 'room-item';
    el.style.width = width + 'px';
    
    el.style.left = (300 + Math.random() * 100) + 'px';
    el.style.top = (300 + Math.random() * 100) + 'px';
    
    el.dataset.itemId = id; 

    el.dataset.flipped = "false";
    el.dataset.category = category; 
    el.dataset.isRug = isRug;

    el.addEventListener('mousedown', startDrag);
    el.addEventListener('dblclick', flipItem);
    
    el.addEventListener('dragstart', (e) => e.preventDefault());

    if (isRug) {
        el.style.zIndex = 1; 
        rugLayer.appendChild(el);
    } else {
        furnitureLayer.appendChild(el);
        updateZIndex(el);
    }
}

function startDrag(e) {
    if (e.button !== 0) return;
    
    e.preventDefault();
    draggedItem = e.target;
    
    const rect = draggedItem.getBoundingClientRect();
    offset.x = e.clientX - rect.left;
    offset.y = e.clientY - rect.top;

    if (draggedItem.dataset.isRug !== "true") {
        draggedItem.style.zIndex = 99999;
    }

    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', endDrag);
}

function drag(e) {
    if (!draggedItem) return;
    e.preventDefault();

    const containerRect = roomContainer.getBoundingClientRect();
    
    let newX = e.clientX - containerRect.left - offset.x;
    let newY = e.clientY - containerRect.top - offset.y;

    newX = Math.max(-50, Math.min(newX, containerRect.width - 50));
    newY = Math.max(-50, Math.min(newY, containerRect.height - 50));

    draggedItem.style.left = newX + 'px';
    draggedItem.style.top = newY + 'px';

    checkTrashHover(e.clientX, e.clientY);
}

function endDrag(e) {
    if (!draggedItem) return;

    const trashRect = trashCan.getBoundingClientRect();
    const isOverTrash = (
        e.clientX >= trashRect.left && e.clientX <= trashRect.right &&
        e.clientY >= trashRect.top && e.clientY <= trashRect.bottom
    );

    if (isOverTrash) {
        draggedItem.remove();
    } else {
        if (draggedItem.dataset.isRug !== "true") {
            updateZIndex(draggedItem);
        }
    }

    trashCan.classList.remove('drag-over');
    draggedItem = null;
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', endDrag);
}

function checkTrashHover(mouseX, mouseY) {
    const trashRect = trashCan.getBoundingClientRect();
    if (mouseX >= trashRect.left && mouseX <= trashRect.right &&
        mouseY >= trashRect.top && mouseY <= trashRect.bottom) {
        trashCan.classList.add('drag-over');
    } else {
        trashCan.classList.remove('drag-over');
    }
}

function updateZIndex(el) {
    if (el.dataset.isRug === "true") return;

    const itemId = el.dataset.itemId || "";
    const category = el.dataset.category;

    if (itemId.includes('window') || itemId.includes('poster')) {
        el.style.zIndex = 10; 
        return; 
    }
    const rect = el.getBoundingClientRect();
    
    let depth = Math.floor(parseInt(el.style.top) + rect.height);

    if (category === 'decor' || category === 'misc' || category === 'toy') {
        depth += 2000; 
    }
    
    if (category === 'rug') {
        depth = 1;
    }

    el.style.zIndex = depth;
}

function flipItem(e) {
    const el = e.target;
    if (el.dataset.flipped === "false") {
        el.style.transform = "scaleX(-1)";
        el.dataset.flipped = "true";
    } else {
        el.style.transform = "scaleX(1)";
        el.dataset.flipped = "false";
    }
}
window.clearRoom = function() {
    if(confirm('Bạn có chắc muốn xóa hết đồ đạc trong phòng không?')) {
        furnitureLayer.innerHTML = '';
        rugLayer.innerHTML = '';
    }
}
if(document.getElementById('save-btn')) {
    document.getElementById('save-btn').addEventListener('click', () => {
        html2canvas(roomContainer, {
            useCORS: true,
            scale: 2 
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'thiet-ke-phong-cua-em.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });
}