<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/tower_game.css?v=<?= time() ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/matter-js/0.19.0/matter.min.js"></script>

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/engineer.png" alt="Kỹ sư xây dựng" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Tower Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "XÂY DỰNG THÁP". Nhiệm vụ của bạn là xây dựng tháp vững chắc bằng cách sắp xếp các khối một cách thông minh. Hãy cẩn thận với trọng lực và độ bền! Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper tower-game-mode"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI XÂY DỰNG THÁP</h1>
        <p class="game-subtitle">Thử thách xây dựng - Rèn luyện kỹ năng kỹ thuật</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box remaining">
            <span class="stat-label">SỐ KHỐI CÒN LẠI</span>
            <span id="remaining-nodes-display" class="stat-value"><?= $currentLevel['config']['freeNodes'] ?></span>
        </div>
        <div class="stat-box progress">
            <span class="stat-label">CẤP ĐỘ</span>
            <span id="level-display" class="stat-value"><?= $currentLevel['id'] ?>/<?= $totalLevels ?? 1 ?></span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </button>
        <button id="reset-btn-main" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Kéo các khối từ thanh công cụ và thả vào vị trí để xây dựng tháp vững chắc. Cẩn thận với trọng lực!</span>
        </div>
    </div>
    
    <div class="game-container">
        <div id="physics-container"></div>

        <div class="build-toolbar">
            <div class="node-inventory" id="node-source">
                <div class="node-icon"></div>
                <div class="node-count">
                    <span>x</span><span id="remaining-nodes"><?= $currentLevel['config']['freeNodes'] ?></span>
                </div>
                <div class="tooltip">Kéa thả vào màn hình</div>
            </div>
        </div>

        <div id="drag-ghost" class="node-ghost"></div>
    </div>
            
    </div>
    
    <div id="result-modal" class="modal">
        <div class="modal-content">
            <h2>HOÀN THÀNH!</h2>
            <p>Bạn đã chinh phục thử thách!</p>
            <div class="modal-buttons">
                <?php if ($currentLevel['id'] < ($totalLevels ?? 1)): ?>
                    <a href="engineering_tower_game?level=<?= $currentLevel['id'] + 1 ?>" class="game-btn next">Tiếp theo</a>
                <?php else: ?>
                    <button onclick="window.location.reload()" class="game-btn">Chơi lại</button>
                <?php endif; ?>
                
                <a href="<?= $base_url ?>/views/main_lesson.php" class="game-btn home-btn-ui">Về Trang Chủ</a>
            </div>
        </div>
    </div>
    
    <div id="lose-modal" class="modal">
        <div class="modal-content lose-content">
            <h2>CẤU TRÚC ĐÃ GÃY!</h2>
            <p>Tháp không chịu nổi lực căng và đã sụp đổ.</p>
            <div class="modal-buttons">
                <button onclick="window.location.reload()" class="game-btn reset">Chơi lại</button>
                <a href="<?= $base_url ?>/views/main_lesson.php" class="game-btn home-btn-ui">Về Trang Chủ</a>
            </div>
        </div>
    </div>
</div>

<script>
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
    const levelConfig = <?= json_encode($currentLevel['config']) ?>;
    const currentLevelId = <?= $currentLevel['id'] ?>;
    const totalTowerLevels = <?= $totalLevels ?? 1 ?>;
</script>

<script src="<?= $base_url ?>/public/JS/tower_game.js?v=<?= time() ?>" defer></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>