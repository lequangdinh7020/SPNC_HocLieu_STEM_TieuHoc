<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/computer_parts_game.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class=" modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/computer_parts/case.png" alt="Máy tính" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Trò chơi: Các bộ phận của máy tính</h3>
            <p>Hãy kéo các bộ phận vào đúng vị trí của chúng trên bàn máy tính nhé! Học cách nhận biết và lắp ráp máy tính một cách chính xác.</p>
            <button id="start-game-btn" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper computer-game"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI LẮP RÁP MÁY TÍNH</h1>
        <p class="game-subtitle">Thử thách nhận biết - Rèn luyện tư duy logic</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label">ĐÃ LẮP</span>
            <span id="placed-count" class="stat-value">0</span>
        </div>
        <div class="stat-box wrong">
            <span class="stat-label">CÒN LẠI</span>
            <span id="remaining-count" class="stat-value"><?= count($computerParts) ?></span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">THỜI GIAN</span>
            <span id="timer-display" class="stat-value">00:00</span>
        </div>
    </div>
    
    <div class="game-controls">
        <a href="<?= $base_url ?>/views/main_lesson.php" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </a>
        <button id="restart-game-btn-main" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
        <button id="hint-btn" class="control-btn complete">
            <i class="fas fa-lightbulb"></i> Gợi ý
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Kéo các bộ phận từ ngăn bên trái vào đúng vị trí trên bàn máy tính</span>
        </div>
    </div>
    
    <div id="game-feedback"></div>
    
    <div class="game-container">
        <div id="game-area">
            <div id="parts-bank">
                <h3>Các bộ phận:</h3>
                <?php foreach ($computerParts as $part): ?>
                    <div class="draggable-part" draggable="true" data-part-id="<?= $part['id'] ?>">
                        <img src="<?= $base_url ?>/public/images/computer_parts/<?= $part['img'] ?>" alt="<?= $part['name'] ?>">
                        <span><?= $part['name'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="desk-area">
                <img src="<?= $base_url ?>/public/images/computer_parts/desk_outline.png" class="desk-bg-outline" alt="Bàn máy tính">

                <div class="dropzone" data-target="monitor"    style="top: 13.3%; left: 27.5%; width: 32.1%; height: 53.5%;"></div>
                <div class="dropzone" data-target="case"       style="top: 18.5%; left: 62%; width: 17.1%; height: 47.5%;"></div>
                <div class="dropzone" data-target="printer"    style="top: 39.5%; left: 10.7%;  width: 16.5%; height: 26.7%;"></div>
                <div class="dropzone" data-target="keyboard"   style="top: 71.85%; left: 26.7%; width: 35.4%; height: 19.3%;"></div>
                <div class="dropzone" data-target="mouse"      style="top: 72.7%; left: 63.7%; width: 5.2%;  height: 18.8%;"></div>
                <div class="dropzone" data-target="speaker"    style="top: 42.7%; left: 80.4%; width: 9.1%;  height: 23.2%;"></div>
                <div class="dropzone" data-target="microphone" style="top: 72%; left: 70.6%; width: 7.3%;  height: 21%;"></div>
            </div>
        </div>
    </div>
    
    <div class="game-hints">
        <div class="hint-box">
            <i class="fas fa-trophy"></i>
            <div class="hint-content">
                <h4>Mẹo để hoàn thành:</h4>
                <ul>
                    <li>Quan sát kỹ vị trí các vùng trống trên bàn</li>
                    <li>So sánh kích thước bộ phận với vùng thả</li>
                    <li>Màn hình thường ở giữa, chuột ở bên phải</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="win-modal" class="modal">
        <div class="modal-content">
            <h2>Chúc mừng!</h2>
            <p>Bạn đã lắp ráp máy tính thành công! Em giỏi quá!</p>
            <div class="modal-actions">
                <button id="restart-game-btn" class="game-btn">Chơi lại</button>
                <button id="back-to-tech-btn" class="game-btn">Quay lại</button>
            </div>
        </div>
    </div>
</div>

<script>
    const totalParts = <?= count($computerParts) ?>;
</script>
<script src="<?= $base_url ?>/public/JS/computer_parts_game.js?v=<?= time() ?>" defer></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>