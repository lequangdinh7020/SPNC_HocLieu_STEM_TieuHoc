<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>"> 
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/trash_game.css?v=<?= time() . rand(1000, 9999) ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/tam.png" alt="Tấm" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Tấm!</h3>
            <p>Vậy là bạn đã học được cách phân loại rác rồi nhé. Giờ để thực hành, bạn có thể giúp mình dọn dẹp nhà được không?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper trash-game"><br><br><br>
    <div class="game-header">
        <h1>GIÚP TẤM DỌN NHÀ</h1>
        <p class="game-subtitle">Phân loại rác đúng cách - Bảo vệ môi trường</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label">ĐIỂM</span>
            <span id="score" class="stat-value"><?= $_SESSION['trash_score'] ?></span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">RÁC ĐÃ PHÂN LOẠI</span>
            <span id="sortedCount" class="stat-value">0</span>
        </div>
    </div>
    
    <div class="game-controls">
        <a href="<?= $base_url ?>/views/lessons/science.php" id="trashBackButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </a>
        <button id="trashResetButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
        <button id="trashCompleteButton" class="control-btn complete">
            <i class="fas fa-check-circle"></i> Hoàn thành
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Kéo các vật phẩm vào đúng thùng rác tương ứng - Hữu Cơ (xanh), Tái Chế (vàng), Vô Cơ (đỏ)</span>
        </div>
    </div>

    <div id="trashGameContainer">
        <img src="<?= $base_url ?>/public/images/trash/background.png" alt="Sân nhà Tấm" class="game-background">
        
        <div id="binContainer">
            <div class="trash-bin bin-huuco" data-bin-type="huuco">
                <img src="<?= $base_url ?>/public/images/trash/bin_green.png" alt="Thùng rác hữu cơ">
            </div>
            <div class="trash-bin bin-taiche" data-bin-type="taiche">
                <img src="<?= $base_url ?>/public/images/trash/bin_yellow.png" alt="Thùng rác tái chế">
            </div>
            <div class="trash-bin bin-voco" data-bin-type="voco">
                <img src="<?= $base_url ?>/public/images/trash/bin_red.png" alt="Thùng rác vô cơ">
            </div>
        </div>

        <div id="trashItems">
            <?php foreach ($trashItems as $item): ?>
                <img src="<?= $base_url ?>/public/images/trash/<?= $item['img'] ?>" 
                     alt="<?= $item['name'] ?>"
                     class="trash-item"
                     draggable="true"
                     id="<?= $item['id'] ?>"
                     data-group="<?= $item['group'] ?>"
                     data-attempt="1"
                     style="top: <?= $item['top'] ?>; left: <?= $item['left'] ?>;">
            <?php endforeach; ?>
        </div>

        <div id="character-area">
            <div id="tam-dialogue-box" class="hidden">
                <span id="tam-dialogue-text">...</span>
            </div>
            <img src="<?= $base_url ?>/public/images/character/tam.png" alt="Tấm" id="tam-character">
        </div>
        
    </div>
</div>

<script>
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
</script>
<script src="<?= $base_url ?>/public/JS/trash_game.js"></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>