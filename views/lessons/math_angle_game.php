<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/math_angle_game.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/Pythagoras_Math.png" alt="Bậc thầy góc" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Angle Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "HẬU NGHỆ BẮN MẶT TRỜI". Nhiệm vụ của bạn là tính toán góc bắn chính xác để bắn hạ các mặt trời. Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper angle-game"><br><br><br>
    <div class="game-header">
        <h1>Màn <?= $currentLevel['id'] ?>: <?= $currentLevel['title'] ?></h1>
        <p class="game-subtitle">Thử thách toán học - Rèn luyện tư duy góc</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box angle">
            <span class="stat-label">GÓC BẮN</span>
            <span id="angle-value" class="stat-value">0°</span>
        </div>
        <div class="stat-box type">
            <span class="stat-label">LOẠI GÓC</span>
            <span id="angle-type" class="stat-value">...</span>
        </div>
        <div class="stat-box level">
            <span class="stat-label">MÀN CHƠI</span>
            <span class="stat-value"><?= $currentLevel['id'] ?>/<?= $totalLevels ?></span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </button>
        <button id="resetButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Làm lại
        </button>
        <button id="fire-btn" class="control-btn complete">
            <i class="fas fa-bullseye"></i> Bắn!
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Nhiệm vụ:</strong> <?= $currentLevel['desc'] ?></span>
        </div>
    </div>
    
    <div class="game-container">
        <div id="game-stage">
            <canvas id="gameCanvas"></canvas>
            
            <div class="controls-overlay">
                <div class="protractor-container">
                    <div class="protractor-bg"></div>
                    <input type="range" id="angle-slider" min="0" max="180" value="0" step="1">
                </div>
            </div>
            
            <div id="miss-feedback" class="miss-feedback hidden">TRƯỢT RỒI! THỬ LẠI NHÉ</div>
        </div>
    </div>
</div>

<div id="result-modal" class="modal">
    <div class="modal-content result-content">
        <h2 id="modal-title"></h2>
        <p id="modal-message"></p>
        <div class="modal-buttons">
            <button id="next-level-btn" class="game-btn">Màn tiếp theo</button>
            <button id="retry-btn" class="game-btn">Thử lại</button>
            <button id="back-btn" class="game-btn back-btn">Quay lại</button>
        </div>
    </div>
</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;
</script>

<script src="<?= $base_url ?>/public/JS/math_angle_game.js?v=<?= time() ?>"></script>

<script>
    // Intro modal handling
    document.addEventListener('DOMContentLoaded', () => {
        const introModal = document.getElementById('intro-modal');
        const startGameButton = document.getElementById('startGameButton');
        const giveUpButton = document.getElementById('giveUpButton');
        const backButton = document.getElementById('back-btn');
        
        if (startGameButton) {
            startGameButton.addEventListener('click', () => {
                introModal.classList.remove('active');
            });
        }
        
        if (giveUpButton) {
            giveUpButton.addEventListener('click', () => {
                window.location.href = '<?= $base_url ?>/views/main_lesson.php';
            });
        }
        
        if (backButton) {
            backButton.addEventListener('click', () => {
                window.location.href = '<?= $base_url ?>/views/lessons/math.php';
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>