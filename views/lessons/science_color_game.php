<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/color_mixing_game.css?v=<?php echo time(); ?>"> 
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Modal Overlay -->
<div class="modal-overlay active" id="introModal">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/painter.png" alt="Color Mixing Character" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Trò Chơi Pha Màu 🎨</h3>
            <p>Hãy trộn các màu cơ bản để tạo ra màu đích! Thử thách khả năng quan sát màu sắc của bạn.</p>
            <button id="startGameButton" class="start-btn">
                <i class="fas fa-play"></i> Bắt đầu chơi
            </button>
        </div>
    </div>
</div>

<div class="game-wrapper color-game-wrapper">
    <!-- Game Header -->
    <div class="game-header">
        <h1>Trò Chơi Pha Màu</h1>
        <p class="game-subtitle">Khám phá thế giới màu sắc qua việc trộn màu</p>
    </div>
    
    <!-- Game Stats -->
    <div class="game-stats">
        <div class="stat-box correct">
            <span class="stat-label"><i class="fas fa-star"></i> Điểm Số</span>
            <span class="stat-value" id="totalScore"><?= $_SESSION['total_score'] ?></span>
        </div>
    </div>
    
    <!-- Game Controls -->
    <div class="game-controls">
        <a href="<?= $base_url ?>/views/lessons/science.php" class="control-btn give-up">
            <i class="fas fa-arrow-left"></i> Quay về
        </a>
        <button id="resetGameButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Chơi lại
        </button>
        <button id="completeButton" class="control-btn complete">
            <i class="fas fa-check"></i> Kết thúc
        </button>
    </div>
    
    <!-- Game Instructions -->
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span>Chọn các màu trong bảng màu và trộn chúng trên canvas để tạo ra màu mục tiêu. Sử dụng tỷ lệ màu phù hợp để đạt kết quả chính xác!</span>
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