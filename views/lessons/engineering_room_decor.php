<?php
require_once __DIR__ . '/../template/header.php';
?>

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/room_decor.css?v=<?= time() ?>">

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/character/designer.png" alt="Nhà thiết kế" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>Chào bạn, mình là Design Master!</h3>
            <p>Chào mừng bạn đến với trò chơi "TRANG TRÍ PHÒNG". Nhiệm vụ của bạn là sử dụng sự sáng tạo để trang trí căn phòng theo phong cách của riêng bạn. Hãy kéo thả các đồ vật và tạo ra không gian đẹp nhất! Bạn sẵn sàng chưa?</p>
            <button id="startGameButton" class="start-btn">Bắt đầu thôi!</button>
        </div>
    </div>
</div>

<div class="game-wrapper room-decor-mode"><br><br><br>
    <div class="game-header">
        <h1>TRÒ CHƠI TRANG TRÍ PHÒNG</h1>
        <p class="game-subtitle">Thử thách sáng tạo - Rèn luyện kỹ năng thiết kế</p>
    </div>
    
    <div class="game-stats">
        <div class="stat-box remaining">
            <span class="stat-label">SỐ ĐỒ VẬT</span>
            <span id="items-count-display" class="stat-value">0</span>
        </div>
        <div class="stat-box timer">
            <span class="stat-label">THỜI GIAN</span>
            <span id="timer-display" class="stat-value">00:00</span>
        </div>
        <div class="stat-box progress">
            <span class="stat-label">DANH MỤC</span>
            <span id="category-display" class="stat-value">Nội thất</span>
        </div>
    </div>
    
    <div class="game-controls">
        <button id="giveUpButton" class="control-btn give-up">
            <i class="fas fa-home"></i> Menu
        </button>
        <button class="control-btn reset" onclick="clearRoom()">
            <i class="fas fa-broom"></i> Dọn sạch
        </button>
        <button class="control-btn pause" id="save-btn">
            <i class="fas fa-camera"></i> Chụp ảnh
        </button>
        <button class="control-btn complete" id="complete-room-btn">
            <i class="fas fa-check"></i> Hoàn thành
        </button>
    </div>
    
    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb"></i>
            <span><strong>Cách chơi:</strong> Chọn danh mục, kéo đồ vật từ kho vào phòng, click đúp để xoay lật, kéo vào thùng rác để xóa</span>
        </div>
    </div>
    
    <div class="game-container-main">
        <div class="sidebar">
            <div class="logo-area">
                <h2>Kho Đồ</h2>
            </div>

            <div class="category-tabs">
                <?php $firstCat = true; ?>
                <?php foreach ($gameData['categories'] as $key => $cat): ?>
                    <button class="cat-btn <?= $firstCat ? 'active' : '' ?>" onclick="switchCategory('<?= $key ?>', this)">
                        <i class="fas <?= $cat['icon'] ?>"></i>
                        <span><?= $cat['label'] ?></span>
                    </button>
                    <?php $firstCat = false; ?>
                <?php endforeach; ?>
            </div>

            <div id="items-grid" class="items-grid"></div>

            <div class="trash-zone" id="trash-can">
                <i class="fas fa-trash-alt"></i> Kéo vào đây để xóa
            </div>
        </div>

        <div class="main-area">
            <div class="room-viewport">
                <div id="room-container">
                    <div id="room-backgrounds">
                        <img src="<?= $base_url ?>/public/images/room_decor/room_1.png" id="bg-main" class="bg-layer">
                    </div>
                    
                    <div id="rug-layer"></div>

                    <div id="furniture-layer"></div>
                </div>
            </div>
            
            <div class="help-text">
                👆 <strong>Click đúp</strong> để xoay lật đồ | 🖱️ <strong>Kéo thả</strong> để sắp xếp (Vật ở thấp sẽ che vật ở cao)
            </div>
        </div>
    </div>
</div>
    </div>
</div>

<script>
    const categories = <?= json_encode($gameData['categories']) ?>;
</script>

<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="<?= $base_url ?>/public/JS/room_decor.js?v=<?= time() ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const introModal = document.getElementById('intro-modal');
        const startGameButton = document.getElementById('startGameButton');
        const giveUpButton = document.getElementById('giveUpButton');
        
        if (startGameButton) {
            startGameButton.addEventListener('click', () => {
                introModal.classList.remove('active');
            });
        }
        
        if (giveUpButton) {
            giveUpButton.addEventListener('click', () => {
                window.location.href = baseUrl + '/views/main_lesson.php';
            });
        }
    });
    
    (function(){
        const btn = document.getElementById('complete-room-btn');
        if (!btn) return;
        btn.addEventListener('click', function(){
            fetch(`${baseUrl}/views/lessons/update-room-decor-score`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'commit', score_pct: 100 })
            }).then(r => r.json()).then(json => {
                if (json && json.success) {
                    alert('Hoàn thành đã được lưu — Bạn nhận đầy đủ điểm và XP.');
                } else {
                    alert('Lưu điểm thất bại: ' + (json && json.message ? json.message : 'Không rõ'));
                }
            }).catch(err => {
                console.error('Commit room decor error', err);
                alert('Lỗi kết nối. Không thể lưu điểm');
            });
        });
    })();
</script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>