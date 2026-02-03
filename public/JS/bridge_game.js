// public/JS/bridge_game.js

// --- 1. CẤU HÌNH ĐƯỜNG DẪN ẢNH ---
const BASE_PATH = (typeof GAME_ASSETS_PATH !== 'undefined') 
                  ? GAME_ASSETS_PATH 
                  : '/SPNC_HocLieu_STEM_TieuHoc/public/images/bridge_game/';

const ASSETS = {
    fullCar: BASE_PATH + 'xe.png', 
    groundTexture: BASE_PATH + 'dat_hoan_chinh.png',
    signTexture: BASE_PATH + 'bien_go.png' 
};

// --- CẤU HÌNH MATTER.JS ---
const Engine = Matter.Engine, Render = Matter.Render, Runner = Matter.Runner,
      Bodies = Matter.Bodies, Composite = Matter.Composite, Constraint = Matter.Constraint,
      Mouse = Matter.Mouse, MouseConstraint = Matter.MouseConstraint, Events = Matter.Events,
      Body = Matter.Body, Vector = Matter.Vector;

const engine = Engine.create();
engine.gravity.scale = 0.002; 
const world = engine.world;

const gameContainer = document.getElementById('bridge-game-container');
const render = Render.create({
    element: gameContainer || document.body,
    engine: engine,
    options: { 
        width: gameContainer ? gameContainer.offsetWidth : window.innerWidth,
        height: gameContainer ? gameContainer.offsetHeight : window.innerHeight,
        wireframes: false,
        background: 'transparent'
    }
});

// --- DANH MỤC VA CHẠM ---
const CAT_DEFAULT = 0x0001; // Chuột
const CAT_CAR     = 0x0002; // Xe
const CAT_BRIDGE  = 0x0004; // Cầu
const CAT_GROUND  = 0x0008; // Đất

// --- CẤU HÌNH KÍCH THƯỚC ---
const bankWidth = 485;
const containerHeight = gameContainer ? gameContainer.offsetHeight : window.innerHeight;
const containerWidth = gameContainer ? gameContainer.offsetWidth : window.innerWidth;
const baseGroundY = containerHeight - 150;
const leftBankX = bankWidth; 
const rightBankX = containerWidth - bankWidth;
const defaultGapWidth = rightBankX - leftBankX;

// --- DỮ LIỆU LEVEL ---
const LEVELS_DATA = [
    {
        name: "Level 1: Xây Cầu Cơ Bản",
        rightBankOffset: 0, 
        bridgePieces: [ null ] 
    }
    /* // TẠM ẨN LEVEL 2,
    {
        name: "Level 2: Địa Hình Dốc",
        rightBankOffset: -120, 
        bridgePieces: [ 
            { length: 270, angle: -0.35 }, 
            { length: 270, angle: 0 }      
        ] 
    }
    */
];

// BIẾN QUẢN LÝ
let currentLevelIndex = 0; 
let car; 
let bridgeBars = []; 
let createdConstraints = []; 
let isPlaying = false, gameEnded = false;
let carOnBridge = false; // Theo dõi xe có đang trên cầu không

