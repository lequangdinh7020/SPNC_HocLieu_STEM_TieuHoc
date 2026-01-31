<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/plant_game.css?v=<?php echo time(); ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/plants/plant_hoa_bg.png" alt="Plant Master" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn! Mình là Plant Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "LẮP GHÉP BỘ PHẬN CÂY". Nhiệm vụ của bạn là kéo các nhãn tên vào đúng vị trí trên cây. Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper plant-game">
    <div class="game-header-bar">
        <div class="header-left">
            <div class="menu-btn">
                <i class="fas fa-home"></i>
                <a href="<?= $base_url ?>/views/lessons/science.php">Menu</a>
            </div>
            <div class="reset-btn">
                <i class="fas fa-redo"></i>
                <button id="plantResetButton">Làm lại</button>
            </div>
        </div>
        <div class="header-center">
            <h1>LẮP GHÉP BỘ PHẬN CÂY</h1>
            <p class="game-subtitle">Kéo nhãn tên vào đúng vị trí</p>
        </div>
        <div class="header-right">
            <div class="progress-display">
                <span class="progress-label">Đúng</span>
                <span id="plantProgress" class="progress-value">0/0</span>
            </div>
        </div>
    </div>

    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb instruction-icon"></i>
            <h3>Hướng dẫn chơi</h3>
            <p>Hãy kéo các nhãn tên bộ phận cây vào đúng vị trí trên hình ảnh. Mỗi bộ phận đúng sẽ được cố định tại vị trí.</p>
        </div>
    </div>

    <div id="userFeedback"></div>
    <button id="plantFinishButton" class="finish-btn" style="display: block; margin: 10px auto;">Hoàn thành</button>

    <div id="plantGameContainer">

        <div id="partsBank">
            <h3>Các bộ phận:</h3>
            <?php foreach ($plantData['parts'] as $part): ?>
                <div class="draggable-label"
                    draggable="true"
                    id="<?= $part['id'] ?>"
                    data-part-name="<?= $part['name'] ?>"
                    data-attempt="1">
                    <?= $part['text'] ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="plantTarget">
            <img src="<?= $base_url ?>/public/images/plants/<?php echo $plantData['image_bg']; ?>" alt="<?php echo $plantData['title']; ?>" class="plant-image-bg">

            <?php foreach ($plantData['dropzones'] as $zone): ?>
                <div class="dropzone"
                    data-target-part="<?= $zone['target'] ?>"
                    style="top: <?= $zone['top'] ?>; left: <?= $zone['left'] ?>; width: <?= $zone['width'] ?>; height: <?= $zone['height'] ?>;">
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <div class="final-result" id="finalResult">
        <h2>Kết quả cuối cùng</h2>
        <div class="score-circle">
            <p class="final-score" id="finalScore">0</p>
            <span class="score-label">Đúng</span>
        </div>
        <div class="result-actions">
            <button class="play-again" onclick="location.reload()">Chơi lại</button>
            <a href="<?= $base_url ?>/views/lessons/science.php" class="back-to-lessons">Về bài học</a>
        </div>
    </div>

    <div class="game-hints">
        <h3><i class="fas fa-trophy"></i> Mẹo để đạt điểm cao</h3>
        <ul>
            <li>Quan sát kỹ hình dạng và vị trí của từng bộ phận</li>
            <li>Bộ phận đúng sẽ được giữ lại ở vị trí</li>
            <li>Bộ phận sai sẽ trở về kho</li>
        </ul>
    </div>
</div>

<script>
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
    window.gameName = window.gameName || "<?= addslashes($plantData['title']) ?>";
    window.nextPlantType = <?= json_encode($nextType) ?>;
    window.prevPlantType = <?= json_encode($prevType ?? null) ?>;
    window.currentPlantType = <?= json_encode($plantType) ?>;
</script>
<script src="<?= $base_url ?>/public/JS/plant_game.js"></script>

<!-- Win modal for plant progression -->
<div id="win-modal" class="modal" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:#fff; padding:1.2rem; max-width:520px; width:90%; border-radius:8px; text-align:center;">
        <button id="close-modal-btn" style="float:right; background:none; border:none; font-size:1.1rem;">✖</button>
        <h2>🎉 Hoàn thành!</h2>
        <p>Bạn đã ghép xong loại cây này.</p>
        <div style="margin-top:1rem; display:flex; gap:.5rem; justify-content:center;">
            <button id="next-level-btn" style="display:none; background:#2ecc71; color:#fff; padding:.6rem 1rem; border-radius:6px; border:none;">Chơi tiếp</button>
            <button id="replay-all-btn" style="display:none; background:#3498db; color:#fff; padding:.6rem 1rem; border-radius:6px; border:none;">Chơi lại từ đầu</button>
            <button id="back-to-lessons-btn" style="display:none; background:#3498db; color:#fff; padding:.6rem 1rem; border-radius:6px; border:none;">Quay lại</button>
        </div>
    </div>
</div>

<script>
    (function() {
        var backBtn = document.querySelector('.back-button');
        var prevType = window.prevPlantType || null;
        if (backBtn) {
            if (prevType) {
                backBtn.setAttribute('href', window.baseUrl + '/views/lessons/science_plant_game?type=' + encodeURIComponent(prevType));
            } else {
                backBtn.setAttribute('href', window.baseUrl + '/views/lessons/science.php');
            }
        }
    })();
</script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>