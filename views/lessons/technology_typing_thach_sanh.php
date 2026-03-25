<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/typing_thach_sanh.css?v=<?= time() ?>">

<div id="story-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/thachsanh/thach_sanh.png" alt="Thạch Sanh" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3 id="storyHeading">Tráng sĩ Thạch Sanh</h3>
            <p id="storyText">Chào các bạn nhỏ! Ngôi làng yên bình của chúng ta đang gặp một mối nguy hiểm vô cùng to lớn. Lão Chằn Tinh hung ác từ trong núi sâu cùng đàn Đại Bàng khổng lồ đang ầm ầm kéo đến tấn công, đe dọa cuộc sống của người dân.</p>
            <button id="nextStoryButton" class="start-btn">Tiếp theo</button>
            <div id="levelSelect" class="level-select" style="display: none;">
                <button class="game-btn lvl-btn" onclick="startGame('easy')">Cấp độ 1: Cơ bản (Hàng A,S,D...)</button>
                <button class="game-btn lvl-btn hard" onclick="startGame('hard')">Cấp độ 2: Nâng cao (Từ vựng)</button>
            </div>
        </div>
    </div>
</div>

<div class="game-wrapper thach-sanh-game" id="game-wrapper"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI DŨNG SĨ DIỆT CHẰN TINH</h1>
        <p class="game-subtitle">Thử thách đánh máy - Rèn luyện phản xạ nhanh nhạy</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label">ĐIỂM</span>
            <span id="score" class="stat-value">0</span>
        </div>
        <div class="stat-box wrong">
            <span class="stat-label">MẠNG</span>
            <span id="lives" class="stat-value">5</span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">THỜI GIAN</span>
            <span id="time-display" class="stat-value">01:30</span>
        </div>
    </div>
    
    <div class="game-controls">
        <a href="<?= $base_url ?>/views/main_lesson.php" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </a>
        <button id="replay-btn-game" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
        <button id="quick-complete-btn" class="control-btn complete">
            <i class="fas fa-check-circle"></i> Hoàn thành
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Gõ các chữ cái hoặc từ xuất hiện trên quái vật để tiêu diệt chúng trước khi chúng chạm đất</span>
        </div>
    </div>
    
    <div class="game-container">
        <div id="game-stage">
            <div id="arrows-container"></div>
            
            <div id="thach-sanh">
                <img src="<?= $base_url ?>/public/images/thachsanh/thach_sanh.png" alt="Thạch Sanh">
            </div>
            
            <div id="enemies-container"></div>

            <div id="visual-feedback"></div>
        </div>
    </div>
    
    <div class="game-hints">
        <div class="hint-box">
            <i class="fas fa-trophy"></i>
            <div class="hint-content">
                <h4>Mẹo để đạt điểm cao:</h4>
                <ul>
                    <li>Quan sát nhanh và gõ chính xác</li>
                    <li>Ưu tiên tiêu diệt quái vật gần đất trước</li>
                    <li>Luyện tập để cải thiện tốc độ đánh máy</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="game-over-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 id="end-title">KẾT THÚC!</h2>
            <p id="end-message">Buôn làng đã bị tấn công.</p>
            <p class="final-pct">Điểm: <span id="final-score">0</span></p>
            <p class="final-pct">Tiến độ hoàn thành: <span id="final-pct">0%</span></p>
            <div class="modal-actions">
                <button id="replay-btn" class="game-btn">Chơi lại</button>
                <button id="back-btn" class="game-btn">Quay lại</button>
            </div>
        </div>
    </div>

    <div id="quick-complete-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Hoàn thành nhanh</h3>
            <p id="qc-msg" class="qc-msg">&nbsp;</p>
            <p>Điểm hiện tại: <strong><span id="qc-total">0</span></strong> / <strong><span id="qc-max">0</span></strong></p>
            <p>Tiến độ hoàn thành: <strong><span id="qc-pct">0%</span></strong></p>
            <div class="modal-actions">
                <button id="qc-replay" class="game-btn">Chơi lại</button>
                <button id="qc-back" class="game-btn">Quay lại</button>
            </div>
        </div>
    </div>
</div>

<script>
    const wordData = <?= json_encode($gameData) ?>;
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
</script>
<script src="<?= $base_url ?>/public/JS/typing_thach_sanh.js?v=<?= time() ?>" defer></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>