// --- HÀM TẢI LEVEL ---
function loadLevel(index) {
    // [SỬA 1] Dừng game và gỡ bỏ sự kiện 'beforeUpdate' cũ để không bị chạy chồng lặp
    isPlaying = false;
    gameEnded = false;
    Events.off(engine, 'beforeUpdate'); 

    // [SỬA 2] Chỉ xóa World, KHÔNG dùng Engine.clear(engine) vì dễ gây lỗi mất hình
    Composite.clear(world); 
    
    // Reset mảng quản lý
    bridgeBars = [];
    createdConstraints = [];
    
    if (index >= LEVELS_DATA.length) index = 0;
    currentLevelIndex = index;
    const levelData = LEVELS_DATA[index];

    // Reset UI
    const msg = document.getElementById('status-msg');
    const btnNext = document.getElementById('nextButton');
    const currentLevelDisplay = document.getElementById('currentLevel');
    const gameStatusDisplay = document.getElementById('gameStatus');
    
    if (msg) msg.style.display = 'none';
    if (btnNext) {
        btnNext.disabled = true;
    }
    if (currentLevelDisplay) currentLevelDisplay.textContent = (index + 1);
    if (gameStatusDisplay) gameStatusDisplay.textContent = 'Sẵn sàng';

    // Địa hình
    const leftY = baseGroundY;
    const rightY = baseGroundY + levelData.rightBankOffset;
    const anchorLeft = { x: leftBankX, y: leftY };
    const anchorRight = { x: rightBankX, y: rightY };

    // ... (Giữ nguyên code tạo Đất, Móc neo, Biển báo) ...
    // Copy lại đoạn tạo Đất từ code cũ của bạn vào đây
    const groundOpts = { isStatic: true, friction: 0.1, render: { sprite: { texture: ASSETS.groundTexture } }, collisionFilter: { category: CAT_GROUND, mask: CAT_CAR } };
    Composite.add(world, [
        Bodies.rectangle(bankWidth / 2, leftY + 200, bankWidth, 400, groundOpts),
        Bodies.rectangle(window.innerWidth - (bankWidth / 2), rightY + 200, bankWidth, 400, groundOpts),
        Bodies.circle(anchorLeft.x, anchorLeft.y, 8, { isStatic: true, render: { fillStyle: '#333' }, sensor: true }),
        Bodies.circle(anchorRight.x, anchorRight.y, 8, { isStatic: true, render: { fillStyle: '#333' }, sensor: true }),
        Bodies.rectangle(window.innerWidth - 80, rightY - 50, 100, 100, { isStatic: true, sensor: true, render: { sprite: { texture: ASSETS.signTexture } } })
    ]);


    // Tạo thanh cầu
    const supplyZoneX = window.innerWidth / 2;
    let supplyZoneY = 150; 

    levelData.bridgePieces.forEach((pieceData, i) => {
        let actualLength, initialAngle = 0;

        if (typeof pieceData === 'object' && pieceData !== null) {
            actualLength = pieceData.length;
            initialAngle = pieceData.angle || 0;
        } else {
            actualLength = pieceData || (defaultGapWidth + 40);
        }
        
        const bar = Bodies.rectangle(supplyZoneX, supplyZoneY + (i * 60), actualLength, 20, {
            isStatic: true,
            isSensor: true, 
            friction: 0.8, 
            density: 0.05,
            chamfer: { radius: 5 },
            render: { fillStyle: '#555', strokeStyle: '#000', lineWidth: 2 },
            label: 'bridgePiece',
            collisionFilter: { 
                category: CAT_BRIDGE, 
                mask: CAT_DEFAULT | CAT_CAR
            }
        });
        
        if (initialAngle !== 0) {
            Body.setAngle(bar, initialAngle);
        }
        
        bar.barLength = actualLength;
        bridgeBars.push(bar);
        supplyZoneY += 50; 
    });

    Composite.add(world, bridgeBars);
    createCar(bankWidth / 2, leftY - 50);
    setupMouseControl(anchorLeft, anchorRight, levelData);
}

function createCar(x, y) {
    const carWidth = 220;
    const carHeight = 90;
    
    car = Bodies.rectangle(x, y, carWidth, carHeight, {
        label: 'CarWhole', 
        density: 0.005,      
        chamfer: { radius: 45 }, 
        friction: 0,       
        frictionStatic: 0, 
        frictionAir: 0.02,     
        restitution: 0,        
        render: { sprite: { texture: ASSETS.fullCar } },
        
        // Xe va chạm với TẤT CẢ (Đất, Cầu)
        collisionFilter: { 
            category: CAT_CAR, 
            mask: CAT_GROUND | CAT_BRIDGE 
        }
    });
    Composite.add(world, car);
}

function getBarEnds(body) {
    const len = body.barLength;
    const angle = body.angle;
    const center = body.position;
    const dx = (len / 2) * Math.cos(angle);
    const dy = (len / 2) * Math.sin(angle);
    return {
        leftEnd: { x: center.x - dx, y: center.y - dy },
        rightEnd: { x: center.x + dx, y: center.y + dy }
    };
}

