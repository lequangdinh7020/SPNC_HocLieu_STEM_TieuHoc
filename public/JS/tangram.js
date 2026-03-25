document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    let isDragging = false;
    let dragPiece = null;
    let dragOffsetX, dragOffsetY;
    let isGameComplete = false;

    // Modal elements
    const modal = document.getElementById('result-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMsg = document.getElementById('modal-message');
    const nextBtn = document.getElementById('next-level-btn');
    const retryBtn = document.getElementById('retry-btn');
    const playAgainBtn = document.getElementById('play-again-btn');
    const backBtn = document.getElementById('back-btn');
    const completeBtn = document.getElementById('complete-btn');

    // --- 1. ĐỊNH NGHĨA CÁC MẢNH TANGRAM CHUẨN ---
    // Đơn vị chuẩn hóa, sẽ được scale lên khi vẽ.
    // Tọa độ đỉnh (vertices) tương đối so với tâm (0,0) của mảnh đó.
    const TANGRAM_SHAPES = {
        big: { // Tam giác vuông cân lớn
            vertices: [{x: -2, y: -1}, {x: 2, y: -1}, {x: 0, y: 1}],
            color: '#e74c3c' // Đỏ
        },
        medium: { // Tam giác vuông cân vừa
            vertices: [{x: 0, y: 0}, {x: 1.414, y: 1.414}, {x: -1.414, y: 1.414}],
            color: '#3498db' // Xanh dương
        },
        small: { // Tam giác vuông cân nhỏ
            vertices: [{x: -1, y: -0.5}, {x: 1, y: -0.5}, {x: 0, y: 0.5}],
            color: '#f1c40f' // Vàng
        },
        square: { // Hình vuông
            vertices: [{x: 0, y: -1}, {x: 1, y: 0}, {x: 0, y: 1}, {x: -1, y: 0}],
            color: '#2ecc71' // Xanh lá
        },
        parallelogram: { // Hình bình hành
            vertices: [{x: -1.5, y: -0.5}, {x: 0.5, y: -0.5}, {x: 1.5, y: 0.5}, {x: -0.5, y: 0.5}],
            color: '#9b59b6' // Tím
        }
    };

    const UNIT_SCALE = 60; // Kích thước cơ bản để phóng to các mảnh
    const SNAP_DISTANCE = 30; // Khoảng cách để tự động hút vào vị trí đúng
    const ROTATION_UNIT = Math.PI / 4; // Đơn vị xoay là 45 độ

    // --- 2. KHỞI TẠO CÁC MẢNH CHO LEVEL ---
    let pieces = [];
    // Vị trí bắt đầu rải rác bên ngoài khu vực xếp
    const startPositions = [
        {id: 'big1', type: 'big', x: 650, y: 100, color: '#e74c3c'},
        {id: 'big2', type: 'big', x: 650, y: 250, color: '#c0392b'}, 
        {id: 'medium', type: 'medium', x: 650, y: 400, color: '#3498db'},
        {id: 'square', type: 'square', x: 550, y: 100, color: '#2ecc71'},
        {id: 'small1', type: 'small', x: 550, y: 250, color: '#f1c40f'},
        {id: 'small2', type: 'small', x: 550, y: 350, color: '#f39c12'},
        {id: 'parallelogram', type: 'parallelogram', x: 550, y: 450, color: '#9b59b6'}
    ];

    // Tạo các đối tượng mảnh ghép
    startPositions.forEach(p => {
        pieces.push({
            id: p.id,
            type: p.type,
            // Deep copy đỉnh để không ảnh hưởng gốc
            vertices: JSON.parse(JSON.stringify(TANGRAM_SHAPES[p.type].vertices)),
            color: p.color,
            x: p.x,
            y: p.y,
            rotationState: 0, // 0 đến 7 (nhân với 45 độ)
            isSnapped: false, // Đã vào vị trí đúng chưa
            scale: UNIT_SCALE
        });
    });

    // Vị trí trung tâm để vẽ hình bóng mục tiêu
    const TARGET_OFFSET_X = 200; 
    const TARGET_OFFSET_Y = 300;

    // --- 3. HÀM VẼ (RENDER) ---
    function draw() {
        // Xóa canvas
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // A. Vẽ HÌNH BÓNG MỤC TIÊU (Silhouette)
        // Vẽ mờ các mảnh ở vị trí giải pháp để tạo thành hình bóng
        ctx.save();
        ctx.translate(TARGET_OFFSET_X, TARGET_OFFSET_Y); // Dời gốc tọa độ đến vị trí mục tiêu
        ctx.fillStyle = '#bdc3c7'; // Màu xám cho hình bóng
        ctx.strokeStyle = '#95a5a6';
        ctx.lineWidth = 2;

        for (let pieceId in levelData.solution) {
            const sol = levelData.solution[pieceId];
            // Tìm loại mảnh tương ứng với ID
            const pieceType = pieces.find(p => p.id === pieceId).type;
            drawPolygon(ctx, TANGRAM_SHAPES[pieceType].vertices, sol.x, sol.y, sol.rot * ROTATION_UNIT, UNIT_SCALE, true);
        }
        ctx.restore();


        // B. Vẽ CÁC MẢNH GHÉP CỦA NGƯỜI CHƠI
        // Vẽ các mảnh chưa snap trước, mảnh đang kéo vẽ cuối cùng (để nổi lên trên)
        pieces.filter(p => !p.isSnapped && p !== dragPiece).forEach(p => drawPiece(p));
        pieces.filter(p => p.isSnapped && p !== dragPiece).forEach(p => drawPiece(p));
        if (dragPiece) drawPiece(dragPiece);
    }

    // Hàm phụ trợ để vẽ một mảnh
    function drawPiece(p) {
        ctx.fillStyle = p.color;
        ctx.strokeStyle = '#fff'; // Viền trắng cho dễ nhìn
        ctx.lineWidth = 3;
        // Nếu đã snap thì vẽ ở vị trí mục tiêu, ngược lại vẽ ở vị trí hiện tại
        let drawX = p.x;
        let drawY = p.y;
        if(p.isSnapped) {
             drawX = TARGET_OFFSET_X + levelData.solution[p.id].x;
             drawY = TARGET_OFFSET_Y + levelData.solution[p.id].y;
        }

        drawPolygon(ctx, p.vertices, drawX, drawY, p.rotationState * ROTATION_UNIT, p.scale);
        
        // Hiệu ứng khi đang kéo: thêm bóng và sáng lên
        if (p === dragPiece) {
            ctx.shadowColor = 'rgba(0,0,0,0.3)'; ctx.shadowBlur = 15;
            ctx.fillStyle = lightenColor(p.color, 20);
            drawPolygon(ctx, p.vertices, drawX, drawY, p.rotationState * ROTATION_UNIT, p.scale);
            ctx.shadowBlur = 0; // Reset shadow
        }
    }

    // Hàm cốt lõi: Vẽ một đa giác dựa trên đỉnh, vị trí, góc xoay, tỷ lệ
    function drawPolygon(context, vertices, cx, cy, angle, scale, isSilhouette = false) {
        context.beginPath();
        vertices.forEach((v, i) => {
            // 1. Scale: Phóng to đỉnh
            let vx = v.x * scale;
            let vy = v.y * scale;
            
            // 2. Rotate: Xoay đỉnh quanh gốc (0,0)
            let rx = vx * Math.cos(angle) - vy * Math.sin(angle);
            let ry = vx * Math.sin(angle) + vy * Math.cos(angle);

            // 3. Translate: Dời đến vị trí thực trên canvas
            let finalX = cx + rx;
            let finalY = cy + ry;

            if (i === 0) context.moveTo(finalX, finalY);
            else context.lineTo(finalX, finalY);
        });
        context.closePath();
        if(isSilhouette) {
            context.fill();
            // context.stroke(); // Bỏ comment nếu muốn viền cho silhouette
        } else {
            context.fill();
            context.stroke();
        }
    }


    // --- 4. XỬ LÝ SỰ KIỆN CHUỘT ---

    // Lấy tọa độ chuột trên canvas
    function getMousePos(canvas, evt) {
        const rect = canvas.getBoundingClientRect();
        // Tính toán tỉ lệ nếu canvas bị resize bằng CSS
        const scaleX = canvas.width / rect.width; 
        const scaleY = canvas.height / rect.height;
        return {
            x: (evt.clientX - rect.left) * scaleX,
            y: (evt.clientY - rect.top) * scaleY
        };
    }

    // Thuật toán "Ray Casting" để kiểm tra điểm có nằm trong đa giác không
    function isPointInPolygon(px, py, vertices, cx, cy, angle, scale) {
        // Biến đổi điểm click NGƯỢC LẠI về hệ tọa độ cục bộ của mảnh ghép chưa xoay
        // 1. Translate ngược
        let tx = px - cx;
        let ty = py - cy;
        // 2. Rotate ngược (-angle)
        let rx = tx * Math.cos(-angle) - ty * Math.sin(-angle);
        let ry = tx * Math.sin(-angle) + ty * Math.cos(-angle);
        // 3. Scale ngược
        let localX = rx / scale;
        let localY = ry / scale;

        // Thuật toán Ray Casting trên các đỉnh gốc
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

    // MOUSE DOWN: Bắt đầu kéo
    canvas.addEventListener('mousedown', (e) => {
        if (isGameComplete) return;
        const mouse = getMousePos(canvas, e);
        
        // Kiểm tra click từ mảnh trên cùng xuống dưới
        for (let i = pieces.length - 1; i >= 0; i--) {
            const p = pieces[i];
            // Chỉ cho phép kéo mảnh chưa snap đúng
            if (p.isSnapped) continue; 

            if (isPointInPolygon(mouse.x, mouse.y, p.vertices, p.x, p.y, p.rotationState * ROTATION_UNIT, p.scale)) {
                isDragging = true;
                dragPiece = p;
                // Tính offset để khi kéo không bị giật cục
                dragOffsetX = mouse.x - p.x;
                dragOffsetY = mouse.y - p.y;
                
                // Đưa mảnh đang kéo lên trên cùng mảng để vẽ sau cùng
                pieces.splice(i, 1);
                pieces.push(p);
                draw();
                return;
            }
        }
    });

    // MOUSE MOVE: Di chuyển mảnh
    canvas.addEventListener('mousemove', (e) => {
        if (isDragging && dragPiece) {
            const mouse = getMousePos(canvas, e);
            dragPiece.x = mouse.x - dragOffsetX;
            dragPiece.y = mouse.y - dragOffsetY;
            draw();
        }
    });

    // MOUSE UP: Thả mảnh và kiểm tra snap
    canvas.addEventListener('mouseup', () => {
        if (isDragging && dragPiece) {
            // --- ĐOẠN CODE HỖ TRỢ DEV (Xóa khi hoàn thiện sản phẩm) ---
            // In ra tọa độ hiện tại của mảnh vừa thả để bạn copy vào Controller
            const relativeX = Math.round(dragPiece.x - TARGET_OFFSET_X);
            const relativeY = Math.round(dragPiece.y - TARGET_OFFSET_Y);
            console.log(`'${dragPiece.id}' => ['x' => ${relativeX}, 'y' => ${relativeY}, 'rot' => ${dragPiece.rotationState}],`);
            // -----------------------------------------------------------

            checkSnap(dragPiece);
            isDragging = false;
            dragPiece = null;
            draw();
            checkWinCondition();
        }
    });

    // DOUBLE CLICK: Xoay mảnh
    canvas.addEventListener('dblclick', (e) => {
        if (isGameComplete) return;
        if (isDragging) return; // Không xoay khi đang kéo
        const mouse = getMousePos(canvas, e);
        for (let i = pieces.length - 1; i >= 0; i--) {
            const p = pieces[i];
            if (p.isSnapped) continue;
            if (isPointInPolygon(mouse.x, mouse.y, p.vertices, p.x, p.y, p.rotationState * ROTATION_UNIT, p.scale)) {
                // Tăng góc xoay lên 1 đơn vị (45 độ), quay vòng 0-7
                p.rotationState = (p.rotationState + 1) % 8;
                draw();
                // Sau khi xoay có thể nó khớp luôn, kiểm tra ngay
                checkSnap(p);
                checkWinCondition();
                return;
            }
        }
    });

    

    // Commit điểm: dùng chung cho nút Complete và cho auto-commit màn cuối
    function commitScore(opts) {
        // opts: { pct, mode: 'pieces'|'level', correctCount, completedLevels, totalLevels }
        const pct = opts.pct ?? 0;
        fetch(`${baseUrl}/views/lessons/update-tangram-score`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'commit', score_pct: pct })
        }).then(r => r.json()).then(json => {
            // Show result modal and reveal play-again/back buttons
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

            // UI buttons
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

    // Hoàn thành nhanh: tổng kết số mảnh đúng và gửi lên server
    if (completeBtn) {
        completeBtn.addEventListener('click', () => {
            // Treat completion as finishing current level (count levels completed)
            const completedLevels = levelData.id;
            const pct = totalLevels > 0 ? Math.round((completedLevels / totalLevels) * 100) : 0;
            commitScore({ pct: pct, mode: 'level', completedLevels: completedLevels, totalLevels: totalLevels });
        });
    }

    // --- 5. LOGIC GAME ---

    // Kiểm tra xem mảnh có gần vị trí đúng để "hít" vào không
    function checkSnap(piece) {
        const target = levelData.solution[piece.id];
        // Tính tọa độ tuyệt đối trên canvas của vị trí đích
        const targetAbsX = TARGET_OFFSET_X + target.x;
        const targetAbsY = TARGET_OFFSET_Y + target.y;

        // Tính khoảng cách giữa vị trí hiện tại và đích
        const dist = Math.sqrt((piece.x - targetAbsX)**2 + (piece.y - targetAbsY)**2);
        
        // Điều kiện Snap: Khoảng cách gần VÀ góc xoay đúng
        // (Cho phép sai số góc xoay nhỏ hoặc dùng đúng rotationState)
        if (dist < SNAP_DISTANCE && piece.rotationState === target.rot) {
            piece.isSnapped = true;
            // Đặt vị trí chính xác vào đích (để vẽ đẹp)
            piece.x = targetAbsX;
            piece.y = targetAbsY;
            // Phát âm thanh 'click' nếu muốn
        } else {
            piece.isSnapped = false;
        }
    }

    function checkWinCondition() {
        // Thắng nếu TẤT CẢ các mảnh đều đã snap
        const allSnapped = pieces.every(p => p.isSnapped);
        if (allSnapped && !isGameComplete) {
            isGameComplete = true;
            draw(); // Vẽ lần cuối để thấy kết quả hoàn hảo
            setTimeout(showWinModal, 500); // Hiện thông báo sau 0.5s
        }
    }

    // Hàm làm sáng màu (cho hiệu ứng khi kéo)
    function lightenColor(color, percent) {
        var num = parseInt(color.replace("#",""),16),
        amt = Math.round(2.55 * percent),
        R = (num >> 16) + amt,
        B = (num >> 8 & 0x00ff) + amt,
        G = (num & 0x0000ff) + amt;
        return "#" + (0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (B<255?B<1?0:B:255)*0x100 + (G<255?G<1?0:G:255)).toString(16).slice(1);
    }

    // --- 6. XỬ LÝ KẾT THÚC ---
    function showWinModal() {
        modal.style.display = 'flex';
        modalTitle.innerText = "HOÀN THÀNH! 🎉";
        modalMsg.innerText = "Bạn đã ghép thành công hình này!";
        // Restart whole game from level 1
        retryBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/math_tangram_3d?level=1`;

        // After winning, allow user to proceed to next level OR commit via Complete button
        if (levelData.id < totalLevels) {
            nextBtn.style.display = 'inline-block';
            nextBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/math_tangram_3d?level=${levelData.id + 1}`;
        } else {
            // Final level: auto-commit the perfect score
            const correctCount = pieces.filter(p => p.isSnapped).length;
            const pct = pieces.length > 0 ? Math.round((correctCount / pieces.length) * 100) : 0;
            // commitScore will show modal and relevant buttons
            commitScore({ pct: pct, mode: 'pieces', correctCount: correctCount });
            return;
        }

        // Ensure play-again/back hidden until explicit commit
        if (playAgainBtn) playAgainBtn.style.display = 'none';
        if (backBtn) backBtn.style.display = 'none';
    }

    // Vẽ lần đầu khi tải trang
    draw();
});