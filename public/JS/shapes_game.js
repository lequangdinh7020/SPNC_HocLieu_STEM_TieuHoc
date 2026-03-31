document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('shapeCanvas');
    const ctx = canvas.getContext('2d');
    const currentChallengeElement = document.getElementById('currentChallenge');
    const shapeIcon = document.getElementById('shapeIcon');
    const challengeTitle = document.getElementById('challengeTitle');
    const challengeDesc = document.getElementById('challengeDesc');
    const questionText = document.getElementById('questionText');
    const currentShapeName = document.getElementById('currentShapeName');
    const targetShapeName = document.getElementById('targetShapeName');
    const feedbackText = document.getElementById('feedbackText');
    
    let currentChallengeIndex = 0;
    let score = 0;
    let completedChallenges = 0;
    let points = [];
    let currentChallenge = null;
    
    const shapeIcons = {
        'square': '□',
        'rectangle': '▭',
        'triangle': '△',
        'trapezoid': '⏢',
        'parallelogram': '▱',
        'rhombus': '◇'
    };
    
    const shapeNames = {
        'square': 'Hình vuông',
        'rectangle': 'Hình chữ nhật',
        'triangle': 'Tam giác',
        'trapezoid': 'Hình thang',
        'parallelogram': 'Bình hành',
        'rhombus': 'Hình thoi'
    };
    
    function initGame() {
        loadChallenge(currentChallengeIndex);
        initDraggablePoints();
        
        document.getElementById('checkBtn').addEventListener('click', checkSolution);
        document.getElementById('resetBtn').addEventListener('click', resetPoints);
    }
    
    function loadChallenge(index) {
        if (!window.gameData || !window.gameData.challenges[index]) return;
        
        currentChallengeIndex = index;
        currentChallenge = window.gameData.challenges[index];
        
        currentChallengeElement.textContent = currentChallenge.id;
        shapeIcon.textContent = shapeIcons[currentChallenge.targetShape] || '📐';
        challengeTitle.textContent = currentChallenge.title;
        challengeDesc.textContent = currentChallenge.description;
        questionText.textContent = currentChallenge.question;
        currentShapeName.textContent = shapeNames[currentChallenge.startingShape];
        targetShapeName.textContent = shapeNames[currentChallenge.targetShape];
        
        points = JSON.parse(JSON.stringify(currentChallenge.startingPoints));
        
        updateCanvas();
        updateDraggablePoints();
        
        showFeedback('Kéo các điểm để tạo hình!');
    }
    
    function updateCanvas() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        drawGrid();
        
        drawShape();
        
        drawPoints();
    }
    
    function drawGrid() {
        ctx.strokeStyle = '#f0f0f0';
        ctx.lineWidth = 1;
        
        const cellWidth = canvas.width / 8;
        const cellHeight = canvas.height / 6;
        
        for (let y = 0; y <= canvas.height; y += cellHeight) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(canvas.width, y);
            ctx.stroke();
        }
        
        for (let x = 0; x <= canvas.width; x += cellWidth) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, canvas.height);
            ctx.stroke();
        }
    }
    
    function drawShape() {
        if (points.length < 2) return;
        
        const uniquePoints = getUniquePoints(10);
        if (uniquePoints.length < 2) return;
        
        ctx.fillStyle = 'rgba(106, 158, 245, 0.1)';
        ctx.strokeStyle = '#6a9ef5';
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        
        ctx.beginPath();
        ctx.moveTo(uniquePoints[0][0], uniquePoints[0][1]);
        
        for (let i = 1; i < uniquePoints.length; i++) {
            ctx.lineTo(uniquePoints[i][0], uniquePoints[i][1]);
        }
        
        if (uniquePoints.length >= 3 && currentChallenge.targetShape !== 'triangle') {
            ctx.closePath();
        }
        
        ctx.fill();
        ctx.stroke();
    }
    
    function drawPoints() {
        for (let i = 0; i < points.length; i++) {
            let isUnique = true;
            for (let j = 0; j < i; j++) {
                if (distance(points[i], points[j]) < 10) {
                    isUnique = false;
                    break;
                }
            }
            
            if (!isUnique) continue;
            
            ctx.fillStyle = '#6a9ef5';
            ctx.beginPath();
            ctx.arc(points[i][0], points[i][1], 6, 0, Math.PI * 2);
            ctx.fill();
            
            ctx.fillStyle = '#4a7bd4';
            ctx.font = 'bold 14px Arial';
            ctx.fillText(String.fromCharCode(65 + i), points[i][0] + 10, points[i][1] - 10);
        }
    }
    
    function initDraggablePoints() {
        ['A', 'B', 'C', 'D'].forEach(point => {
            const element = document.getElementById(`point${point}`);
            if (element) {
                element.addEventListener('mousedown', startDrag);
                element.addEventListener('touchstart', startDrag);
            }
        });
        
        document.addEventListener('mousemove', drag);
        document.addEventListener('touchmove', drag);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchend', stopDrag);
    }
    
    function updateDraggablePoints() {
        ['A', 'B', 'C', 'D'].forEach((point, index) => {
            const element = document.getElementById(`point${point}`);
            if (element && index < points.length) {
                const offset = 15;
                element.style.left = (points[index][0] - offset) + 'px';
                element.style.top = (points[index][1] - offset) + 'px';
                element.style.display = 'block';
            } else if (element) {
                element.style.display = 'none';
            }
        });
    }
    
    let isDragging = false;
    let draggedPoint = null;
    
    function startDrag(e) {
        e.preventDefault();
        isDragging = true;
        draggedPoint = this.dataset.point;
        
        this.style.cursor = 'grabbing';
        this.style.transform = 'scale(1.1)';
    }
    
    function drag(e) {
        if (!isDragging || !draggedPoint) return;
        
        e.preventDefault();
        
        const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
        const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
        const canvasRect = canvas.getBoundingClientRect();
        
        let x = clientX - canvasRect.left;
        let y = clientY - canvasRect.top;
        
        const offset = 15;
        x = Math.max(offset, Math.min(canvas.width - offset, x));
        y = Math.max(offset, Math.min(canvas.height - offset, y));
        
        const pointIndex = draggedPoint.charCodeAt(0) - 65;
        if (pointIndex < points.length) {
            points[pointIndex][0] = x;
            points[pointIndex][1] = y;
            
            const pointElement = document.getElementById(`point${draggedPoint}`);
            if (pointElement) {
                pointElement.style.left = (x - offset) + 'px';
                pointElement.style.top = (y - offset) + 'px';
            }
            
            updateCanvas();
        }
    }
    
    function stopDrag() {
        if (draggedPoint) {
            const pointElement = document.getElementById(`point${draggedPoint}`);
            if (pointElement) {
                pointElement.style.cursor = 'grab';
                pointElement.style.transform = '';
            }
        }
        
        isDragging = false;
        draggedPoint = null;
    }
    
    function checkSolution() {
        const targetShape = currentChallenge.targetShape;
        let isValid = false;
        let message = '';
        
        const currentShapeType = detectCurrentShape();
        currentShapeName.textContent = shapeNames[currentShapeType] || 'Không xác định';
        
        switch(targetShape) {
            case 'rectangle':
                isValid = checkRectangle();
                message = isValid ? 
                    '✓ Đúng rồi! Đây là hình chữ nhật!' :
                    '✗ Chưa đúng. Thử lại nhé!';
                break;
                
            case 'triangle':
                isValid = checkTriangle();
                message = isValid ?
                    '✓ Đúng rồi! Đây là tam giác!' :
                    '✗ Chưa đúng. Thử lại nhé!';
                break;
                
            case 'trapezoid':
                isValid = checkTrapezoid();
                message = isValid ?
                    '✓ Đúng rồi! Đây là hình thang!' :
                    '✗ Chưa đúng. Thử lại nhé!';
                break;
                
            case 'parallelogram':
                isValid = checkParallelogram();
                message = isValid ?
                    '✓ Đúng rồi! Đây là hình bình hành!' :
                    '✗ Chưa đúng. Thử lại nhé!';
                break;
                
            case 'rhombus':
                isValid = checkRhombus();
                message = isValid ?
                    '✓ Đúng rồi! Đây là hình thoi!' :
                    '✗ Chưa đúng. Thử lại nhé!';
                break;
                
            case 'square':
                isValid = checkSquare();
                message = isValid ?
                    '✓ Đúng rồi! Đây là hình vuông!' :
                    '✗ Chưa đúng. Thử lại nhé!';
                break;
        }
        
        if (isValid) {
            score += 100;
            showFeedback(message);
            
            updateProgress(currentChallenge.targetShape);
            
            if (!currentChallenge.completed) {
                currentChallenge.completed = true;
                completedChallenges++;
                
                if (completedChallenges >= (window.gameData.challenges ? window.gameData.challenges.length : 6)) {
                    commitShapesScore();
                }
            }
            
            setTimeout(() => {
                if (currentChallengeIndex < window.gameData.challenges.length - 1) {
                    currentChallengeIndex++;
                    loadChallenge(currentChallengeIndex);
                    showFeedback('Thử thách mới!');
                } else {
                    showFeedback('🎉 Hoàn thành tất cả thử thách!');
                }
            }, 1500);
        } else {
            showFeedback(message);
        }
    }
    
    function detectCurrentShape() {
        const uniquePoints = getUniquePoints(10);
        const numPoints = uniquePoints.length;
        
        if (numPoints === 3) return 'triangle';
        if (numPoints !== 4) return 'unknown';
        
        if (checkSquare()) return 'square';
        if (checkRectangle()) return 'rectangle';
        if (checkRhombus()) return 'rhombus';
        if (checkParallelogram()) return 'parallelogram';
        if (checkTrapezoid()) return 'trapezoid';
        
        return 'quadrilateral';
    }
    
    function checkRectangle() {
        const uniquePoints = getUniquePoints(10);
        if (uniquePoints.length !== 4) return false;
        
        const angles = calculateAngles(uniquePoints);
        for (let angle of angles) {
            if (Math.abs(angle - 90) > 15) return false;
        }
        
        const sides = calculateSideLengths(uniquePoints);
        const oppositeSidesEqual = 
            Math.abs(sides[0] - sides[2]) < 30 && 
            Math.abs(sides[1] - sides[3]) < 30;
        
        return oppositeSidesEqual;
    }
    
    function checkTriangle() {
        const uniquePoints = getUniquePoints(10);
        return uniquePoints.length === 3;
    }
    
    function checkTrapezoid() {
        const uniquePoints = getUniquePoints(10);
        if (uniquePoints.length !== 4) return false;
        
        const hasParallelSides = checkParallelSides(uniquePoints);
        return hasParallelSides;
    }
    
    function checkParallelogram() {
        const uniquePoints = getUniquePoints(10);
        if (uniquePoints.length !== 4) return false;
        
        const isParallel = checkParallelSides(uniquePoints);
        if (!isParallel) return false;
        
        const sides = calculateSideLengths(uniquePoints);
        const oppositeSidesEqual = 
            Math.abs(sides[0] - sides[2]) < 30 && 
            Math.abs(sides[1] - sides[3]) < 30;
        
        return oppositeSidesEqual;
    }
    
    function checkRhombus() {
        const uniquePoints = getUniquePoints(10);
        if (uniquePoints.length !== 4) return false;
        
        const sides = calculateSideLengths(uniquePoints);
        const allSidesEqual = sides.every(side => 
            Math.abs(side - sides[0]) < 30
        );
        
        return allSidesEqual;
    }
    
    function checkSquare() {
        const uniquePoints = getUniquePoints(10);
        if (uniquePoints.length !== 4) return false;
        
        const sides = calculateSideLengths(uniquePoints);
        const allSidesEqual = sides.every(side => 
            Math.abs(side - sides[0]) < 20
        );
        if (!allSidesEqual) return false;
        
        const angles = calculateAngles(uniquePoints);
        const allRightAngles = angles.every(angle => 
            Math.abs(angle - 90) < 10
        );
        
        return allRightAngles;
    }
    
    function distance(p1, p2) {
        const dx = p2[0] - p1[0];
        const dy = p2[1] - p1[1];
        return Math.sqrt(dx*dx + dy*dy);
    }
    
    function getUniquePoints(minDistance = 10) {
        const unique = [];
        
        for (let point of points) {
            let isUnique = true;
            for (let u of unique) {
                if (distance(point, u) < minDistance) {
                    isUnique = false;
                    break;
                }
            }
            if (isUnique) {
                unique.push(point);
            }
        }
        
        return unique;
    }
    
    function calculateSideLengths(pointsArray = points) {
        const uniquePoints = pointsArray.length === 4 ? pointsArray : getUniquePoints(10);
        if (uniquePoints.length < 2) return [];
        
        const lengths = [];
        const n = uniquePoints.length;
        
        for (let i = 0; i < n; i++) {
            const next = (i + 1) % n;
            lengths.push(distance(uniquePoints[i], uniquePoints[next]));
        }
        
        return lengths;
    }
    
    function calculateAngles(pointsArray = points) {
        const uniquePoints = pointsArray.length === 4 ? pointsArray : getUniquePoints(10);
        if (uniquePoints.length < 3) return [];
        
        const angles = [];
        const n = uniquePoints.length;
        
        for (let i = 0; i < n; i++) {
            const prev = (i - 1 + n) % n;
            const curr = i;
            const next = (i + 1) % n;
            
            const v1 = [uniquePoints[prev][0] - uniquePoints[curr][0], uniquePoints[prev][1] - uniquePoints[curr][1]];
            const v2 = [uniquePoints[next][0] - uniquePoints[curr][0], uniquePoints[next][1] - uniquePoints[curr][1]];
            
            const dot = v1[0]*v2[0] + v1[1]*v2[1];
            const mag1 = Math.sqrt(v1[0]*v1[0] + v1[1]*v1[1]);
            const mag2 = Math.sqrt(v2[0]*v2[0] + v2[1]*v2[1]);
            
            if (mag1 === 0 || mag2 === 0) {
                angles.push(90);
                continue;
            }
            
            let angle = Math.acos(dot / (mag1 * mag2)) * (180 / Math.PI);
            if (isNaN(angle)) angle = 90;
            
            angles.push(angle);
        }
        
        return angles;
    }
    
    function checkParallelSides(pointsArray = points) {
        const uniquePoints = pointsArray.length === 4 ? pointsArray : getUniquePoints(10);
        if (uniquePoints.length !== 4) return false;
        
        const vectors = [];
        for (let i = 0; i < 4; i++) {
            const next = (i + 1) % 4;
            vectors.push([
                uniquePoints[next][0] - uniquePoints[i][0],
                uniquePoints[next][1] - uniquePoints[i][1]
            ]);
        }
        
        for (let i = 0; i < 2; i++) {
            const j = i + 2;
            const v1 = vectors[i];
            const v2 = vectors[j];
            
            const crossProduct = Math.abs(v1[0] * v2[1] - v1[1] * v2[0]);
            const tolerance = 100;
            
            if (crossProduct < tolerance) {
                return true;
            }
        }
        
        return false;
    }
    
    function resetPoints() {
        points = JSON.parse(JSON.stringify(currentChallenge.startingPoints));
        updateCanvas();
        updateDraggablePoints();
        currentShapeName.textContent = shapeNames[currentChallenge.startingShape];
        showFeedback('Đã làm lại! Thử tiếp nhé!');
    }
    
    function commitShapesScore() {
        if (window._shapesCommitInProgress) return;
        window._shapesCommitInProgress = true;

        const totalChallenges = window.gameData.challenges ? window.gameData.challenges.length : 6;
        const maxPoints = totalChallenges * 100;
        const pct = maxPoints > 0 ? Math.max(0, Math.min(100, Math.round((score / maxPoints) * 100))) : 0;

        fetch(window.baseUrl + '/views/lessons/update-shapes-score', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'commit', score_pct: pct, total_challenges: totalChallenges })
        }).then(r => r.json()).then(res => {
            if (res && res.success) {
                console.log('Điểm đã lưu: ' + pct + '%');
            }
        }).catch(err => {
            console.error('Lỗi lưu điểm:', err);
        }).finally(() => {
            setTimeout(() => { window._shapesCommitInProgress = false; }, 2000);
        });
    }
    
    function updateProgress(shape) {
        const progressItem = document.getElementById(`progress${shape.charAt(0).toUpperCase() + shape.slice(1)}`);
        if (progressItem) {
            progressItem.classList.add('completed');
        }
    }
    
    function showFeedback(text) {
        feedbackText.textContent = text;
        
        const feedbackMessage = document.querySelector('.feedback-message');
        feedbackMessage.classList.remove('success-animation');
        void feedbackMessage.offsetWidth;
        feedbackMessage.classList.add('success-animation');
        
        setTimeout(() => {
            feedbackMessage.classList.remove('success-animation');
        }, 500);
    }
    
    initGame();
});