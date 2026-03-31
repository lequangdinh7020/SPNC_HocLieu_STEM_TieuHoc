const Engine = Matter.Engine,
      Render = Matter.Render,
      Runner = Matter.Runner,
      Bodies = Matter.Bodies,
      Body = Matter.Body,
      Composite = Matter.Composite,
      Constraint = Matter.Constraint,
      Mouse = Matter.Mouse,
      MouseConstraint = Matter.MouseConstraint,
      Events = Matter.Events,
      Vector = Matter.Vector;

const CONF = {
    nodeRadius: 12,
    linkThickness: 6,
    maxLinkLength: levelConfig.connectDistance, 
    
    linkStiffness: 0.40,    
    breakThreshold: 0.002, 
    
    colors: {
        node: '#95a5a6',
        anchor: '#34495e',
        link: '#5d4037',      
        linkStressed: '#e74c3c', 
        target: '#ecf0f1',
        ground: '#27ae60',
        previewLine: 'rgba(46, 204, 113, 0.6)'
    }
};

let engine, world, render, runner;
let nodes = []; 
let targetSensors = []; 
let isWon = false;
let isLost = false;
let remainingNodes = parseInt(document.getElementById('remaining-nodes').innerText);
let isDraggingFromUI = false;
let isDraggingLooseNode = false;
let draggedBody = null;
let currentDragPos = { x: 0, y: 0 }; 

const ghostNode = document.getElementById('drag-ghost');
const nodeSource = document.getElementById('node-source');
const container = document.getElementById('physics-container');

document.addEventListener("DOMContentLoaded", () => {
    const introModal = document.getElementById('intro-modal');
    const startGameButton = document.getElementById('startGameButton');
    
    if (startGameButton) {
        startGameButton.addEventListener('click', () => {
            introModal.classList.remove('active');
            initGame();
        });
    }
    
    const resetBtnMain = document.getElementById('reset-btn-main');
    const giveUpButton = document.getElementById('giveUpButton');
    
    if (resetBtnMain) {
        resetBtnMain.addEventListener('click', () => window.location.reload());
    }
    
    if (giveUpButton) {
        giveUpButton.addEventListener('click', () => {
            window.location.href = baseUrl + '/views/main_lesson.php';
        });
    }
});


function parseVal(val, maxDimension) {
    if (typeof val === 'string' && val.includes('%')) {
        return (parseFloat(val) / 100) * maxDimension;
    }
    return val;
}

function initGame() {
    const width = container.clientWidth;
    const height = container.clientHeight;

    engine = Engine.create();
    world = engine.world;
    engine.positionIterations = 10;
    engine.velocityIterations = 10;

    render = Render.create({
        element: container,
        engine: engine,
        options: {
            width: width, height: height,
            wireframes: false, background: 'transparent', showAngleIndicator: false
        }
    });

    const ground = Bodies.rectangle(width / 2, height + 30, width, 100, { 
        isStatic: true, render: { fillStyle: CONF.colors.ground }
    });
    Composite.add(world, ground);

    const targetsData = levelConfig.targets || [levelConfig.targetPos];

    targetsData.forEach(pos => {
        const realX = parseVal(pos.x, width);
        const realY = parseVal(pos.y, height);

        const sensor = Bodies.circle(realX, realY, 25, {
            isStatic: true, 
            isSensor: true, 
            render: { fillStyle: CONF.colors.target, strokeStyle: '#f1c40f', lineWidth: 4 }
        });
        sensor.isHit = false; 
        targetSensors.push(sensor);
        Composite.add(world, sensor);
    });

    levelConfig.anchors.forEach(pos => {
        const realX = parseVal(pos.x, width);
        const realY = parseVal(pos.y, height);
        createNode(realX, realY, true);
    });

    const mouse = Mouse.create(render.canvas);
    const mouseConstraint = MouseConstraint.create(engine, {
        mouse: mouse,
        constraint: { stiffness: 0.2, render: { visible: false } }
    });
    Composite.add(world, mouseConstraint);
    render.mouse = mouse;

    Events.on(mouseConstraint, 'startdrag', function(event) {
        if (isWon || isLost) return;
        const body = event.body;
        if (nodes.includes(body)) {
            if (isNodeLinked(body) || body.isStatic) {
                event.source.constraint.bodyB = null; 
            } else {
                isDraggingLooseNode = true;
                draggedBody = body;
            }
        }
    });

    Events.on(mouseConstraint, 'enddrag', function(event) {
        if (isDraggingLooseNode && draggedBody) {
            tryConnectNode(draggedBody);
            isDraggingLooseNode = false;
            draggedBody = null;
        }
    });

    Events.on(mouseConstraint, 'mousemove', function(event) {
        currentDragPos.x = event.mouse.position.x;
        currentDragPos.y = event.mouse.position.y;
    });

    setupUIDragLogic();

    Events.on(engine, 'beforeUpdate', checkStructuralIntegrity);
    Events.on(render, 'afterRender', drawPreviewLinks);
    Events.on(engine, 'collisionStart', checkWin);

    runner = Runner.create();
    Runner.run(runner, engine);
    Render.run(render);
}