function setupMouseControl(anchorLeft, anchorRight, levelData) {
    const mouse = Mouse.create(render.canvas);
    const mouseConstraint = MouseConstraint.create(engine, {
        mouse: mouse,
        constraint: { stiffness: 0.2, render: { visible: false } },
        collisionFilter: { mask: CAT_BRIDGE }
    });

    Events.on(mouseConstraint, 'startdrag', function(event) {
        if (isPlaying) return;
        const body = event.body;
        if (bridgeBars.includes(body)) {
            Body.setStatic(body, false);
            Body.setInertia(body, Infinity);
            body.render.fillStyle = '#7f8c8d'; 
            removeConstraintsAttachedTo(body);
        }
    });

    Events.on(mouseConstraint, 'enddrag', function(event) {
        if (isPlaying) return;
        const body = event.body;
        if (bridgeBars.includes(body)) {
            Body.setStatic(body, true);
            Body.setAngle(body, body.angle);
            body.render.fillStyle = '#555'; 
            checkAndAttach(body, anchorLeft, anchorRight, levelData);
        }
    });

    Composite.add(world, mouseConstraint);
    render.mouse = mouse;
}

function removeConstraintsAttachedTo(body) {
    createdConstraints = createdConstraints.filter(c => {
        if (c.bodyB === body) {
            Composite.remove(world, c);
            return false;
        }
        return true;
    });
}

function checkAndAttach(body, anchorLeft, anchorRight, levelData) {
    const snapDist = 60; 

    // Level 1 (Giữ nguyên logic cũ)
    if (levelData.bridgePieces.length === 1 && levelData.bridgePieces[0] === null) {
        const midGapX = (leftBankX + rightBankX) / 2;
        const avgY = (anchorLeft.y + anchorRight.y) / 2;
        const dist = Vector.magnitude(Vector.sub(body.position, {x: midGapX, y: avgY}));

        if (dist < 100) {
            Body.setPosition(body, { x: midGapX, y: avgY });
            Body.setAngle(body, 0);
            snapToAnchor(body, { x: -body.barLength/2, y: 0 }, anchorLeft);
            snapToAnchor(body, { x: body.barLength/2, y: 0 }, anchorRight);
            body.render.fillStyle = '#27ae60';
        }
    } 
    // Level 2 trở đi (SỬA PHẦN NÀY)
    else {
        const ends = getBarEnds(body);
        let attached = false;

        // --- HÀM HỖ TRỢ MỚI ---
        // Hàm này giúp dịch chuyển ngay lập tức thanh thép để đầu của nó 
        // trùng khít với điểm neo trước khi tạo khớp.
        const snapBodyPositionToAnchor = (currentEndPos, anchorPos) => {
            const dx = anchorPos.x - currentEndPos.x;
            const dy = anchorPos.y - currentEndPos.y;
            // Dịch chuyển vật thể
            Body.translate(body, { x: dx, y: dy });
        };
        // ----------------------

        // Kiểm tra đầu TRÁI của thanh thép
        if (Vector.magnitude(Vector.sub(ends.leftEnd, anchorLeft)) < snapDist) {
            // 1. Dịch chuyển thanh thép vào đúng vị trí neo
            snapBodyPositionToAnchor(ends.leftEnd, anchorLeft);
            // 2. Tạo khớp nối (lúc này khoảng cách đã là 0 nên sẽ không bị giật)
            snapToAnchor(body, { x: -body.barLength/2, y: 0 }, anchorLeft);
            attached = true;
        } else if (Vector.magnitude(Vector.sub(ends.leftEnd, anchorRight)) < snapDist) {
            snapBodyPositionToAnchor(ends.leftEnd, anchorRight);
            snapToAnchor(body, { x: -body.barLength/2, y: 0 }, anchorRight);
            attached = true;
        }

        // [QUAN TRỌNG] Cập nhật lại toạ độ các đầu vì thanh thép có thể đã bị dịch chuyển ở bước trên
        const newEnds = getBarEnds(body);

        // Kiểm tra đầu PHẢI của thanh thép
        if (Vector.magnitude(Vector.sub(newEnds.rightEnd, anchorLeft)) < snapDist) {
            // Chỉ cho phép dịch chuyển nếu chưa được gắn vào đâu để tránh xung đột
            if (!attached) { snapBodyPositionToAnchor(newEnds.rightEnd, anchorLeft); }
            snapToAnchor(body, { x: body.barLength/2, y: 0 }, anchorLeft);
            attached = true;
        } else if (Vector.magnitude(Vector.sub(newEnds.rightEnd, anchorRight)) < snapDist) {
            if (!attached) { snapBodyPositionToAnchor(newEnds.rightEnd, anchorRight); }
            snapToAnchor(body, { x: body.barLength/2, y: 0 }, anchorRight);
            attached = true;
        }

        // Đổi màu nếu đã được gắn thành công
        if (attached) {
            body.render.fillStyle = '#27ae60';
        }
    }
}

