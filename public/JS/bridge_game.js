const BASE_PATH = (typeof GAME_ASSETS_PATH !== 'undefined') 
                  ? GAME_ASSETS_PATH 
                  : '/SPNC_HocLieu_STEM_TieuHoc/public/images/bridge_game/';

const ASSETS = {
    fullCar: BASE_PATH + 'xe.png', 
    groundTexture: BASE_PATH + 'dat_hoan_chinh.png',
    signTexture: BASE_PATH + 'chuc_nu.png' 
};

document.addEventListener('DOMContentLoaded', () => {
    const introModal = document.getElementById('intro-modal');
    const nextStoryButton = document.getElementById('nextStoryButton');
    const storyText = document.getElementById('storyText');
    const storyDialogues = [
        "Chào các bạn nhỏ! Chắc hẳn chúng mình đã từng nghe câu chuyện về chàng Ngưu Lang và nàng tiên Chức Nữ rồi đúng không? Hai người bị chia cắt bởi dòng sông Ngân Hà rộng mênh mông và chỉ được gặp nhau mỗi năm đúng một lần.",
        "Hôm nay đã đến ngày hẹn, nhưng đàn chim Ô Thước mãi chẳng thấy đâu. Để không lỡ mất cơ hội các bạn nhỏ hãy giúp Ngưu Lang Chức Nữ xây cầu để họ gặp nhau!",
        "Thời gian trăng lên sắp hết rồi, chúng mình cùng nhanh tay thôi nào! 3... 2... 1... Bắt đầu xây cầu thôi!"
    ];
    let currentStoryIndex = 0;

    if (introModal && nextStoryButton && storyText) {
        nextStoryButton.addEventListener('click', () => {
            currentStoryIndex++;

            if (currentStoryIndex < storyDialogues.length) {
                storyText.textContent = storyDialogues[currentStoryIndex];

                if (currentStoryIndex === storyDialogues.length - 1) {
                    nextStoryButton.innerHTML = '<i class="fas fa-play"></i> Bắt đầu xây cầu thôi!';
                }

                return;
            }

            introModal.classList.remove('active');
        });
    }
});

if (typeof Matter === 'undefined') {
    console.error('Matter.js is not loaded. Please check CDN/network/script order.');
    const statusMsg = document.getElementById('status-msg');
    if (statusMsg) {
        statusMsg.textContent = 'Khong tai duoc thu vien vat ly Matter.js. Vui long tai lai trang.';
        statusMsg.style.display = 'block';
        statusMsg.style.color = 'red';
    }
    throw new Error('Matter.js is not loaded');
}

const Engine = Matter.Engine, Render = Matter.Render, Runner = Matter.Runner,
      Bodies = Matter.Bodies, Composite = Matter.Composite, Constraint = Matter.Constraint,
      Mouse = Matter.Mouse, MouseConstraint = Matter.MouseConstraint, Events = Matter.Events,
    MatterBody = Matter.Body, Vector = Matter.Vector;

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

const CAT_DEFAULT = 0x0001;
const CAT_CAR     = 0x0002; 
const CAT_BRIDGE  = 0x0004;
const CAT_GROUND  = 0x0008;

const bankWidth = 485;
const containerHeight = gameContainer ? gameContainer.offsetHeight : window.innerHeight;
const containerWidth = gameContainer ? gameContainer.offsetWidth : window.innerWidth;
const baseGroundY = containerHeight - 120;
const leftBankX = bankWidth; 
const rightBankX = containerWidth - bankWidth + 10;
const defaultGapWidth = rightBankX - leftBankX;
const CAR_WIDTH = 165;
const CAR_HEIGHT = 75;

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

let currentLevelIndex = 0; 
let car; 
let bridgeBars = []; 
let createdConstraints = []; 
let isPlaying = false, gameEnded = false;
let carOnBridge = false;


