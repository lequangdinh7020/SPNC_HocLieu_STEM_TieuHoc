<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/shapes_game.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css">

<style>
    body {
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%) !important;
        min-height: 100vh;
    }
    
    .game-wrapper {
        min-height: calc(100vh - 150px);
        padding-bottom: 50px;
    }
    
    footer {
        margin-top: 50px;
    }
</style>

<div class="game-wrapper"><br><br><br><br><br>
    <div class="game-container">
        <div class="left-panel">
            <div class="mission-card">
                <div class="mission-header">
                    <h2>Thử thách hình học</h2>
                    <div class="challenge-counter">
                        <span class="current-challenge" id="currentChallenge">1</span>
                        <span class="total-challenges">/6</span>
                    </div>
                </div>
                
                <div class="mission-content">
                    <div class="challenge-info">
                        <div class="shape-icon-large" id="shapeIcon">□</div>
                        <div class="challenge-text">
                            <h3 id="challengeTitle">Hình vuông</h3>
                            <p class="challenge-desc" id="challengeDesc">4 cạnh bằng nhau</p>
                        </div>
                    </div>
                    
                    <div class="challenge-question">
                        <p class="question-text" id="questionText">
                            Biến hình vuông thành hình chữ nhật
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="shape-progress">
                <h2>Tiến độ</h2>
                <div class="progress-grid">
                    <div class="progress-item completed" data-shape="square">
                        <span class="progress-icon">□</span>
                        <span class="progress-name">Hình vuông</span>
                    </div>
                    <div class="progress-item" data-shape="rectangle" id="progressRectangle">
                        <span class="progress-icon">▭</span>
                        <span class="progress-name">Hình chữ nhật</span>
                    </div>
                    <div class="progress-item" data-shape="triangle" id="progressTriangle">
                        <span class="progress-icon">△</span>
                        <span class="progress-name">Tam giác</span>
                    </div>
                    <div class="progress-item" data-shape="trapezoid" id="progressTrapezoid">
                        <span class="progress-icon">⏢</span>
                        <span class="progress-name">Hình thang</span>
                    </div>
                    <div class="progress-item" data-shape="parallelogram" id="progressParallelogram">
                        <span class="progress-icon">▱</span>
                        <span class="progress-name">Bình hành</span>
                    </div>
                    <div class="progress-item" data-shape="rhombus" id="progressRhombus">
                        <span class="progress-icon">◇</span>
                        <span class="progress-name">Hình thoi</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="right-panel">
            <div class="game-area">
                <div class="game-title">
                    <h1>Kéo điểm để tạo hình</h1>
                </div>
                
                <div class="play-area">
                    <div class="shape-status">
                        <div class="current-shape">
                            <span>Hình hiện tại: </span>
                            <span class="shape-name" id="currentShapeName">Hình vuông</span>
                        </div>
                        <div class="target-shape">
                            <span>Yêu cầu: </span>
                            <span class="shape-name target" id="targetShapeName">Hình chữ nhật</span>
                        </div>
                    </div>
                    
                    <div class="canvas-container">
                        <canvas id="shapeCanvas" width="400" height="350"></canvas>
                        
                        <div class="draggable-point" id="pointA" data-point="A">
                            <div class="point-circle">A</div>
                        </div>
                        <div class="draggable-point" id="pointB" data-point="B">
                            <div class="point-circle">B</div>
                        </div>
                        <div class="draggable-point" id="pointC" data-point="C">
                            <div class="point-circle">C</div>
                        </div>
                        <div class="draggable-point" id="pointD" data-point="D">
                            <div class="point-circle">D</div>
                        </div>
                    </div>
                    
                    <div class="controls">
                        <button id="checkBtn" class="control-btn primary-btn">
                            Kiểm tra
                        </button>
                        <button id="resetBtn" class="control-btn secondary-btn">
                            Làm lại
                        </button>
                        <button id="backToLessonsBtn" class="control-btn tertiary-btn" onclick="window.location.href='<?= $base_url ?>/views/lessons/math.php'">
                            Quay lại
                        </button>
                    </div>
                    
                    <div class="feedback-container">
                        <div class="feedback-message" id="feedbackMessage">
                            <span class="feedback-text" id="feedbackText">
                                Kéo các điểm A, B, C, D để tạo hình
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
    window.gameData = <?= json_encode([
        'challenges' => [
            [
                'id' => 1,
                'title' => 'Hình vuông → Hình chữ nhật',
                'description' => '4 góc vuông',
                'question' => 'Biến hình vuông thành hình chữ nhật',
                'startingShape' => 'square',
                'targetShape' => 'rectangle',
                'startingPoints' => [[120,80], [200,80], [200,160], [120,160]],
                'nextShape' => 'triangle'
            ],
            [
                'id' => 2,
                'title' => 'Hình vuông → Tam giác',
                'description' => '3 cạnh',
                'question' => 'Biến hình vuông thành tam giác',
                'startingShape' => 'square',
                'targetShape' => 'triangle',
                'startingPoints' => [[120,80], [200,80], [200,160], [120,160]],
                'nextShape' => 'trapezoid'
            ],
            [
                'id' => 3,
                'title' => 'Hình vuông → Hình thang',
                'description' => 'Có cạnh song song',
                'question' => 'Biến hình vuông thành hình thang',
                'startingShape' => 'square',
                'targetShape' => 'trapezoid',
                'startingPoints' => [[120,80], [200,80], [200,160], [120,160]],
                'nextShape' => 'parallelogram'
            ],
            [
                'id' => 4,
                'title' => 'Hình vuông → Bình hành',
                'description' => 'Cạnh đối song song',
                'question' => 'Biến hình vuông thành hình bình hành',
                'startingShape' => 'square',
                'targetShape' => 'parallelogram',
                'startingPoints' => [[120,80], [200,80], [200,160], [120,160]],
                'nextShape' => 'rhombus'
            ],
            [
                'id' => 5,
                'title' => 'Hình vuông → Hình thoi',
                'description' => '4 cạnh bằng nhau',
                'question' => 'Biến hình vuông thành hình thoi',
                'startingShape' => 'square',
                'targetShape' => 'rhombus',
                'startingPoints' => [[120,80], [200,80], [200,160], [120,160]],
                'nextShape' => 'square2'
            ],
            [
                'id' => 6,
                'title' => 'Hình thoi → Hình vuông',
                'description' => '4 cạnh bằng, 4 góc vuông',
                'question' => 'Biến hình thoi thành hình vuông',
                'startingShape' => 'rhombus',
                'targetShape' => 'square',
                'startingPoints' => [[160,40], [240,120], [160,200], [80,120]],
                'nextShape' => null
            ]
        ]
    ]) ?>;
</script>
<script src="<?= $base_url ?>/public/JS/shapes_game.js?v=<?= time() ?>"></script>
<br><br><br>
<?php
require_once __DIR__ . '/../template/footer.php';
?>