function checkStructuralIntegrity() {
    if (isWon || isLost) return;

    const constraints = Composite.allConstraints(world);
    
    for (let i = 0; i < constraints.length; i++) {
        const c = constraints[i];
        if (c.label === "Mouse Constraint") continue;
        
        const bodyA = c.bodyA;
        const bodyB = c.bodyB;
        if (!bodyA || !bodyB) continue;

        const currentLength = Vector.magnitude(Vector.sub(bodyA.position, bodyB.position));
        const originalLength = c.length;
        const diff = currentLength - originalLength;
        const strain = diff / originalLength; 

        if (strain > (CONF.breakThreshold * 0.4)) { 
            c.render.strokeStyle = CONF.colors.linkStressed;
            c.render.lineWidth = Math.max(2, CONF.linkThickness - (strain * 50)); 
        } else {
            c.render.strokeStyle = CONF.colors.link;
            c.render.lineWidth = CONF.linkThickness;
        }

        if (strain > CONF.breakThreshold) {
            Composite.remove(world, c); 
            loseGame(); 
            break; 
        }
    }
}

function loseGame() {
    if (isLost || isWon) return;
    isLost = true;
    setTimeout(() => {
        document.getElementById('lose-modal').style.display = 'flex';
    }, 1000);
}

function isConnectedToAnchor(startNode) {
    if (startNode.isStatic) return true; 

    let queue = [startNode];
    let visited = new Set();
    visited.add(startNode);

    const constraints = Composite.allConstraints(world);

    while (queue.length > 0) {
        let currentNode = queue.shift();

        for (let c of constraints) {
            if (c.label === "Mouse Constraint") continue;

            let neighbor = null;
            if (c.bodyA === currentNode) neighbor = c.bodyB;
            else if (c.bodyB === currentNode) neighbor = c.bodyA;

            if (neighbor && nodes.includes(neighbor) && !visited.has(neighbor)) {
                if (neighbor.isStatic) return true;
                
                visited.add(neighbor);
                queue.push(neighbor);
            }
        }
    }

    return false; 
}

function checkWin(event) {
    if (isWon || isLost) return;
    
    const pairs = event.pairs;
    
    for (let i = 0; i < pairs.length; i++) {
        const pair = pairs[i];
        
        targetSensors.forEach(sensor => {
            if ((pair.bodyA === sensor || pair.bodyB === sensor) && !sensor.isHit) {
                const other = pair.bodyA === sensor ? pair.bodyB : pair.bodyA;
                
                if (nodes.includes(other)) {
                    if (isConnectedToAnchor(other)) {
                        sensor.isHit = true;
                        sensor.render.fillStyle = "#2ecc71"; 
                        Body.scale(sensor, 1.2, 1.2);
                        setTimeout(() => Body.scale(sensor, 1/1.2, 1/1.2), 200);
                    } else {
                    }
                }
            }
        });
    }

    const allHit = targetSensors.every(s => s.isHit);

    if (allHit) {
        isWon = true;
        Runner.stop(runner);
        const completedLevels = (typeof currentLevelId !== 'undefined') ? currentLevelId : 1;
        const totalLevels = (typeof totalTowerLevels !== 'undefined') ? totalTowerLevels : 1;

        if (completedLevels < totalLevels) {
            setTimeout(() => { document.getElementById('result-modal').style.display = 'flex'; }, 500);
            return;
        }

        const pct = totalLevels > 0 ? Math.round((completedLevels / totalLevels) * 100) : 0;

        fetch(`${baseUrl}/views/lessons/update-tower-score`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'commit', score_pct: pct })
        }).then(r => r.json()).then(json => {
            const modal = document.getElementById('result-modal');
            const title = modal.querySelector('h2');
            const para = modal.querySelector('p');
            if (json && json.success) {
                title.innerText = 'HOÀN THÀNH!';
                para.innerText = `Bạn hoàn thành ${completedLevels}/${totalLevels} màn. Độ hoàn thành: ${pct}%` + (json.xp_awarded ? `\nBạn nhận +${json.xp_awarded} XP` : '');
            } else {
                title.innerText = 'HOÀN THÀNH! (Không lưu được)';
                para.innerText = `Bạn hoàn thành ${completedLevels}/${totalLevels} màn. Độ hoàn thành: ${pct}%. \nLỗi: ${json && json.message ? json.message : 'Không thể lưu điểm'}`;
            }
            setTimeout(() => { modal.style.display = 'flex'; }, 500);
        }).catch(err => {
            console.error('Tower commit error', err);
            setTimeout(() => { document.getElementById('result-modal').style.display = 'flex'; }, 500);
        });
    }
}

