<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Trí Phòng - STEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/main.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/room_decor.css">
</head>
<body>

<div class="game-container">
    
    <div class="sidebar">
        <div class="logo-area">
            <a href="<?= $base_url ?>/views/main_lesson.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
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
        <div class="top-toolbar">
            <div class="room-title">Phòng Của Em</div>
            <div class="actions">
                <button class="tool-btn" onclick="clearRoom()"><i class="fas fa-broom"></i> Dọn sạch</button>
                <button class="tool-btn" onclick="window.location.href='<?= $base_url ?>/views/lessons/engineering.php'"><i class="fas fa-arrow-left"></i> Quay lại</button>
                <button class="tool-btn" id="complete-room-btn"><i class="fas fa-check"></i> Hoàn thành</button>
                <button class="tool-btn highlight" id="save-btn"><i class="fas fa-camera"></i> Chụp ảnh</button>
            </div>
        </div>

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

<script>
    const baseUrl = "<?= $base_url ?>";
    const categories = <?= json_encode($gameData['categories']) ?>;
</script>
<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
<script src="<?= $base_url ?>/public/JS/room_decor.js"></script>

<script>
    // Complete button: commit full score (100%) for Room Decor
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

</body>
</html>