<?php require_once __DIR__ . '/../template/header.php'; ?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/math_angle_game.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/angle_game/HauNghe.png" alt="Hậu Nghệ" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Tráng sĩ Hậu Nghệ</h3>
            <p id="storyText">Chào các bạn nhỏ! Bầu trời hôm nay bỗng nhiên xuất hiện tới 4 ông mặt trời rực lửa. Cây cối héo úa, sông suối cạn khô, muôn loài đang vô cùng khát nước và nóng bức. Tráng sĩ Hậu Nghệ với sức mạnh phi thường đã mang cây cung thần ra để bắn hạ 3 ông mặt trời, cứu lấy Trái Đất. Để mũi tên bay trúng đích, Hậu Nghệ phải căn chỉnh Góc bắn của cây cung thật chuẩn xác. Hậu Nghệ đang mồ hôi nhễ nhại và rất cần các "chiến binh toán học" nhí giúp ngắm bắn đấy!</p>
            <button id="nextStoryButton" class="start-btn">Tiếp theo</button>
        </div>
    </div>
</div>

<div class="game-wrapper angle-game"><br><br><br>
    <div class="game-header">
        <h1>Màn <?= $currentLevel['id'] ?>: <?= $currentLevel['title'] ?></h1>
        <p class="game-subtitle">Thử thách toán học - Rèn luyện tư duy góc</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box angle">
            <span class="stat-label">GÓC BẮN</span>
            <span id="angle-value" class="stat-value">0°</span>
        </div>
        <div class="stat-box type">
            <span class="stat-label">LOẠI GÓC</span>
            <span id="angle-type" class="stat-value">...</span>
        </div>
        <div class="stat-box level">
            <span class="stat-label">MÀN CHƠI</span>
            <span class="stat-value"><?= $currentLevel['id'] ?>/<?= $totalLevels ?></span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </button>
        <button id="resetButton" class="control-btn reset">
            <i class="fas fa-redo"></i> Làm lại
        </button>
        <button id="fire-btn" class="control-btn complete">
            <i class="fas fa-bullseye"></i> Bắn!
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Nhiệm vụ:</strong> <?= $currentLevel['desc'] ?></span>
        </div>
    </div>
    
    <div class="game-container">
        <div id="game-stage">
            <canvas id="gameCanvas"></canvas>
            
            <div class="controls-overlay">
                <div class="protractor-container">
                    <div class="protractor-bg"></div>
                    <input type="range" id="angle-slider" min="0" max="180" value="0" step="1">
                </div>
            </div>
            
            <div id="miss-feedback" class="miss-feedback hidden">TRƯỢT RỒI! THỬ LẠI NHÉ</div>
        </div>
    </div>
</div>

<div id="result-modal" class="modal">
    <div class="modal-content result-content">
        <h2 id="modal-title"></h2>
        <p id="modal-message"></p>
        <div class="modal-buttons">
            <button id="next-level-btn" class="game-btn">Màn tiếp theo</button>
            <button id="retry-btn" class="game-btn">Thử lại</button>
            <button id="back-btn" class="game-btn back-btn">Quay lại</button>
        </div>
    </div>
</div>

<script>
    const levelData = <?= json_encode($currentLevel) ?>;
    const totalLevels = <?= $totalLevels ?>;
</script>

<script src="<?= $base_url ?>/public/JS/math_angle_game.js?v=<?= time() ?>"></script>

<script>
    // Intro modal handling
    document.addEventListener('DOMContentLoaded', () => {
        const introModal = document.getElementById('intro-modal');
        const nextStoryButton = document.getElementById('nextStoryButton');
        const storyText = document.getElementById('storyText');
        const giveUpButton = document.getElementById('giveUpButton');
        const backButton = document.getElementById('back-btn');

        const storyDialogues = [
            'Chào các bạn nhỏ! Bầu trời hôm nay bỗng nhiên xuất hiện tới 4 ông mặt trời rực lửa. Cây cối héo úa, sông suối cạn khô, muôn loài đang vô cùng khát nước và nóng bức. Tráng sĩ Hậu Nghệ với sức mạnh phi thường đã mang cây cung thần ra để bắn hạ 3 ông mặt trời, cứu lấy Trái Đất. Để mũi tên bay trúng đích, Hậu Nghệ phải căn chỉnh Góc bắn của cây cung thật chuẩn xác. Hậu Nghệ đang mồ hôi nhễ nhại và rất cần các "chiến binh toán học" nhí giúp ngắm bắn đấy!',
            'Nhiệm vụ của chúng mình: Trên màn hình là cây cung thần của Hậu Nghệ. Các bạn hãy căn chỉnh tạo thành các góc thật chuẩn xác nhé:\nGóc nhọn (Nhỏ hơn góc vuông): Kéo mũi tên tạo thành một góc hẹp, nhọn hoắt để bắn những mặt trời ở tầm thấp.',
            'Góc vuông (Ngay ngắn như góc quyển vở): Kéo mũi tên thẳng đứng lên tạo thành hình chữ L (90 độ) để bắn mặt trời ở ngay trên đỉnh đầu.\nGóc tù (Lớn hơn góc vuông): Kéo mũi tên ngả rộng ra phía sau để lấy đà bắn những mặt trời ở tít trên cao.\nGóc bẹt (Thẳng tắp): Hạ mũi tên nằm ngang thẳng băng như một đường kẻ để ngắm bắn mặt trời ở tuốt phía chân trời.',
            'Trái đất đang nóng lên rồi, hãy nhanh tay ngắm bắn giúp Hậu Nghệ nào! 3... 2... 1... Kéo cung!'
        ];

        let currentStoryIndex = 0;
        
        if (nextStoryButton && storyText && introModal) {
            nextStoryButton.addEventListener('click', () => {
                currentStoryIndex++;

                if (currentStoryIndex < storyDialogues.length) {
                    storyText.textContent = storyDialogues[currentStoryIndex];

                    if (currentStoryIndex === storyDialogues.length - 1) {
                        nextStoryButton.textContent = 'Kéo cung thôi!';
                    }
                } else {
                    introModal.classList.remove('active');
                }
            });
        }
        
        if (giveUpButton) {
            giveUpButton.addEventListener('click', () => {
                window.location.href = '<?= $base_url ?>/views/main_lesson.php';
            });
        }
        
        if (backButton) {
            backButton.addEventListener('click', () => {
                window.location.href = '<?= $base_url ?>/views/lessons/math.php';
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../template/footer.php'; ?>