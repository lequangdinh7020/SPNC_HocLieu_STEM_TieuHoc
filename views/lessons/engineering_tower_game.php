<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $currentLevel['title'] ?> - STEM Universe</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/main.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/tower_game.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/matter-js/0.19.0/matter.min.js"></script>
</head>
<body>
    <div class="game-wrapper tower-game-mode">
        <div class="game-ui-layer">
            <div class="header-game">
                <a href="<?= $base_url ?>/views/main_lesson.php" class="home-btn"><i class="fas fa-home"></i></a>
                <h1><?= $currentLevel['title'] ?></h1>
            </div>
            
            <div id="result-modal" class="modal">
                <div class="modal-content">
                    <h2>HOÀN THÀNH!</h2>
                    <p>Bạn đã chinh phục thử thách!</p>
                    <div class="modal-buttons">
                        <?php if ($currentLevel['id'] < ($totalLevels ?? 1)): ?>
                            <a href="engineering_tower_game?level=<?= $currentLevel['id'] + 1 ?>" class="game-btn next">Tiếp theo</a>
                        <?php else: ?>
                            <button onclick="window.location.reload()" class="game-btn">Chơi lại</button>
                        <?php endif; ?>
                        
                        <a href="<?= $base_url ?>/views/main_lesson.php" class="game-btn home-btn-ui">Về Trang Chủ</a>
                    </div>
                </div>
            </div>
            <div id="lose-modal" class="modal">
            <div class="modal-content lose-content">
                <h2>CẤU TRÚC ĐÃ GÃY!</h2>
                <p>Tháp không chịu nổi lực căng và đã sụp đổ.</p>
                <div class="modal-buttons">
                    <button onclick="window.location.reload()" class="game-btn reset">Chơi lại</button>
                    <a href="<?= $base_url ?>/views/main_lesson.php" class="game-btn home-btn-ui">Về Trang Chủ</a>
                </div>
            </div>
        </div>
        </div>
        
        <div id="physics-container"></div>

        <div class="build-toolbar">
            <div class="node-inventory" id="node-source">
                <div class="node-icon"></div>
                <div class="node-count">
                    <span>x</span><span id="remaining-nodes"><?= $currentLevel['config']['freeNodes'] ?></span>
                </div>
                <div class="tooltip">Kéo thả vào màn hình</div>
            </div>
            
            <button id="reset-btn" class="tool-btn reset" title="Xóa làm lại">
                <i class="fas fa-undo"></i>
            </button>
        </div>

        <div id="drag-ghost" class="node-ghost"></div>
    </div>

    <script>
        const levelConfig = <?= json_encode($currentLevel['config']) ?>;
        const currentLevelId = <?= $currentLevel['id'] ?>;
        const totalTowerLevels = <?= $totalLevels ?? 1 ?>;
        const baseUrl = '<?= $base_url ?>';
    </script>
    <script src="<?= $base_url ?>/public/JS/tower_game.js"></script>
</body>
</html>