function snapToAnchor(body, bodyPoint, anchorPoint) {
    const c = Constraint.create({
        pointA: anchorPoint, 
        bodyB: body, 
        pointB: bodyPoint,
        stiffness: 1,      // [SỬA] Đặt stiffness = 1 (cứng tuyệt đối)
        length: 0, 
        damping: 0.1,      // Giữ damping để giảm rung lắc
        render: { visible: true, lineWidth: 5, strokeStyle: '#e74c3c' }
    });
    Composite.add(world, c);
    createdConstraints.push(c);
}

function startGame() {
    if (isPlaying || gameEnded) return;
    isPlaying = true;

    bridgeBars.forEach(bar => {
        // [QUAN TRỌNG] Dùng Body.set để update an toàn
        Body.set(bar, { 
            isSensor: false, 
            isStatic: false 
        });
        // [SỬA] Giảm ma sát khí để vật rơi tự nhiên hơn, tránh bị "bồng bềnh"
        bar.frictionAir = 0.05; 
        
        // [SỬA] Tăng khối lượng để thanh cầu vững hơn khi xe đi qua
        Body.setDensity(bar, 0.1);
    });

    Events.on(engine, 'beforeUpdate', function gameLoop() {
        if (!gameEnded && isPlaying) {
            // Di chuyển xe
            if (car.speed < 10) {
                Body.applyForce(car, car.position, { x: 0.06, y: 0 });
            }
            
            // Xử lý xe đi trên thanh thép
            handleCarOnBridge();
            checkWinLoseCondition();
        }
    });
}

