<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/tangram.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/Pythagoras_Math.png" alt="Bậc thầy Tangram" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Tangram Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "TANGRAM". Nhiệm vụ của bạn là sắp xếp các mảnh ghép để tạo thành hình hoàn chỉnh. Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper tangram-game"><br><br><br>
    <div class="game-header">
        <h1>🧩 <?= $currentLevel['title'] ?></h1>
        <p class="game-subtitle">Thử thách hình học - Rèn luyện tư duy không gian</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box level">
            <span class="stat-label">MÀN CHƠI</span>
            <span class="stat-value"><?= $currentLevel['id'] ?>/<?= $totalLevels ?></span>
        </div>
        <div class="stat-box pieces">
            <span class="stat-label">MẢNH GHÉP</span>
            <span class="stat-value">7</span>
        </div>
        <div class="stat-box status">
            <span class="stat-label">TRẠNG THÁI</span>
            <span class="stat-value">Đang chơi</span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </button>
        <button id="resetButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Làm lại
        </button>
        <button id="complete-btn" class="control-btn complete">
            <i class="fas fa-check-circle"></i> Hoàn thành
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> <?= $currentLevel['desc'] ?> | Kéo thả để di chuyển | Nhấn chuột để xoay</span>
        </div>
    </div>
    
    <div class="game-container">
        <div id="canvas-container">
            <canvas id="gameCanvas" width="800" height="600"></canvas>
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
            <button id="play-again-btn" class="game-btn" style="display:none;">Chơi lại</button>
            <button id="back-btn" class="game-btn back-btn">Quay lại</button>
        </div>
    </div>
</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;
</script>

<script src="<?= $base_url ?>/public/JS/tangram.js?v=<?= time() ?>"></script>

<script>
    // Intro modal handling
    document.addEventListener('DOMContentLoaded', () => {
        const introModal = document.getElementById('intro-modal');
        const startGameButton = document.getElementById('startGameButton');
        const giveUpButton = document.getElementById('giveUpButton');
        const backButton = document.getElementById('back-btn');
        const resetButton = document.getElementById('resetButton');
        
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
        
        if (resetButton) {
            resetButton.addEventListener('click', () => {
                location.reload();
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>