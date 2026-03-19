<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/coding_game.css?v=<?= time() ?>">

<div id="story-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/coding/sontinh.png" alt="Sơn Tinh" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Tráng sĩ Sơn Tinh</h3>
            <p id="storyText">Chào các bạn nhỏ! Vua Hùng đang kén rể cho công chúa Mị Nương xinh đẹp. Nhà vua đưa ra một thử thách vô cùng khó khăn: Ai mang được sính lễ gồm "voi chín ngà, gà chín cựa, ngựa chín hồng mao" đến trước sẽ được rước công chúa về dinh.</p>
            <button id="nextStoryButton" class="start-btn">Tiếp theo</button>
        </div>
    </div>
</div>

<div class="game-wrapper coding-game"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI LẬP TRÌNH SƠN TINH</h1>
        <p class="game-subtitle">Màn <?= $currentLevel['id'] ?>: <?= $currentLevel['title'] ?></p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label">MÀN</span>
            <span class="stat-value"><?= $currentLevel['id'] ?></span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">THỜI GIAN</span>
            <span id="timer-display" class="stat-value">00:00</span>
            <div id="timer-bar" style="display: none;"></div>
        </div>
        <div class="stat-box progress">
            <span class="stat-label">KHỐI LỆNH</span>
            <span class="stat-value"><span id="block-count">0</span>/<?= $currentLevel['limit'] ?></span>
        </div>
    </div>
    
    <div class="game-controls">
        <a href="<?= $base_url ?>/views/main_lesson.php" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </a>
        <button id="reset-btn" class="control-btn reset">
            <i class="fas fa-trash"></i> Xóa phép
        </button>
        <button id="run-btn" class="control-btn complete">
            <i class="fas fa-play"></i> Triển khai
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Nhiệm vụ:</strong> <?= $currentLevel['mission'] ?></span>
        </div>
    </div>
    
    <div class="game-container coding-container">
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
                    <h3>Sách phép thuật</h3>
                </div>
                
                <div id="program-list" class="dropzone main-dropzone">
                    <div class="placeholder-text">Kéo phép thuật vào đây để giúp Sơn Tinh</div>
                </div>
            </div>

            <div id="stage-container">
                <div id="grid-map">
                </div>
            </div>

        </div>
    </div>
    
    <div class="game-hints">
        <div class="hint-box">
            <i class="fas fa-trophy"></i>
            <div class="hint-content">
                <h4>Mẹo để hoàn thành:</h4>
                <ul>
                    <li>Sử dụng vòng lặp để giảm số lượng khối lệnh</li>
                    <li>Quan sát kỹ bản đồ trước khi lập trình</li>
                    <li>Kiểm tra hướng của Sơn Tinh trước khi di chuyển</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="result-modal" class="modal">
        <div class="modal-content result-content">
            <div id="result-icon"></div>
            <h2 id="result-title"></h2>
            <p id="result-message"></p>
            <div class="modal-actions">
                <button id="retry-btn" class="game-btn reset">Thử lại</button>
                <button id="next-level-btn" class="game-btn next">Màn tiếp theo</button>
            </div>
        </div>
    </div>
</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
</script>
<script src="<?= $base_url ?>/public/JS/coding_game.js?v=<?= time() ?>" defer></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>