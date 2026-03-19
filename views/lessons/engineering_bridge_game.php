<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/bridge_game.css?v=<?= time() ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/matter-js/0.19.0/matter.min.js"></script>

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/bridge_game/nguu_lang_chuc_nu_holding_hands.png" alt="Ngưu Lang Chức Nữ" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>LỜI DẪN CHUYỆN: BẮC CẦU NGÂN HÀ</h3>
            <p id="storyText">Chào các bạn nhỏ! Chắc hẳn chúng mình đã từng nghe câu chuyện về chàng Ngưu Lang và nàng tiên Chức Nữ rồi đúng không? Hai người bị chia cắt bởi dòng sông Ngân Hà rộng mênh mông và chỉ được gặp nhau mỗi năm đúng một lần.</p>
            <button id="nextStoryButton" class="start-btn"><i class="fas fa-forward"></i> Tiếp theo</button>
        </div>
    </div>
</div>

<div class="game-wrapper bridge-game"><br><br><br>
    <div class="game-header">
        <h1>XÂY CẦU VƯỢT</h1>
        <p class="game-subtitle">Kỹ sư nhí - Thiết kế cầu vững chắc</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label">CẤP ĐỘ</span>
            <span id="currentLevel" class="stat-value">1</span>
        </div>
        <div class="stat-box progress">
            <span class="stat-label">TIẾN ĐỘ</span>
            <span id="progressCount" class="stat-value">0/5</span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">TRẠNG THÁI</span>
            <span id="gameStatus" class="stat-value">Sẵn sàng</span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="replayButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
        <button id="playButton" class="control-btn pause">
            <i class="fas fa-play"></i> Chạy thử
        </button>
        <button id="nextButton" class="control-btn complete" disabled>
            <i class="fas fa-arrow-right"></i> Màn tiếp
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Kéo và thả các thanh gỗ từ kho vật liệu để xây cầu. Nhấn "Chạy thử" để kiểm tra cầu có đủ vững chắc không!</span>
        </div>
    </div>

    <div id="bridge-game-container">
        <div id="supply-zone">
            <span id="supply-label">KHO VẬT LIỆU</span>
        </div>
        <div id="status-msg"></div>
    </div>
</div>

<script>
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
</script>
<script src="<?= $base_url ?>/public/JS/bridge_game.js"></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>