function loadLevel(index) {
    isPlaying = false;
    gameEnded = false;
    Events.off(engine, 'beforeUpdate'); 

    Composite.clear(world); 
    
    bridgeBars = [];
    createdConstraints = [];
    
    if (index >= LEVELS_DATA.length) index = 0;
    currentLevelIndex = index;
    const levelData = LEVELS_DATA[index];

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

    const leftY = baseGroundY;
    const rightY = baseGroundY + levelData.rightBankOffset;
    const anchorLeft = { x: leftBankX, y: leftY };
    const anchorRight = { x: rightBankX, y: rightY };

    const groundVisualOpts = {
        isStatic: true,
        friction: 0.1,
        render: { sprite: { texture: ASSETS.groundTexture } },
        collisionFilter: { category: CAT_GROUND, mask: CAT_DEFAULT }
    };
    const groundColliderOpts = {
        isStatic: true,
        render: { visible: false },
        collisionFilter: { category: CAT_GROUND, mask: CAT_CAR }
    };
    Composite.add(world, [
        Bodies.rectangle(bankWidth / 2, leftY + 200, bankWidth, 400, groundVisualOpts),
        Bodies.rectangle(containerWidth - (bankWidth / 2), rightY + 200, bankWidth, 400, groundVisualOpts),
        Bodies.rectangle(bankWidth / 2, leftY - 40, bankWidth, 20, groundColliderOpts),
        Bodies.rectangle(containerWidth - (bankWidth / 2), rightY - 40, bankWidth, 20, groundColliderOpts),
        Bodies.circle(anchorLeft.x, anchorLeft.y, 8, { isStatic: true, render: { fillStyle: '#333' }, sensor: true }),
        Bodies.circle(anchorRight.x, anchorRight.y, 8, { isStatic: true, render: { fillStyle: '#333' }, sensor: true }),
        Bodies.rectangle(containerWidth - 85, rightY - 120, 110, 110, { isStatic: true, sensor: true, render: { sprite: { texture: ASSETS.signTexture, xScale: 0.28, yScale: 0.28 } } })
    ]);


    const supplyZoneX = containerWidth / 2;
    let supplyZoneY = 95; 

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
            MatterBody.setAngle(bar, initialAngle);
        }
        
        bar.barLength = actualLength;
        bridgeBars.push(bar);
        supplyZoneY += 40; 
    });

    Composite.add(world, bridgeBars);
    createCar(bankWidth / 2, leftY - 110);
    setupMouseControl(anchorLeft, anchorRight, levelData);
}
function createCar(x, y) {
    car = Bodies.rectangle(x, y, CAR_WIDTH, CAR_HEIGHT, {
        label: 'CarWhole', 
        isStatic: true,
        density: 0.005,      
        chamfer: { radius: 30 }, 
        friction: 0,       
        frictionStatic: 0, 
        frictionAir: 0.02,     
        restitution: 0,        
        render: { sprite: { texture: ASSETS.fullCar, xScale: 0.22, yScale: 0.22 } },
        
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
            MatterBody.setStatic(body, false);
            MatterBody.setInertia(body, Infinity);
            body.render.fillStyle = '#7f8c8d'; 
            removeConstraintsAttachedTo(body);
        }
    });

    Events.on(mouseConstraint, 'enddrag', function(event) {
        if (isPlaying) return;
        const body = event.body;
        if (bridgeBars.includes(body)) {
            MatterBody.setStatic(body, true);
            MatterBody.setAngle(body, body.angle);
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

    if (levelData.bridgePieces.length === 1 && levelData.bridgePieces[0] === null) {
        const midGapX = (leftBankX + rightBankX) / 2;
        const avgY = (anchorLeft.y + anchorRight.y) / 2;
        const dist = Vector.magnitude(Vector.sub(body.position, {x: midGapX, y: avgY}));

        if (dist < 100) {
            MatterBody.setPosition(body, { x: midGapX, y: avgY });
            MatterBody.setAngle(body, 0);
            snapToAnchor(body, { x: -body.barLength/2, y: 0 }, anchorLeft);
            snapToAnchor(body, { x: body.barLength/2, y: 0 }, anchorRight);
            body.render.fillStyle = '#27ae60';
        }
    } 
    else {
        const ends = getBarEnds(body);
        let attached = false;

        const snapBodyPositionToAnchor = (currentEndPos, anchorPos) => {
            const dx = anchorPos.x - currentEndPos.x;
            const dy = anchorPos.y - currentEndPos.y;
            MatterBody.translate(body, { x: dx, y: dy });
        };
        if (Vector.magnitude(Vector.sub(ends.leftEnd, anchorLeft)) < snapDist) {
            snapBodyPositionToAnchor(ends.leftEnd, anchorLeft);
            snapToAnchor(body, { x: -body.barLength/2, y: 0 }, anchorLeft);
            attached = true;
        } else if (Vector.magnitude(Vector.sub(ends.leftEnd, anchorRight)) < snapDist) {
            snapBodyPositionToAnchor(ends.leftEnd, anchorRight);
            snapToAnchor(body, { x: -body.barLength/2, y: 0 }, anchorRight);
            attached = true;
        }

        const newEnds = getBarEnds(body);

        if (Vector.magnitude(Vector.sub(newEnds.rightEnd, anchorLeft)) < snapDist) {
            if (!attached) { snapBodyPositionToAnchor(newEnds.rightEnd, anchorLeft); }
            snapToAnchor(body, { x: body.barLength/2, y: 0 }, anchorLeft);
            attached = true;
        } else if (Vector.magnitude(Vector.sub(newEnds.rightEnd, anchorRight)) < snapDist) {
            if (!attached) { snapBodyPositionToAnchor(newEnds.rightEnd, anchorRight); }
            snapToAnchor(body, { x: body.barLength/2, y: 0 }, anchorRight);
            attached = true;
        }

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
        stiffness: 1,      
        length: 0, 
        damping: 0.1,     
        render: { visible: true, lineWidth: 5, strokeStyle: '#e74c3c' }
    });
    Composite.add(world, c);
    createdConstraints.push(c);
}

function startGame() {
    if (isPlaying || gameEnded) return;
    isPlaying = true;

    if (car) {
        MatterBody.setStatic(car, false);
        MatterBody.setVelocity(car, { x: 0, y: 0 });
        MatterBody.setAngularVelocity(car, 0);
        Matter.Sleeping.set(car, false);
    }

    bridgeBars.forEach(bar => {
        MatterBody.set(bar, { 
            isSensor: false, 
            isStatic: true 
        });
        bar.frictionAir = 0; 
        
        MatterBody.setDensity(bar, 0.1);
    });

    Events.on(engine, 'beforeUpdate', function gameLoop() {
        if (!gameEnded && isPlaying) {
            if (car.speed < 10) {
                MatterBody.applyForce(car, car.position, { x: 0.02, y: 0 });
            }
            
            handleCarOnBridge();
            checkWinLoseCondition();
        }
    });
}

function handleCarOnBridge() {
    if (!car || gameEnded) return;
    
    const halfCarHeight = CAR_HEIGHT / 2;
    const halfCarWidth = CAR_WIDTH / 2;
    const carBottom = car.position.y + halfCarHeight;
    const carLeft = car.position.x - halfCarWidth;
    const carRight = car.position.x + halfCarWidth;
    
    let highestBridgeTop = null;
    carOnBridge = false;
    
    bridgeBars.forEach(bar => {
        // Ignore bars that are still in placement mode; active bars can be static.
        if (bar.isSensor) return;
        
        const angle = bar.angle;
        const halfWidth = bar.barLength / 2;
        const halfHeight = 10; 
        
        const cos = Math.cos(angle);
        const sin = Math.sin(angle);
        
        const corners = [
            { 
                x: bar.position.x + (-halfWidth * cos - (-halfHeight) * sin),
                y: bar.position.y + (-halfWidth * sin + (-halfHeight) * cos)
            },
            {
                x: bar.position.x + (halfWidth * cos - (-halfHeight) * sin),
                y: bar.position.y + (halfWidth * sin + (-halfHeight) * cos)
            },
            { 
                x: bar.position.x + (-halfWidth * cos - halfHeight * sin),
                y: bar.position.y + (-halfWidth * sin + halfHeight * cos)
            },
            { 
                x: bar.position.x + (halfWidth * cos - halfHeight * sin),
                y: bar.position.y + (halfWidth * sin + halfHeight * cos)
            }
        ];
        
        const topY = Math.min(corners[0].y, corners[1].y);
        const leftX = Math.min(corners[0].x, corners[1].x, corners[2].x, corners[3].x);
        const rightX = Math.max(corners[0].x, corners[1].x, corners[2].x, corners[3].x);
        
        const carOverlapsX = carRight > leftX && carLeft < rightX;
        
        if (carOverlapsX) {
            const distanceToTop = carBottom - topY;
            
            if (distanceToTop > -30 && distanceToTop < 30) {
                if (highestBridgeTop === null || topY < highestBridgeTop) {
                    highestBridgeTop = topY;
                    carOnBridge = true;
                }
            }
        }
    });
    
    if (carOnBridge && highestBridgeTop !== null) {
        const targetY = highestBridgeTop - halfCarHeight;
        const currentY = car.position.y;
        
        if (currentY > targetY) {
            MatterBody.setPosition(car, { x: car.position.x, y: targetY });
            MatterBody.setVelocity(car, { x: car.velocity.x, y: 0 });
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

document.addEventListener('DOMContentLoaded', () => {
    const startGameButton = document.getElementById('startGameButton');
    const introModal = document.getElementById('intro-modal');
    if (startGameButton && introModal) {
        startGameButton.addEventListener('click', () => {
            introModal.classList.remove('active');
        });
    }

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