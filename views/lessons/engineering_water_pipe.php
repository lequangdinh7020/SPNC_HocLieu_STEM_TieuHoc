<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/water_pipe.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/engineer.png" alt="Kỹ sư nước" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Water Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "HỆ THỐNG DẪN NƯỚC". Nhiệm vụ của bạn là xoay các đoạn ống để tạo thành đường dẫn nước từ vòi đến cây. Hãy giúp khu vườn xanh tốt trở lại! Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper pipe-game-mode"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI HỆ THỐNG DẪN NƯỚC</h1>
        <p class="game-subtitle">Thử thách logic - Rèn luyện tư duy không gian</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box remaining">
            <span class="stat-label">CẤP ĐỘ</span>
            <span id="level-display" class="stat-value"><?= $currentLevel['id'] ?>/<?= $totalLevels ?></span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">THỜI GIAN</span>
            <span id="timer-display" class="stat-value">00:00</span>
        </div>
        <div class="stat-box progress">
            <span class="stat-label">TRẠNG THÁI</span>
            <span id="status-display" class="stat-value">Sẵn sàng</span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </button>
        <button id="reset-btn" class="control-btn reset" onclick="window.location.reload()">
            <i class="fas fa-redo"></i> Xếp lại
        </button>
        <button id="check-flow-btn" class="control-btn complete">
            <i class="fas fa-tint"></i> Mở nước
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> <?= $currentLevel['desc'] ?> - Click vào các đoạn ống để xoay chúng thành đường dẫn nước.</span>
        </div>
    </div>
    
    <div class="game-container-pipe">
        <div id="game-stage">
            <div class="game-board-container">
                <div id="pipe-grid" class="pipe-grid" 
                     style="grid-template-columns: repeat(<?= $currentLevel['grid_size'] ?>, 1fr);">
                </div>
            </div>
        </div>
    </div>
</div>

<div id="result-modal" class="modal" style="display: none;">
    <div class="modal-content result-content">
        <h2 id="modal-title"></h2>
        <p id="modal-message"></p>
        <div class="modal-actions">
            <button id="next-btn" class="game-btn lvl-btn">Tiếp tục</button>
            <button id="retry-btn" class="game-btn" onclick="window.location.reload()">Thử lại</button>
        </div>
    </div>
</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;
</script>

<script src="<?= $base_url ?>/public/JS/water_pipe.js?v=<?= time() ?>"></script>

<script>
    // Intro modal handling
    document.addEventListener('DOMContentLoaded', () => {
        const introModal = document.getElementById('intro-modal');
        const startGameButton = document.getElementById('startGameButton');
        const giveUpButton = document.getElementById('giveUpButton');
        
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
    });
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>