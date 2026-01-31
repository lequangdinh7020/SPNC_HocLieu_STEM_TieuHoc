<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/water_filter.css?v=<?= time() . rand(1000, 9999) ?>">

<div class="game-wrapper filter-game">
    <div class="header-game">
        <a href="<?= $base_url ?>/views/main_lesson.php" class="home-btn"><i class="fas fa-home"></i></a>
        <h1><?= $gameData['title'] ?></h1>
    </div>
    
    <div class="game-area">
        
        <div class="materials-panel">
            <h3>Vật Liệu</h3>
            <div class="materials-grid">
                <p class="hint-text">Kéo thả vật liệu vào chai</p>
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

        <div class="controls-panel">
            <button id="test-btn" class="game-btn run">Đổ Nước Bẩn</button>
            <button class="game-btn" onclick="window.location.href='<?= $base_url ?>/views/lessons/engineering.php'">Quay lại</button>
            <button id="reset-btn" class="game-btn reset">Làm Lại</button>
        </div>

    </div>

    <div id="result-modal" class="modal">
        <div class="modal-content">
            <h2 id="modal-title"></h2>
            <p id="modal-message"></p>
            <div id="science-explanation"></div>
            <button id="retry-btn" class="game-btn">Thử lại</button>
        </div>
    </div>
</div>

<script>
    const correctOrder = <?= json_encode($gameData['correct_order']) ?>;
</script>
<script src="<?= $base_url ?>/public/JS/water_filter.js"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>