document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    let isDragging = false;
    let dragPiece = null;
    let dragOffsetX, dragOffsetY;
    let isGameComplete = false;

    const modal = document.getElementById('result-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMsg = document.getElementById('modal-message');
    const nextBtn = document.getElementById('next-level-btn');
    const retryBtn = document.getElementById('retry-btn');
    const playAgainBtn = document.getElementById('play-again-btn');
    const backBtn = document.getElementById('back-btn');
    const completeBtn = document.getElementById('complete-btn');

    const TANGRAM_SHAPES = {
        big: { 
            vertices: [{x: -2, y: -1}, {x: 2, y: -1}, {x: 0, y: 1}],
            color: '#e74c3c'
        },
        medium: { 
            vertices: [{x: 0, y: 0}, {x: 1.414, y: 1.414}, {x: -1.414, y: 1.414}],
            color: '#3498db'
        },
        small: {
            vertices: [{x: -1, y: -0.5}, {x: 1, y: -0.5}, {x: 0, y: 0.5}],
            color: '#f1c40f'
        },
        square: { 
            vertices: [{x: 0, y: -1}, {x: 1, y: 0}, {x: 0, y: 1}, {x: -1, y: 0}],
            color: '#2ecc71'
        },
        parallelogram: {
            vertices: [{x: -1.5, y: -0.5}, {x: 0.5, y: -0.5}, {x: 1.5, y: 0.5}, {x: -0.5, y: 0.5}],
            color: '#9b59b6'
        }
    };

    const UNIT_SCALE = 60;
    const SNAP_DISTANCE = 30;
    const ROTATION_UNIT = Math.PI / 4;

    let pieces = [];
    const startPositions = [
        {id: 'big1', type: 'big', x: 650, y: 100, color: '#e74c3c'},
        {id: 'big2', type: 'big', x: 650, y: 250, color: '#c0392b'}, 
        {id: 'medium', type: 'medium', x: 650, y: 400, color: '#3498db'},
        {id: 'square', type: 'square', x: 550, y: 100, color: '#2ecc71'},
        {id: 'small1', type: 'small', x: 550, y: 250, color: '#f1c40f'},
        {id: 'small2', type: 'small', x: 550, y: 350, color: '#f39c12'},
        {id: 'parallelogram', type: 'parallelogram', x: 550, y: 450, color: '#9b59b6'}
    ];

    startPositions.forEach(p => {
        pieces.push({
            id: p.id,
            type: p.type,
            vertices: JSON.parse(JSON.stringify(TANGRAM_SHAPES[p.type].vertices)),
            color: p.color,
            x: p.x,
            y: p.y,
            rotationState: 0,
            isSnapped: false,
            scale: UNIT_SCALE
        });
    });

    const TARGET_OFFSET_X = 200; 
    const TARGET_OFFSET_Y = 300;

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.save();
        ctx.translate(TARGET_OFFSET_X, TARGET_OFFSET_Y);
        ctx.fillStyle = '#bdc3c7';
        ctx.strokeStyle = '#95a5a6';
        ctx.lineWidth = 2;

        for (let pieceId in levelData.solution) {
            const sol = levelData.solution[pieceId];
            const pieceType = pieces.find(p => p.id === pieceId).type;
            drawPolygon(ctx, TANGRAM_SHAPES[pieceType].vertices, sol.x, sol.y, sol.rot * ROTATION_UNIT, UNIT_SCALE, true);
        }
        ctx.restore();

        pieces.filter(p => !p.isSnapped && p !== dragPiece).forEach(p => drawPiece(p));
        pieces.filter(p => p.isSnapped && p !== dragPiece).forEach(p => drawPiece(p));
        if (dragPiece) drawPiece(dragPiece);
    }

    function drawPiece(p) {
        ctx.fillStyle = p.color;
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 3;
        let drawX = p.x;
        let drawY = p.y;
        if(p.isSnapped) {
             drawX = TARGET_OFFSET_X + levelData.solution[p.id].x;
             drawY = TARGET_OFFSET_Y + levelData.solution[p.id].y;
        }

        drawPolygon(ctx, p.vertices, drawX, drawY, p.rotationState * ROTATION_UNIT, p.scale);
        
        if (p === dragPiece) {
            ctx.shadowColor = 'rgba(0,0,0,0.3)'; ctx.shadowBlur = 15;
            ctx.fillStyle = lightenColor(p.color, 20);
            drawPolygon(ctx, p.vertices, drawX, drawY, p.rotationState * ROTATION_UNIT, p.scale);
            ctx.shadowBlur = 0;
        }
    }

    function drawPolygon(context, vertices, cx, cy, angle, scale, isSilhouette = false) {
        context.beginPath();
        vertices.forEach((v, i) => {
            let vx = v.x * scale;
            let vy = v.y * scale;
            
            let rx = vx * Math.cos(angle) - vy * Math.sin(angle);
            let ry = vx * Math.sin(angle) + vy * Math.cos(angle);

            let finalX = cx + rx;
            let finalY = cy + ry;

            if (i === 0) context.moveTo(finalX, finalY);
            else context.lineTo(finalX, finalY);
        });
        context.closePath();
        if(isSilhouette) {
            context.fill();
        } else {
            context.fill();
            context.stroke();
        }
    }

    function getMousePos(canvas, evt) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width; 
        const scaleY = canvas.height / rect.height;
        return {
            x: (evt.clientX - rect.left) * scaleX,
            y: (evt.clientY - rect.top) * scaleY
        };
    }

    function isPointInPolygon(px, py, vertices, cx, cy, angle, scale) {
        let tx = px - cx;
        let ty = py - cy;
        let rx = tx * Math.cos(-angle) - ty * Math.sin(-angle);
        let ry = tx * Math.sin(-angle) + ty * Math.cos(-angle);
        let localX = rx / scale;
        let localY = ry / scale;

        let inside = false;
        for (let i = 0, j = vertices.length - 1; i < vertices.length; j = i++) {
            let xi = vertices[i].x, yi = vertices[i].y;
            let xj = vertices[j].x, yj = vertices[j].y;
            
            let intersect = ((yi > localY) !== (yj > localY)) &&
                (localX < (xj - xi) * (localY - yi) / (yj - yi) + xi);
            if (intersect) inside = !inside;
        }
        return inside;
    }

    canvas.addEventListener('mousedown', (e) => {
        if (isGameComplete) return;
        const mouse = getMousePos(canvas, e);
        
        for (let i = pieces.length - 1; i >= 0; i--) {
            const p = pieces[i];
            if (p.isSnapped) continue; 

            if (isPointInPolygon(mouse.x, mouse.y, p.vertices, p.x, p.y, p.rotationState * ROTATION_UNIT, p.scale)) {
                isDragging = true;
                dragPiece = p;
                dragOffsetX = mouse.x - p.x;
                dragOffsetY = mouse.y - p.y;
                
                pieces.splice(i, 1);
                pieces.push(p);
                draw();
                return;
            }
        }
    });

    canvas.addEventListener('mousemove', (e) => {
        if (isDragging && dragPiece) {
            const mouse = getMousePos(canvas, e);
            dragPiece.x = mouse.x - dragOffsetX;
            dragPiece.y = mouse.y - dragOffsetY;
            draw();
        }
    });

    canvas.addEventListener('mouseup', () => {
        if (isDragging && dragPiece) {
            const relativeX = Math.round(dragPiece.x - TARGET_OFFSET_X);
            const relativeY = Math.round(dragPiece.y - TARGET_OFFSET_Y);
            console.log(`'${dragPiece.id}' => ['x' => ${relativeX}, 'y' => ${relativeY}, 'rot' => ${dragPiece.rotationState}],`);

            checkSnap(dragPiece);
            isDragging = false;
            dragPiece = null;
            draw();
            checkWinCondition();
        }
    });

    canvas.addEventListener('dblclick', (e) => {
        if (isGameComplete) return;
        if (isDragging) return;
        const mouse = getMousePos(canvas, e);
        for (let i = pieces.length - 1; i >= 0; i--) {
            const p = pieces[i];
            if (p.isSnapped) continue;
            if (isPointInPolygon(mouse.x, mouse.y, p.vertices, p.x, p.y, p.rotationState * ROTATION_UNIT, p.scale)) {
                p.rotationState = (p.rotationState + 1) % 8;
                draw();
                checkSnap(p);
                checkWinCondition();
                return;
            }
        }
    });

    

    function commitScore(opts) {
        const pct = opts.pct ?? 0;
        fetch(`${baseUrl}/views/lessons/update-tangram-score`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'commit', score_pct: pct })
        }).then(r => r.json()).then(json => {
            modal.style.display = 'flex';
            if (json && json.success) {
                modalTitle.innerText = 'Tổng kết hoàn thành';
                modalTitle.style.color = '#2ecc71';
                if (opts.mode === 'level') {
                    modalMsg.innerText = `Bạn hoàn thành ${opts.completedLevels}/${opts.totalLevels} màn. Độ hoàn thành: ${pct}%`;
                } else {
                    modalMsg.innerText = `Bạn ghép đúng ${opts.correctCount}/${pieces.length}. Độ chính xác: ${pct}%`;
                }
                if (json.xp_awarded) modalMsg.innerText += `\nBạn nhận được +${json.xp_awarded} XP`;
            } else {
                modalTitle.innerText = 'Lưu điểm thất bại';
                modalTitle.style.color = '#e74c3c';
                modalMsg.innerText = (json && json.message) ? json.message : 'Không thể lưu điểm';
            }

            nextBtn.style.display = 'none';
            retryBtn.style.display = 'none';
            if (playAgainBtn) { playAgainBtn.style.display = 'inline-block'; playAgainBtn.onclick = () => { modal.style.display = 'none'; window.location.reload(); } }
            if (backBtn) { backBtn.style.display = 'inline-block'; backBtn.onclick = () => { window.location.href = `${baseUrl}/views/lessons/math.php`; } }
        }).catch(err => {
            console.error('Commit error', err);
            modal.style.display = 'flex';
            modalTitle.innerText = 'Lưu điểm thất bại';
            modalTitle.style.color = '#e74c3c';
            modalMsg.innerText = 'Không thể kết nối tới server';
            nextBtn.style.display = 'none';
            retryBtn.style.display = 'inline-block';
        });
    }

    if (completeBtn) {
        completeBtn.addEventListener('click', () => {
            const completedLevels = levelData.id;
            const pct = totalLevels > 0 ? Math.round((completedLevels / totalLevels) * 100) : 0;
            commitScore({ pct: pct, mode: 'level', completedLevels: completedLevels, totalLevels: totalLevels });
        });
    }

    function checkSnap(piece) {
        const target = levelData.solution[piece.id];
        const targetAbsX = TARGET_OFFSET_X + target.x;
        const targetAbsY = TARGET_OFFSET_Y + target.y;

        const dist = Math.sqrt((piece.x - targetAbsX)**2 + (piece.y - targetAbsY)**2);
        
        if (dist < SNAP_DISTANCE && piece.rotationState === target.rot) {
            piece.isSnapped = true;
            piece.x = targetAbsX;
            piece.y = targetAbsY;
        } else {
            piece.isSnapped = false;
        }
    }

    function checkWinCondition() {
        const allSnapped = pieces.every(p => p.isSnapped);
        if (allSnapped && !isGameComplete) {
            isGameComplete = true;
            draw();
            setTimeout(showWinModal, 500);
        }
    }

    function lightenColor(color, percent) {
        var num = parseInt(color.replace("#",""),16),
        amt = Math.round(2.55 * percent),
        R = (num >> 16) + amt,
        B = (num >> 8 & 0x00ff) + amt,
        G = (num & 0x0000ff) + amt;
        return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (B<255?B<1?0:B:255)*0x100 + (G<255?G<1?0:G:255)).toString(16).slice(1);
    }

    function showWinModal() {
        modal.style.display = 'flex';
        modalTitle.innerText = "HOÀN THÀNH!";
        modalMsg.innerText = "Bạn đã ghép thành công hình này!";

        retryBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/math_tangram_3d?level=1`;

        if (levelData.id < totalLevels) {
            nextBtn.style.display = 'inline-block';
            nextBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/math_tangram_3d?level=${levelData.id + 1}`;
        } else {
            const correctCount = pieces.filter(p => p.isSnapped).length;
            const pct = pieces.length > 0 ? Math.round((correctCount / pieces.length) * 100) : 0;
            commitScore({ pct: pct, mode: 'pieces', correctCount: correctCount });
            return;
        }

        if (playAgainBtn) playAgainBtn.style.display = 'none';
        if (backBtn) backBtn.style.display = 'none';
    }

    draw();
});