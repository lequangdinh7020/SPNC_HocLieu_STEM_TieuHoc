<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/water_filter.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/scientist.png" alt="Nhà khoa học" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Filter Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "HỆ THỐNG LỌC NƯỚC". Nhiệm vụ của bạn là sắp xếp các lớp vật liệu đúng thứ tự để lọc nước bẩn thành nước sạch. Hãy áp dụng kiến thức khoa học! Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper filter-game-mode"><br><br><br>
    <div class="game-header">
        <h1><?= $gameData['title'] ?></h1>
        <p class="game-subtitle">Thử thách khoa học - Rèn luyện tư duy logic</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box remaining">
            <span class="stat-label">VẬT LIỆU</span>
            <span id="materials-count" class="stat-value"><?= count($gameData['materials']) ?></span>
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
        <button id="reset-btn" class="control-btn reset">
            <i class="fas fa-redo"></i> Làm lại
        </button>
        <button id="test-btn" class="control-btn complete">
            <i class="fas fa-tint"></i> Đổ nước
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Kéo các vật liệu từ kho vào chai theo thứ tự đúng để tạo hệ thống lọc nước hiệu quả</span>
        </div>
    </div>
    
    <div class="game-container-filter">
        <div class="materials-panel">
            <h3>Kho Vật Liệu</h3>
            <div class="materials-grid">
                <?php foreach ($gameData['materials'] as $mat): ?>
                    <div class="material-item mat-<?= $mat['id'] ?>" draggable="true" data-id="<?= $mat['id'] ?>">
                        <div class="mat-icon" style="background-image: url('<?= $base_url ?>/public/images/water_filter/<?= $mat['img'] ?>');"></div>
                        <span><?= $mat['name'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bottle-system">
            <div class="water-container" id="water-effect"></div>
            <div class="bottle" id="bottle-layers">
                <div class="layer-placeholder">Kéo vật liệu vào đây</div>
            </div>
            <div class="bottle-neck"></div>
            <div class="beaker">
                <div class="beaker-water" id="result-water"></div>
            </div>
        </div>
    </div>
</div>

<div id="result-modal" class="modal">
    <div class="modal-content result-content">
        <h2 id="modal-title"></h2>
        <p id="modal-message"></p>
        <div id="science-explanation"></div>
        <div class="modal-buttons">
            <button id="retry-btn" class="game-btn">Thử lại</button>
            <button id="back-btn" class="game-btn back-btn">Quay lại</button>
        </div>
    </div>
</div>

<script>
    const correctOrder = <?= json_encode($gameData['correct_order']) ?>;
</script>

<script src="<?= $base_url ?>/public/JS/water_filter.js?v=<?= time() ?>"></script>

<script>
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
        
        const backButton = document.getElementById('back-btn');
        if (backButton) {
            backButton.addEventListener('click', () => {
                window.location.href = '<?= $base_url ?>/views/lessons/engineering.php';
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>