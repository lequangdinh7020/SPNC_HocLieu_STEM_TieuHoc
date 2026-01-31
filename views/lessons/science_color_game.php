<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/color_mixing_game.css?v=<?php echo time(); ?>"> 
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">

<div class="color-game-wrapper"> 
    <h1>TRÒ CHƠI PHA MÀU</h1>
    
    <div class="controls-section">
        <div class="score-box">Điểm của bạn: <span id="totalScore"><?= $_SESSION['total_score'] ?></span></div>
        
        <div class="button-row">
            <a href="<?= $base_url ?>/views/lessons/science.php" class="menu-btn">Menu</a>
            <button id="resetGameButton" class="reset-btn">Chơi lại</button>
            <button id="completeButton" class="complete-btn">Kết thúc</button>
        </div>
    </div>

    <?php if ($target): ?>
        <p class="question"><?= $target['text'] ?></p>
        <div class="selected"><p>Màu đã chọn:</p><div id="selectedColors"></div></div>
        
        <div class="game-layout">
            <!-- Bên trái: Màu cần pha và bảng màu -->
            <div class="palette-section">
                <div class="target">
                    <span>Màu cần pha:</span>
                    <div class="color-target" style="background-color: rgb(<?= implode(',', $target['rgb']) ?>);"></div>
                </div>
                <div class="palette">
                    <div class="color" data-color="red" style="background:red;"></div>
                    <div class="color" data-color="yellow" style="background:yellow;"></div>
                    <div class="color" data-color="blue" style="background:blue;"></div>
                    <div class="color" data-color="white" style="background:white;"></div>
                    <div class="color" data-color="black" style="background:black;"></div>
                </div>
            </div>
            
            <!-- Bên phải: Khung pha màu -->
            <div class="canvas-section">
                <canvas id="mixCanvas" width="400" height="250"></canvas>
            </div>
        </div>
        
        <div id="hintBox"></div>
        <div id="result"></div>
        <div class="controls">
            <a href="<?= $base_url ?>/science/color-game?next=1" id="nextButton" style="display:none;">Câu hỏi tiếp theo ➡️</a>
        </div>

        <script>
            const targetColor = <?= json_encode($target['rgb']) ?>;
            const correctPair = <?= json_encode($correct_colors_sorted) ?>;
            let currentAttempt = <?= $current_attempt ?>;
        </script>
        
        <script src="<?= $base_url ?>/public/JS/color_mixing_game.js"></script>

    <?php else: ?>
        <p class="question">Trò chơi đã kết thúc!</p>
        <?php
            if (isset($completionResult) && is_array($completionResult)) {
                if (!empty($completionResult['success'])) {
                    if (!empty($completionResult['completed'])) {
                        echo '<p class="completed-msg">🎉 Bạn đã hoàn thành trò chơi! Tiến độ +1.</p>';
                    } else {
                        $need = isset($passingThreshold) ? htmlspecialchars($passingThreshold) . '%' : '25%';
                        echo '<p class="incomplete-msg">⚠️ Bạn chưa đạt điểm tối thiểu để hoàn thành trò chơi (cần ' . $need . ').</p>';
                    }
                } else {
                    echo '<p class="error-msg">Có lỗi khi lưu điểm: ' . htmlspecialchars($completionResult['message'] ?? '') . '</p>';
                }
            }
        ?>
        <script>
            (function(){
                const resetBtn = document.getElementById('resetGameButton');
                if (resetBtn) {
                    resetBtn.addEventListener('click', function(e){
                        e.preventDefault();
                        window.location.href = `${baseUrl}/science/color-game?next=1`;
                    });
                }
            })();
        </script>
        
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../template/footer.php'; ?>