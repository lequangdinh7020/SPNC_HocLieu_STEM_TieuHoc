<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học Xem Giờ - STEM Universe</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/main.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/time_game.css?v=<?php echo time(); ?>">
    
    <style>
        body { background: #ffecd2; overflow: hidden; font-family: 'Fredoka', sans-serif; }
    </style>
</head>
<body>

    <div class="game-wrapper time-game">
        
        <div class="header-game">
            <a href="<?= $base_url ?>/views/main_lesson.php" class="home-btn"><i class="fas fa-home"></i></a>
            <div>
                <h1><?= $currentLevel['title'] ?></h1>
                <p class="subtitle"><?= $currentLevel['desc'] ?></p>
            </div>
            <div class="score-board">Câu: <span id="q-current">1</span>/<span id="q-total">5</span></div>
        </div>

        <div class="game-container">
            
            <div class="digital-clock-panel">
                <h3>Hãy chỉnh đồng hồ thành:</h3>
                <div class="digital-display">
                    <span id="target-hour">00</span>
                    <span class="colon">:</span>
                    <span id="target-minute">00</span>
                </div>
                <div class="mascot">
                </div>
            </div>

            <div class="analog-clock-panel">
                <canvas id="clockCanvas" width="400" height="400"></canvas>
            </div>
        </div>

        <div class="controls">
            <button id="check-btn" class="game-btn check">Kiểm Tra</button>
            <button id="complete-btn" class="game-btn check">Hoàn Thành</button>
        </div>
        
        <div id="result-modal" class="modal">
            <div class="modal-content">
                <h2 id="modal-title"></h2>
                <p id="modal-message"></p>
                <div id="modal-actions">
                    <button id="next-btn" class="game-btn">Tiếp tục</button>
                    <button id="play-again-btn" class="game-btn" style="display:none;">Chơi lại</button>
                    <button id="back-btn" class="game-btn" style="display:none;">Quay lại</button>
                </div>
            </div>
        </div>

    </div>

    <script>
        const baseUrl = "<?= $base_url ?>";
        const levelData = <?= json_encode($currentLevel) ?>;
        const totalGameLevels = <?= $totalLevels ?>;
    </script>
    <script src="<?= $base_url ?>/public/JS/time_game.js"></script>

</body>
</html>