function handleCarOnBridge() {
    if (!car || gameEnded) return;
    
    // Tính toán vị trí dưới cùng của xe
    const carBottom = car.position.y + 45; // 45 là nửa chiều cao xe
    const carLeft = car.position.x - 110; // 110 là nửa chiều rộng xe
    const carRight = car.position.x + 110;
    
    let highestBridgeTop = null;
    carOnBridge = false;
    
    // Duyệt qua tất cả các thanh thép
    bridgeBars.forEach(bar => {
        if (bar.isStatic) return; // Bỏ qua thanh chưa được thả xuống
        
        // Lấy tọa độ 4 góc của thanh thép (xoay theo góc)
        const angle = bar.angle;
        const halfWidth = bar.barLength / 2;
        const halfHeight = 10; // Nửa chiều cao thanh thép (20/2)
        
        // Tính toán các góc của thanh thép
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        
        // 4 góc của hình chữ nhật xoay
        const corners = [
            { // Góc trên trái
                x: bar.position.x + (-halfWidth * cos - (-halfHeight) * sin),
                y: bar.position.y + (-halfWidth * sin + (-halfHeight) * cos)
            },
            { // Góc trên phải
                x: bar.position.x + (halfWidth * cos - (-halfHeight) * sin),
                y: bar.position.y + (halfWidth * sin + (-halfHeight) * cos)
            },
            { // Góc dưới trái
                x: bar.position.x + (-halfWidth * cos - halfHeight * sin),
                y: bar.position.y + (-halfWidth * sin + halfHeight * cos)
            },
            { // Góc dưới phải
                x: bar.position.x + (halfWidth * cos - halfHeight * sin),
                y: bar.position.y + (halfWidth * sin + halfHeight * cos)
            }
        ];
        
        // Tìm Y cao nhất (mặt trên) và thấp nhất của thanh thép
        const topY = Math.min(corners[0].y, corners[1].y);
        const bottomY = Math.max(corners[2].y, corners[3].y);
        
        // Tìm X trái nhất và phải nhất
        const leftX = Math.min(corners[0].x, corners[1].x, corners[2].x, corners[3].x);
        const rightX = Math.max(corners[0].x, corners[1].x, corners[2].x, corners[3].x);
        
        // Kiểm tra xe có nằm trên thanh thép theo trục X không
        const carOverlapsX = carRight > leftX && carLeft < rightX;
        
        if (carOverlapsX) {
            // Kiểm tra xe có gần mặt trên của thanh thép không
            const distanceToTop = carBottom - topY;
            
            // Nếu xe đang ở phía trên và gần mặt trên của thanh (trong khoảng 30 pixel)
            if (distanceToTop > -30 && distanceToTop < 30) {
                if (highestBridgeTop === null || topY < highestBridgeTop) {
                    highestBridgeTop = topY;
                    carOnBridge = true;
                }
            }
        }
    });
    
    // Nếu xe đang trên cầu, giữ xe ở mặt trên
    if (carOnBridge && highestBridgeTop !== null) {
        const targetY = highestBridgeTop - 45; // 45 là nửa chiều cao xe
        const currentY = car.position.y;
        
        // Nếu xe đang chìm xuống dưới mặt cầu, đẩy xe lên
        if (currentY > targetY) {
            Body.setPosition(car, { x: car.position.x, y: targetY });
            // Đặt vận tốc Y về 0 để xe không rơi xuống
            Body.setVelocity(car, { x: car.velocity.x, y: 0 });
        }
    }
}

function resetGame() { 
    loadLevel(currentLevelIndex); 
}

function nextLevel() {
    currentLevelIndex++; 
    if (currentLevelIndex >= LEVELS_DATA.length) {
        alert("Chúc mừng! Bạn đã hoàn thành tất cả màn chơi!");
        currentLevelIndex = 0; 
    }
    loadLevel(currentLevelIndex);
}

function checkWinLoseCondition() {
    const msg = document.getElementById('status-msg');
    const btnNext = document.getElementById('nextButton');
    const gameStatusDisplay = document.getElementById('gameStatus');
    
    if (car.position.y > containerHeight + 200) {
        gameEnded = true; 
        msg.innerText = "❌ RƠI RỒI! THỬ LẠI NHÉ"; 
        msg.style.color = "red"; 
        msg.style.display = "block";
        if (gameStatusDisplay) gameStatusDisplay.textContent = 'Thất bại';
        isPlaying = false;
    }
    
    // Điều kiện thắng
    if (car.position.x > containerWidth - 300) {
        gameEnded = true; 
        msg.innerText = "🏆 TUYỆT VỜI! QUA MÀN!"; 
        msg.style.color = "#2ecc71"; 
        msg.style.display = "block";
        if (gameStatusDisplay) gameStatusDisplay.textContent = 'Hoàn thành';
        if (btnNext) {
            btnNext.disabled = false;
        }
        isPlaying = false;
    }
}

// --- EVENT LISTENERS FOR NEW UI ---
document.addEventListener('DOMContentLoaded', () => {
    // Modal close
    const startGameButton = document.getElementById('startGameButton');
    const introModal = document.getElementById('intro-modal');
    if (startGameButton && introModal) {
        startGameButton.addEventListener('click', () => {
            introModal.classList.remove('active');
        });
    }

    // Button event listeners
    const replayButton = document.getElementById('replayButton');
    const playButton = document.getElementById('playButton');
    const nextButton = document.getElementById('nextButton');

    if (replayButton) {
        replayButton.addEventListener('click', resetGame);
    }
    if (playButton) {
        playButton.addEventListener('click', startGame);
    }
    if (nextButton) {
        nextButton.addEventListener('click', nextLevel);
    }
});

loadLevel(currentLevelIndex);
Render.run(render);
Runner.run(Runner.create(), engine);