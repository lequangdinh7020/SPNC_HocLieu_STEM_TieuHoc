<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/painter_game.css?v=<?php echo time(); ?>">

<div class="game-wrapper painter-game">
    <div class="header-game">
        <div class="left-controls">
            <a href="<?= $base_url ?>/views/main_lesson.php" class="home-btn" id="home-btn"><i class="fas fa-home"></i></a>
            <button id="submit-btn" class="submit-btn" title="Nộp bài">Nộp bài</button>
        </div>

        <h1>Em làm họa sĩ: <?= $currentConfig['title'] ?></h1>
        
        <div class="timer-box">
            <i class="fas fa-clock"></i> <span id="time-display">05:00</span>
        </div>
    </div>

    <div class="workspace">
        
        <div class="sidebar-tools">
            <div class="tool-group">
                <label>Hình khối:</label>
                <button class="tool-btn" data-tool="line" title="Đường thẳng">📏</button>
                <button class="tool-btn" data-tool="rect" title="Hình chữ nhật">⬜</button>
                <button class="tool-btn" data-tool="circle" title="Hình tròn">⭕</button>
                <button class="tool-btn" data-tool="triangle" title="Hình tam giác">🔺</button>
            </div>

            <div class="tool-group">
                <label>Công cụ:</label>
                
                <button class="tool-btn active" data-tool="brush" title="Bút chì">
                    <img src="<?= $base_url ?>/public/images/painter/pencil.png" alt="Bút chì">
                </button>
                
                <button class="tool-btn" data-tool="eraser" title="Tẩy">
                    <img src="<?= $base_url ?>/public/images/painter/eraser.png" alt="Tẩy">
                </button>
                
                <button class="tool-btn" data-tool="bucket" title="Đổ màu">
                    <img src="<?= $base_url ?>/public/images/painter/bucket.png" alt="Đổ màu">
                </button>

                <button class="tool-btn action-btn" id="undo-btn" title="Hoàn tác">
                    <img src="<?= $base_url ?>/public/images/painter/undo.png" alt="Hoàn tác">
                </button>
            </div>

            <div class="tool-group">
                <label>Nét vẽ:</label>
                <input type="range" id="size-slider" min="1" max="20" value="5">
            </div>

            <div class="tool-group action-group">
                <button id="clear-btn" class="tool-btn" title="Xóa hết">
                     <img src="<?= $base_url ?>/public/images/painter/trash.png" alt="Xóa hết">
                </button>
                
                <button id="save-btn" title="Lưu tranh">💾 Lưu</button>
            </div>
        </div>

        <div class="canvas-area">
            <canvas id="drawing-canvas" width="800" height="500"></canvas>
        </div>

        <div class="sidebar-right">
            <div class="color-palette">
                <div class="colors-grid">
                    <div class="color-swatch selected" style="background: #000000;" data-color="#000000"></div>
                    <div class="color-swatch" style="background: #ffffff;" data-color="#ffffff"></div>
                    <div class="color-swatch" style="background: #ff0000;" data-color="#ff0000"></div>
                    <div class="color-swatch" style="background: #ff7f00;" data-color="#ff7f00"></div>
                    <div class="color-swatch" style="background: #ffff00;" data-color="#ffff00"></div>
                    <div class="color-swatch" style="background: #00ff00;" data-color="#00ff00"></div>
                    <div class="color-swatch" style="background: #0000ff;" data-color="#0000ff"></div>
                    <div class="color-swatch" style="background: #4b0082;" data-color="#4b0082"></div>
                    <div class="color-swatch" style="background: #ff69b4;" data-color="#ff69b4"></div>
                    <div class="color-swatch" style="background: #8b4513;" data-color="#8b4513"></div>
                    <input type="color" id="color-picker" value="#000000">
                </div>
            </div>

            <div class="topic-selector">
                <label>Chủ đề</label>
                <div class="topic-list">
                    <a href="?topic=free" class="topic-btn <?= $topic == 'free' ? 'active' : '' ?>">
                        <img src="<?= $base_url ?>/public/images/painter/icon_free.png" alt="Tự vẽ">
                        <span>Tự vẽ</span>
                    </a>
                    <a href="?topic=house" class="topic-btn <?= $topic == 'house' ? 'active' : '' ?>">
                        <img src="<?= $base_url ?>/public/images/painter/icon_house.png" alt="Ngôi nhà">
                        <span>Ngôi nhà</span>
                    </a>
                    <a href="?topic=animal" class="topic-btn <?= $topic == 'animal' ? 'active' : '' ?>">
                        <img src="<?= $base_url ?>/public/images/painter/icon_animal.png" alt="Động vật">
                        <span>Động vật</span>
                    </a>
                    <a href="?topic=computer" class="topic-btn <?= $topic == 'computer' ? 'active' : '' ?>">
                        <img src="<?= $base_url ?>/public/images/painter/icon_computer.png" alt="Máy tính">
                        <span>Máy tính</span>
                    </a>
                    <a href="?topic=nature" class="topic-btn <?= $topic == 'nature' ? 'active' : '' ?>">
                        <img src="<?= $base_url ?>/public/images/painter/icon_nature.png" alt="Thiên nhiên">
                        <span>Thiên nhiên</span>
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    window.baseUrl = "<?= $base_url ?>";

    const bgImageName = "<?= $currentConfig['bg_image'] ?>";
    const timeLimit = <?= $timeLimit ?>;
</script>
<script src="<?= $base_url ?>/public/JS/painter_game.js"></script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>