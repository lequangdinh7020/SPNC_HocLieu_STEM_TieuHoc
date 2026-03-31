document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById('gameCanvas');
    const ctx = canvas.getContext('2d');
    
    const angleSlider = document.getElementById('angle-slider');
    const angleValue = document.getElementById('angle-value');
    const angleType = document.getElementById('angle-type');
    const fireBtn = document.getElementById('fire-btn');
    const missFeedback = document.getElementById('miss-feedback');
    
    const modal = document.getElementById('result-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMsg = document.getElementById('modal-message');
    const nextBtn = document.getElementById('next-level-btn');
    const retryBtn = document.getElementById('retry-btn');

    function resizeCanvas() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        
        playerX = canvas.width / 2;
        playerY = canvas.height - 80; 
        
        sunX = canvas.width * levelData.sun_pos.x;
        sunY = canvas.height * levelData.sun_pos.y;
        
        drawScene();
    }
    window.addEventListener('resize', resizeCanvas);

    const imgHouYi = new Image(); imgHouYi.src = `${baseUrl}/public/images/angle_game/hou_yi.png`;
    const imgSun = new Image(); imgSun.src = `${baseUrl}/public/images/angle_game/sun.png`;
    const imgArrow = new Image(); imgArrow.src = `${baseUrl}/public/images/angle_game/arrow.png`;

    let currentAngle = 0; 
    let arrow = null; 
    let isFired = false;
    
    let playerX, playerY, sunX, sunY;
    const sunRadius = 50; 

    resizeCanvas();

    angleSlider.addEventListener('input', (e) => {
        currentAngle = parseInt(e.target.value);
        angleValue.innerText = currentAngle;
        
        if (currentAngle < 90) {
            angleType.innerText = "Góc Nhọn";
            angleType.style.color = "#2ecc71";
        } else if (currentAngle === 90) {
            angleType.innerText = "Góc Vuông";
            angleType.style.color = "#f1c40f";
        } else {
            angleType.innerText = "Góc Tù";
            angleType.style.color = "#e74c3c";
        }
        drawScene();
    });

    fireBtn.addEventListener('click', () => {
        if (isFired) return;
        isFired = true;
        fireBtn.disabled = true;

        const power = 25; 
        const rad = currentAngle * (Math.PI / 180);
        
        arrow = {
            x: playerX,
            y: playerY - 60,
            vx: power * Math.cos(rad),
            vy: -power * Math.sin(rad),
            angle: -rad
        };

        gameLoop();
    });

    function gameLoop() {
        if (!isFired) return;
        updatePhysics();
        drawScene();
        if (isFired) requestAnimationFrame(gameLoop);
    }

    function updatePhysics() {
        if (!arrow) return;
        arrow.x += arrow.vx;
        arrow.y += arrow.vy;
        arrow.vy += 0.4;

        arrow.angle = Math.atan2(arrow.vy, arrow.vx);

        const dist = Math.sqrt((arrow.x - sunX)**2 + (arrow.y - sunY)**2);
        if (dist < sunRadius + 20) {
            endGame(true);
            return;
        }

        if (arrow.y > canvas.height + 50 || arrow.x < -50 || arrow.x > canvas.width + 50) {
            endGame(false);
        }
    }

    function drawScene() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.drawImage(imgSun, sunX - sunRadius, sunY - sunRadius, sunRadius*2, sunRadius*2);

        ctx.save();
        if (currentAngle > 90) {
            ctx.translate(playerX, playerY);
            ctx.scale(-1, 1); 
            ctx.drawImage(imgHouYi, -60, -80, 120, 160); 
        } else {
            ctx.drawImage(imgHouYi, playerX - 60, playerY - 80, 120, 160);
        }
        ctx.restore();

        if (arrow) {
            ctx.save();
            ctx.translate(arrow.x, arrow.y);
            ctx.rotate(arrow.angle);
            ctx.drawImage(imgArrow, -30, -5, 60, 10);
            ctx.restore();
        } else {
            const rad = currentAngle * (Math.PI / 180);
            const aimLen = 150;
            const aimX = playerX + aimLen * Math.cos(rad);
            const aimY = (playerY - 60) - aimLen * Math.sin(rad);
            
            ctx.beginPath();
            ctx.moveTo(playerX, playerY - 60);
            ctx.lineTo(aimX, aimY);
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.8)'; 
            ctx.setLineDash([10, 10]);
            ctx.lineWidth = 3;
            ctx.stroke();
            ctx.setLineDash([]);
        }
    }
    
    imgHouYi.onload = drawScene;

    function endGame(isWin) {
        isFired = false;
        arrow = null;

        if (isWin) {
            modal.style.display = 'flex';
            modalTitle.innerText = "TRÚNG RỒI!";
            modalTitle.style.color = "green";
            modalMsg.innerText = "Bạn đã bắn rụng mặt trời hung ác!";
            
            retryBtn.style.display = 'none';

            if (levelData.id < totalLevels) {
                nextBtn.style.display = 'inline-block';
                nextBtn.innerText = "Màn tiếp theo";
                nextBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/math_angle_game?level=${levelData.id + 1}`;
            } else {
                    nextBtn.style.display = 'inline-block';
                    nextBtn.innerText = 'Về trang bài học';
                    nextBtn.onclick = () => window.location.href = `${baseUrl}/views/lessons/math.php`;

                    modalMsg.innerText += " Bạn đã hoàn thành xuất sắc nhiệm vụ!";

                    try {
                        fetch(`${baseUrl}/views/lessons/update-angle-score`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ action: 'commit' })
                        })
                        .then(r => r.json())
                        .then(j => {
                            console.log('angle game commit', j);
                            if (j && j.success) {
                                const info = document.createElement('div');
                                info.className = 'server-info';
                                info.textContent = 'Điểm đã được lưu: 100% - XP +' + (j.xp_awarded || 20);
                                modalMsg.appendChild(info);
                            }
                        })
                        .catch(err => console.error('Error committing angle score', err));
                    } catch (e) {
                        console.error('Commit error', e);
                    }
            }
        } else {
            missFeedback.style.display = 'block';
            missFeedback.classList.remove('hidden');
            
            setTimeout(() => {
                missFeedback.classList.add('hidden');
                fireBtn.disabled = false;
                drawScene();
            }, 1500);
        }
    }
});