function isNodeLinked(node) {
    return world.constraints.some(c => c.label !== "Mouse Constraint" && (c.bodyA === node || c.bodyB === node));
}

function tryConnectNode(node) {
    nodes.forEach(otherNode => {
        if (otherNode === node) return;
        const dist = Vector.magnitude(Vector.sub(node.position, otherNode.position));
        if (dist < CONF.maxLinkLength) {
            createLink(node, otherNode);
        }
    });
}

function createNode(x, y, isStatic) {
    const node = Bodies.circle(x, y, CONF.nodeRadius, {
        isStatic: isStatic,
        friction: 0.9,
        restitution: 0.1,
        density: isStatic ? 1 : 0.002, 
        collisionFilter: { group: 1 }, 
        render: { fillStyle: isStatic ? CONF.colors.anchor : CONF.colors.node, strokeStyle: '#7f8c8d', lineWidth: 2 }
    });
    nodes.push(node);
    Composite.add(world, node);
    return node;
}

function createLink(nodeA, nodeB) {
    const exists = world.constraints.some(c => 
        (c.bodyA === nodeA && c.bodyB === nodeB) || (c.bodyA === nodeB && c.bodyB === nodeA)
    );
    if (exists) return;

    const currentDist = Vector.magnitude(Vector.sub(nodeA.position, nodeB.position));
    const link = Constraint.create({
        bodyA: nodeA, bodyB: nodeB,
        length: currentDist,
        stiffness: CONF.linkStiffness, 
        damping: 0.05,
        render: { visible: true, type: 'line', strokeStyle: CONF.colors.link, lineWidth: CONF.linkThickness }
    });
    Composite.add(world, link);
}

function setupUIDragLogic() {
    nodeSource.addEventListener('mousedown', (e) => {
        if (remainingNodes <= 0 || isWon || isLost) return;
        isDraggingFromUI = true;
        ghostNode.style.display = 'block';
        updateGhostPosition(e);
    });

    document.addEventListener('mousemove', (e) => {
        if (isDraggingFromUI) {
            updateGhostPosition(e);
            const rect = render.canvas.getBoundingClientRect();
            currentDragPos.x = e.clientX - rect.left;
            currentDragPos.y = e.clientY - rect.top;
        }
    });

    document.addEventListener('mouseup', (e) => {
        if (isDraggingFromUI) {
            isDraggingFromUI = false;
            ghostNode.style.display = 'none';
            const rect = render.canvas.getBoundingClientRect();
            if (e.clientX >= rect.left && e.clientX <= rect.right &&
                e.clientY >= rect.top && e.clientY <= rect.bottom) {
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                spawnNodeAt(x, y);
            }
        }
    });
}

function updateGhostPosition(e) {
    ghostNode.style.left = (e.clientX - 15) + 'px';
    ghostNode.style.top = (e.clientY - 15) + 'px';
}

function spawnNodeAt(x, y) {
    if (remainingNodes <= 0) return;
    const newNode = createNode(x, y, false);
    remainingNodes--;
    document.getElementById('remaining-nodes').innerText = remainingNodes;
    tryConnectNode(newNode);
}

function drawPreviewLinks() {
    if ((!isDraggingFromUI && !isDraggingLooseNode) || isWon || isLost) return;
    
    let sourcePos = currentDragPos; 
    if (isDraggingLooseNode && draggedBody) {
        sourcePos = draggedBody.position;
    }
    const ctx = render.context;
    
    nodes.forEach(node => {
        if (draggedBody && node === draggedBody) return;
        
        const dist = Vector.magnitude(Vector.sub(node.position, sourcePos));
        if (dist < CONF.maxLinkLength) {
            ctx.beginPath();
            ctx.moveTo(sourcePos.x, sourcePos.y);
            ctx.lineTo(node.position.x, node.position.y);
            ctx.lineWidth = 4;
            ctx.strokeStyle = CONF.colors.previewLine;
            ctx.setLineDash([10, 10]);
            ctx.stroke();
            ctx.setLineDash([]);
            
            ctx.beginPath();
            ctx.arc(node.position.x, node.position.y, CONF.nodeRadius + 5, 0, 2 * Math.PI);
            ctx.strokeStyle = '#2ecc71';
            ctx.lineWidth = 2;
            ctx.stroke();
        }
    });
}