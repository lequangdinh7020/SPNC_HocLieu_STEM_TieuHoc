<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/water_pipe.css?v=<?= time() . rand(1000, 9999) ?>">

<div id="story-modal" class="modal" style="display: none;">
    <div class="modal-content story-content">
        <h2>HỆ THỐNG DẪN NƯỚC</h2>
        <p>Chào kỹ sư nhí! Hãy giúp khu vườn xanh tốt trở lại.</p>
        <div class="instruction">
            <p><strong>Nhiệm vụ:</strong> <?= $currentLevel['desc'] ?></p>
            <p><strong>Cách chơi:</strong> Click vào các đoạn ống để xoay chúng thành đường kín dẫn nước từ vòi đến cây.</p>
        </div>
        <div class="level-select">
            <button class="game-btn lvl-btn" onclick="startGame()">Bắt đầu cấp độ <?= $currentLevel['id'] ?></button>
        </div>
    </div>
</div>

<div class="game-wrapper pipe-game" id="game-wrapper">
    
    <div class="game-hud">
        <div class="hud-item score-box">
            <span><?= $currentLevel['title'] ?></span>
        </div>
        
        <div class="hud-controls" style="display: flex; gap: 10px;">
            <button id="check-flow-btn" class="game-btn run">Mở Nước</button>
            <button id="reset-btn" class="game-btn reset" onclick="window.location.reload()">↺ Xếp Lại</button>
        </div>

        <a href="<?= $base_url ?>/views/main_lesson.php" class="exit-btn"><i class="fas fa-sign-out-alt"></i> Thoát</a>
    </div>

    <div id="game-stage">
        <div class="game-board-container">
            <div id="pipe-grid" class="pipe-grid" 
                 style="grid-template-columns: repeat(<?= $currentLevel['grid_size'] ?>, 1fr);">
            </div>
        </div>
    </div>

    <div id="result-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 id="modal-title"></h2>
            <p id="modal-message"></p>
            <div class="modal-actions">
                <button id="next-btn" class="game-btn lvl-btn">Tiếp tục</button>
                <button id="retry-btn" class="game-btn" onclick="window.location.reload()">Thử lại</button>
            </div>
        </div>
    </div>

</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;

    function startGame() {
        document.getElementById('story-modal').style.display = 'none';
    }
</script>
<script src="<?= $base_url ?>/public/JS/water_pipe.js"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>