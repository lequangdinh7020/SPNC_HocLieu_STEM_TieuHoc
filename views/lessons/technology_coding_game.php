<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/coding_game.css?v=<?php echo time(); ?>">

<div id="story-modal" class="modal" style="display: none;">
    <div class="modal-content story-content">
        <img src="<?= $base_url ?>/public/images/coding/sontinh.png" class="story-avatar">
        <h2>Cốt truyện</h2>
        <p>"Vua Hùng kén rể, thách cưới với: <strong>Voi chín ngà, Gà chín cựa, Ngựa chín hồng mao</strong>.</p>
        <p>Thủy Tinh đang dâng nước đuổi theo sát nút! Các bạn hãy dùng <strong>'Phép thuật Lập trình'</strong> để giúp mình tìm đủ sính lễ trước khi nước lũ tràn về nhé!"</p>
        <button id="start-game-btn" class="game-btn run">Giúp Sơn Tinh ngay!</button>
    </div>
</div>

<div class="game-wrapper coding-game">
    <div class="header-game">
        <div class="level-info">
            <a href="<?= $base_url ?>/views/main_lesson.php" class="home-btn"><i class="fas fa-home"></i></a>
            <h1>Màn <?= $currentLevel['id'] ?>: <?= $currentLevel['title'] ?></h1>
            <div class="mission-badge">Mục tiêu: <?= $currentLevel['mission'] ?></div>
        </div>
        
        <div class="timer-container">
            <div class="timer-label">Thủy Tinh đang đến!</div>
            <div class="timer-bar-bg">
                <div id="timer-bar" class="timer-bar-fill"></div>
            </div>
            <img src="<?= $base_url ?>/public/images/coding/thuytinh_wave.png" id="wave-icon">
        </div>
    </div>
    
    <div id="game-area">
        
        <div id="block-sidebar">
            <h3>Phép thuật</h3>
            
            <div class="block-category">Di chuyển</div>
            <div class="command-block move" draggable="true" data-command="forward">
                <i class="fas fa-arrow-up"></i> Đi thẳng
            </div>
            <div class="command-block move" draggable="true" data-command="turn-left">
                <i class="fas fa-undo"></i> Quay trái
            </div>
            <div class="command-block move" draggable="true" data-command="turn-right">
                <i class="fas fa-redo"></i> Quay phải
            </div>

            <?php if (in_array('loop', $currentLevel['concepts'])): ?>
                <div class="block-category">Vòng lặp</div>
                <div class="command-block loop" draggable="true" data-command="repeat">
                    <i class="fas fa-sync"></i> Lặp lại (3 lần)
                </div>
            <?php endif; ?>

            <?php if (in_array('condition', $currentLevel['concepts'])): ?>
                <div class="block-category">Điều kiện</div>
                <div class="command-block condition" draggable="true" data-command="if-water">
                    <i class="fas fa-water"></i> Nếu gặp Nước -> Bắc Cầu
                </div>
            <?php endif; ?>
        </div>

        <div id="coding-space">
            <div class="coding-header">
                <h3>Sách phép thuật (<span id="block-count">0</span>/<?= $currentLevel['limit'] ?>)</h3>
                <button id="run-btn" class="game-btn run"><i class="fas fa-play"></i> Triển khai</button>
            </div>
            
            <div id="program-list" class="dropzone main-dropzone">
                <div class="placeholder-text">Kéo phép thuật vào đây để giúp Sơn Tinh</div>
            </div>
            
            <button id="reset-btn" class="game-btn reset"><i class="fas fa-trash"></i> Xóa phép</button>
        </div>

        <div id="stage-container">
            <div id="grid-map">
                </div>
        </div>

    </div>

    <div id="result-modal" class="modal">
        <div class="modal-content result-content">
            <div id="result-icon"></div>
            <h2 id="result-title"></h2>
            <p id="result-message"></p>
            <button id="next-level-btn" class="game-btn next">Màn tiếp theo</button>
            <button id="retry-btn" class="game-btn reset">Thử lại</button>
        </div>
    </div>
</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;
</script>
<script src="<?= $base_url ?>/public/JS/coding_game.js"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>