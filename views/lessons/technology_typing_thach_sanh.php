<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/typing_thach_sanh.css?v=<?= time() . rand(1000, 9999) ?>">

<div id="story-modal" class="modal">
    <div class="modal-content story-content">
        <h2>DŨNG SĨ DIỆT CHẰN TINH</h2>
        <p>Hỡi dũng sĩ <strong>Thạch Sanh</strong>! Hãy dùng bàn phím để bắn tên bảo vệ buôn làng.</p>
        <div class="instruction">
            <p><strong>Nhiệm vụ:</strong> Gõ các chữ cái trên người quái vật.</p>
            <p><strong>Cẩn thận:</strong> Đừng để quá 5 quái vật chạm đất!</p>
        </div>
        <div class="level-select">
            <button class="game-btn lvl-btn" onclick="startGame('easy')">Cấp độ 1: Cơ bản (Hàng A,S,D...)</button>
            <button class="game-btn lvl-btn hard" onclick="startGame('hard')">Cấp độ 2: Nâng cao (Từ vựng)</button>
        </div>
    </div>
</div>

<div class="game-wrapper thach-sanh-game" id="game-wrapper">
    <div class="game-hud">
        <div class="hud-item score-box">Điểm: <span id="score">0</span></div>
        
        <div class="hud-item time-box">Thời gian: <span id="time-display">03:00</span></div>
        
        <div class="hud-item lives-box">Mạng: <span id="lives">5</span> ❤️</div>
        <a href="<?= $base_url ?>/views/main_lesson.php" class="exit-btn"><i class="fas fa-sign-out-alt"></i> Thoát</a>
        <button id="quick-complete-btn" class="game-btn quick-complete">Hoàn thành nhanh</button>
    </div>

    <div id="game-stage">
        <div id="arrows-container"></div>
        
        <div id="thach-sanh">
            <img src="<?= $base_url ?>/public/images/thachsanh/thach_sanh.png" alt="Thạch Sanh">
        </div>
        
        <div id="enemies-container"></div>

        <div id="visual-feedback"></div>
    </div>

    <div id="game-over-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <h2 id="end-title">KẾT THÚC!</h2>
            <p id="end-message">Buôn làng đã bị tấn công.</p>
            <p class="final-pct">tiến độ hoàn thành: <span id="final-pct">0%</span></p>
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
            <p>tiến độ hoàn thành: <strong><span id="qc-pct">0%</span></strong></p>
            <div class="modal-actions">
                <button id="qc-replay" class="game-btn">Chơi lại</button>
                <button id="qc-back" class="game-btn">Quay lại</button>
            </div>
        </div>
    </div>
</div>

<script>
    const wordData = <?= json_encode($gameData) ?>;
</script>
<script src="<?= $base_url ?>/public/JS/typing_thach_sanh.js"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>