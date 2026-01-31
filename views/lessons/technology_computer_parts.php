<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/computer_parts_game.css?v=<?php echo time(); ?>">

<div class="game-wrapper computer-game">
    <h1>Trò chơi: Các bộ phận của máy tính</h1>
    <p>Hãy kéo các bộ phận vào đúng vị trí của chúng trên bàn máy tính nhé!</p>
    <div id="game-feedback"></div>
    <hr>

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

            <div class="dropzone" data-target="monitor"    style="top: 18%; left: 23.5%; width: 38.1%; height: 45.9%;"></div>
            <div class="dropzone" data-target="case"       style="top: 22.5%; left: 64.5%; width: 20%; height: 41%;"></div>
            <div class="dropzone" data-target="printer"    style="top: 41.5%; left: 3.2%;  width: 20%; height: 22.3%;"></div>
            <div class="dropzone" data-target="keyboard"   style="top: 68.85%; left: 22.2%; width: 42.1%; height: 17%;"></div>
            <div class="dropzone" data-target="mouse"      style="top: 69.4%; left: 66%; width: 6.5%;  height: 16.8%;"></div>
            <div class="dropzone" data-target="speaker"    style="top: 43%; left: 86.4%; width: 10.2%;  height: 20.2%;"></div>
            <div class="dropzone" data-target="microphone" style="top: 69%; left: 74.6%; width: 8.4%;  height: 17.8%;"></div>
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
    // Truyền dữ liệu sang JS
    const baseUrl = "<?= $base_url ?>";
    const totalParts = <?= count($computerParts) ?>;
</script>
<script src="<?= $base_url ?>/public/JS/computer_parts_game.js"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>