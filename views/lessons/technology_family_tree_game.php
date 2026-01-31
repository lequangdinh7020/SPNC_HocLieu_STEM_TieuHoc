<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/family_tree_game.css?v=<?php echo time(); ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/family_tree/an.png" alt="Family Master" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn! Mình là Family Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "XÂY DỰNG CÂY GIA PHỄ". Nhiệm vụ của bạn là kéo các thành viên vào đúng vị trí trên cây gia phổ dựa vào gợi ý. Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper family-tree-game">
    <div class="game-header-bar">
        <div class="header-left">
            <div class="menu-btn">
                <i class="fas fa-home"></i>
                <a href="<?= $base_url ?>/views/main_lesson.php">Menu</a>
            </div>
            <button id="resetButton" class="reset-btn">
                <i class="fas fa-redo"></i>
                Làm lại
            </button>
        </div>
        <div class="header-center">
            <h1>Cấp độ <span id="current-level-display"><?= $currentLevel['id'] ?></span>: <?= $currentLevel['level_title'] ?></h1>
            <p class="game-subtitle">Xây dựng cây gia phổ hoàn chỉnh</p>
        </div>
        <div class="header-right">
            <div class="lives-display" id="lives-container">
                <span class="lives-label">Mạng</span>
                <div class="hearts">
                    <i class="fas fa-heart live"></i><i class="fas fa-heart live"></i><i class="fas fa-heart live"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb instruction-icon"></i>
            <h3>Hướng dẫn chơi</h3>
            <p>Kéo các thành viên trong gia đình vào đúng vị trí trên cây gia phổ dựa vào các gợi ý. Mỗi lần sai bạn sẽ mất 1 trái tim.</p>
        </div>
    </div>

    <div id="userFeedback"></div>

    <div id="game-area">
        <div id="tree-canvas">

            <?php if ($currentLevel['layout_type'] == 'type_2p_3c_fixed' || $currentLevel['layout_type'] == 'type_2p_3c_fixed_dad'): ?>
                <div class="person-node current-char" style="top: 20px; left: calc(50% - 110px);">
                    <img src="<?= $base_url ?>/public/images/family_tree/<?= $currentLevel['fixed_chars']['parent1']['id'] ?>.png">
                    <span class="char-name"><?= $currentLevel['fixed_chars']['parent1']['name'] ?></span>
                </div>
                <div class="person-node drop-slot" id="slot-parent2" data-slot-id="parent2" style="top: 20px; left: calc(50% + 20px);"></div>

                <div class="line horizontal" style="top: 65px; left: calc(50% - 65px); width: 130px;"></div>
                <div class="line vertical" style="top: 65px; left: calc(50% - 1.5px); height: 105px;"></div>
                <div class="line horizontal" style="top: 170px; left: calc(50% - 180px); width: 362px;"></div>
                <div class="line vertical" style="top: 170px; left: calc(50% - 180px); height: 50px;"></div>
                <div class="line vertical" style="top: 170px; left: calc(50% - 1.5px); height: 50px;"></div>
                <div class="line vertical" style="top: 170px; left: calc(50% + 180px); height: 50px;"></div>

                <div class="person-node drop-slot" id="slot-child1" data-slot-id="child1" style="top: 200px; left: calc(50% - 225px);"></div>
                <div class="person-node drop-slot" id="slot-child2" data-slot-id="child2" style="top: 200px; left: calc(50% - 45px);"></div>
                <div class="person-node drop-slot" id="slot-child3" data-slot-id="child3" style="top: 200px; left: calc(50% + 135px);"></div>

            <?php elseif ($currentLevel['layout_type'] == 'type_2p_2c'): ?>
                <div class="person-node drop-slot" id="slot-parent1" data-slot-id="parent1" style="top: 20px; left: calc(50% - 110px);"></div>
                <div class="person-node drop-slot" id="slot-parent2" data-slot-id="parent2" style="top: 20px; left: calc(50% + 20px);"></div>

                <div class="line horizontal" style="top: 65px; left: calc(50% - 65px); width: 130px;"></div>
                <div class="line vertical" style="top: 65px; left: calc(50% - 1.5px); height: 107px;"></div>
                <div class="line horizontal" style="top: 170px; left: calc(50% - 100px); width: 203px;"></div>
                <div class="line vertical" style="top: 170px; left: calc(50% - 100px); height: 50px;"></div>
                <div class="line vertical" style="top: 170px; left: calc(50% + 100px); height: 50px;"></div>

                <div class="person-node drop-slot" id="slot-child1" data-slot-id="child1" style="top: 200px; left: calc(50% - 145px);"></div>
                <div class="person-node drop-slot" id="slot-child2" data-slot-id="child2" style="top: 200px; left: calc(50% + 55px);"></div>

            <?php elseif ($currentLevel['layout_type'] == 'type_vertical_3gen'): ?>
                <div class="person-node drop-slot" id="slot-gen1" data-slot-id="gen1" style="top: 20px; left: calc(50% - 45px);"></div>
                <div class="line vertical" style="top: 110px; left: calc(50% - 1.5px); height: 50px;"></div>
                <div class="person-node drop-slot" id="slot-gen2" data-slot-id="gen2" style="top: 160px; left: calc(50% - 45px);"></div>
                <div class="line vertical" style="top: 250px; left: calc(50% - 1.5px); height: 50px;"></div>
                <div class="person-node drop-slot" id="slot-gen3" data-slot-id="gen3" style="top: 300px; left: calc(50% - 45px);"></div>

            <?php elseif ($currentLevel['layout_type'] == 'type_2p_3c'): ?>
                <div class="person-node drop-slot" id="slot-parent1" data-slot-id="parent1" style="top: 20px; left: calc(50% - 110px);"></div>
                <div class="person-node drop-slot" id="slot-parent2" data-slot-id="parent2" style="top: 20px; left: calc(50% + 20px);"></div>
                <div class="line horizontal" style="top: 65px; left: calc(50% - 65px); width: 130px;"></div>
                <div class="line vertical" style="top: 65px; left: calc(50% - 1.5px); height: 55px;"></div>
                <div class="line horizontal" style="top: 120px; left: calc(50% - 180px); width: 360px;"></div>
                <div class="line vertical" style="top: 120px; left: calc(50% - 180px); height: 30px;"></div>
                <div class="line vertical" style="top: 120px; left: calc(50% - 1.5px); height: 30px;"></div>
                <div class="line vertical" style="top: 120px; left: calc(50% + 180px); height: 30px;"></div>
                <div class="person-node drop-slot" id="slot-child1" data-slot-id="child1" style="top: 150px; left: calc(50% - 225px);"></div>
                <div class="person-node drop-slot" id="slot-child2" data-slot-id="child2" style="top: 150px; left: calc(50% - 45px);"></div>
                <div class="person-node drop-slot" id="slot-child3" data-slot-id="child3" style="top: 150px; left: calc(50% + 135px);"></div>

            <?php elseif ($currentLevel['layout_type'] == 'type_3gen_complex'): ?>
                <div class="person-node drop-slot" id="slot-gen1_p1" data-slot-id="gen1_p1" style="top: 20px; left: calc(50% - 110px);"></div>
                <div class="person-node drop-slot" id="slot-gen1_p2" data-slot-id="gen1_p2" style="top: 20px; left: calc(50% + 20px);"></div>

                <div class="line horizontal" style="top: 65px; left: calc(50% - 65px); width: 132px;"></div>
                <div class="line vertical" style="top: 65px; left: calc(50% - 1.5px); height: 95px;"></div>

                <div class="line horizontal" style="top: 160px; left: calc(50% - 100px); width: 202px;"></div>
                <div class="line vertical" style="top: 160px; left: calc(50% - 100px); height: 50px;"></div>
                <div class="line vertical" style="top: 160px; left: calc(50% + 100px); height: 50px;"></div>

                <div class="person-node drop-slot" id="slot-gen2_c1" data-slot-id="gen2_c1" style="top: 200px; left: calc(50% - 145px);"></div>
                <div class="person-node drop-slot" id="slot-gen2_c2" data-slot-id="gen2_c2" style="top: 200px; left: calc(50% + 55px);"></div>
                <div class="person-node drop-slot" id="slot-gen2_spouse" data-slot-id="gen2_spouse" style="top: 200px; left: calc(50% + 175px);"></div>

                <div class="line horizontal" style="top: 235px; left: calc(50% + 100px); width: 110px;"></div>
                <div class="line vertical" style="top: 235px; left: calc(50% + 160px); height: 105px;"></div>

                <div class="person-node drop-slot" id="slot-gen3_c1" data-slot-id="gen3_c1" style="top: 325px; left: calc(50% + 115px);"></div> <?php endif; ?>
        </div>

        <div id="character-bank-container">
            <div id="clues-box">
                <h2>Gợi ý</h2>
                <ul>
                    <?php foreach ($currentLevel['clues'] as $clue): ?>
                        <li><?= $clue ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div id="character-bank">
                <?php foreach ($currentLevel['available_characters'] as $charId): ?>
                    <div class="draggable-char" data-char-id="<?= $charId ?>" draggable="true">
                        <img src="<?= $base_url ?>/public/images/family_tree/<?= $charId ?>.png" alt="<?= $charId ?>">
                        <span class="char-name"><?= mb_convert_case($charId, MB_CASE_TITLE, "UTF-8") ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="final-result" id="finalResult">
        <h2>Kết quả cuối cùng</h2>
        <div class="score-circle">
            <p class="final-score" id="finalLevelDisplay">1</p>
            <span class="score-label">Cấp độ</span>
        </div>
        <div class="result-actions">
            <button class="play-again" onclick="location.reload()">Chơi lại</button>
            <a href="<?= $base_url ?>/views/main_lesson.php" class="back-to-lessons">Về bài học</a>
        </div>
    </div>

    <div class="game-hints">
        <h3><i class="fas fa-trophy"></i> Mẹo để đạt điểm cao</h3>
        <ul>
            <li>Đọc kỹ các gợi ý để xác định quan hệ gia đình</li>
            <li>Chú ý các từ "bố, mẹ, con, anh, chị, em" trong gợi ý</li>
            <li>Kéo thả thử và quan sát phản hồi để học hỏi</li>
        </ul>
    </div>

    <div id="game-feedback" class="feedback-message"></div>

    <div id="game-over-modal" class="modal">
        <div class="modal-content-over">
            <h2 id="modal-title">Kết quả</h2>
            <p id="modal-message">Thông báo...</p>

            <button id="next-level-btn" class="game-btn" style="display: none;">Cấp độ tiếp theo</button>

            <button id="restart-game-btn" class="game-btn">Chơi lại</button>
            <button id="back-to-menu-btn" class="game-btn" style="margin-top:8px;">Quay về Menu</button>
            <button id="back-to-technology-btn" class="game-btn" style="display:none; margin-top:8px;">Quay lại</button>
        </div>
    </div>
</div>

<script>
    const currentLevelData = <?= json_encode($currentLevel) ?>;
    const totalGameLevels = <?= $totalLevels ?>;
</script>
<script src="<?= $base_url ?>/public/JS/family_tree_game.js?v=<?php echo time(); ?>"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>