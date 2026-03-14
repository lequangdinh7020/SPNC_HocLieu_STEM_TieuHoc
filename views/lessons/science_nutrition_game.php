<?php
require_once __DIR__ . '/../template/header.php';

// Khởi tạo session và điểm nếu chưa có
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['nutrition_score'])) {
    $_SESSION['nutrition_score'] = 0;
}

// Định nghĩa danh sách món ăn
$foodItems = [
    // Tầng 4 (Đáy tháp) -> data-group = 1
    ['id' => 'food1', 'name' => 'Hạt', 'group' => 1, 'img' => 'hat.png'],
    ['id' => 'food2', 'name' => 'Đậu', 'group' => 1, 'img' => 'hat_dau.png'],
    ['id' => 'food3', 'name' => 'Bánh mì', 'group' => 1, 'img' => 'banh_mi.png'],
    ['id' => 'food4', 'name' => 'Sandwich', 'group' => 1, 'img' => 'sandwich.png'],
    ['id' => 'food5', 'name' => 'Mì', 'group' => 1, 'img' => 'mi.png'],
    ['id' => 'food6', 'name' => 'Cơm', 'group' => 1, 'img' => 'com.png'],
    ['id' => 'food7', 'name' => 'Pasta', 'group' => 1, 'img' => 'pasta.png'],
    ['id' => 'food8', 'name' => 'Ngũ cốc', 'group' => 1, 'img' => 'ngu_coc.png'],

    // Tầng 3 (Rau/Trái cây) -> data-group = 2
    ['id' => 'food9', 'name' => 'Cà chua', 'group' => 2, 'img' => 'ca_chua.png'],
    ['id' => 'food10', 'name' => 'Ớt chuông', 'group' => 2, 'img' => 'ot_chuong.png'],
    ['id' => 'food11', 'name' => 'Nấm', 'group' => 2, 'img' => 'nam.png'],
    ['id' => 'food12', 'name' => 'Cà rốt', 'group' => 2, 'img' => 'ca_rot.png'],
    ['id' => 'food13', 'name' => 'Cam', 'group' => 2, 'img' => 'cam.png'],
    ['id' => 'food14', 'name' => 'Chuối', 'group' => 2, 'img' => 'chuoi.png'],
    ['id' => 'food15', 'name' => 'Nho', 'group' => 2, 'img' => 'nho.png'],
    ['id' => 'food16', 'name' => 'Dâu', 'group' => 2, 'img' => 'dau.png'],

    // Tầng 2 (Đạm/Sữa) -> data-group = 3
    ['id' => 'food17', 'name' => 'Yogurt', 'group' => 3, 'img' => 'yogurt.png'],
    ['id' => 'food18', 'name' => 'Sữa', 'group' => 3, 'img' => 'sua.png'],
    ['id' => 'food19', 'name' => 'Phô mai', 'group' => 3, 'img' => 'pho_mai.png'],
    ['id' => 'food20', 'name' => 'Cá', 'group' => 3, 'img' => 'ca.png'],
    ['id' => 'food21', 'name' => 'Thịt', 'group' => 3, 'img' => 'thit.png'],
    ['id' => 'food22', 'name' => 'Đùi gà', 'group' => 3, 'img' => 'dui_ga.png'],
    ['id' => 'food23', 'name' => 'Trứng', 'group' => 3, 'img' => 'trung.png'],
    ['id' => 'food24', 'name' => 'Tôm', 'group' => 3, 'img' => 'tom.png'],

    // Tầng 1 (Đỉnh tháp) -> data-group = 4
    ['id' => 'food25', 'name' => 'Dầu ăn', 'group' => 4, 'img' => 'dau_an.png'],
    ['id' => 'food26', 'name' => 'Đường', 'group' => 4, 'img' => 'duong.png'],
    ['id' => 'food27', 'name' => 'Muối', 'group' => 4, 'img' => 'muoi.png'],
];
shuffle($foodItems);
?>

<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">

<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/nutrition_game.css?v=<?= time() . rand(1000, 9999) ?>">
<link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css?v=<?= time() . rand(1000, 9999) ?>">
<script src="https://unpkg.com/kaboom@3000.0.1/dist/kaboom.js"></script>

<div id="intro-modal" class="modal-overlay active">
    <div class="intro-dialogue modal-content">
        <div class="intro-avatar">
            <img src="<?= $base_url ?>/public/images/foods/Giong.png" alt="Gióng" class="intro-avatar-img">
        </div>
        <div class="intro-text-content">
            <h3>LỜI DẪN CHUYỆN: GIÚP GIÓNG KHỎE ĐỂ RA TRẬN</h3>
            <p id="storyText">Chào các bạn nhỏ! Giặc Ân đang tràn vào bờ cõi nước ta. Để Gióng có thể vươn vai đã biến thành một tráng sĩ khổng lồ, cưỡi ngựa sắt ra trận thì bà con cần góp nhiều gạo và cà pháo cho Gióng ăn. Nhưng chỉ ăn Cơm (tinh bột) và Cà (chất xơ) thì Gióng sẽ không đủ sức mạnh để đánh giặc lâu dài! Vì vậy chúng ta cần sắp xếp một bữa ăn cân bằng theo đúng Tháp Dinh Dưỡng cho Gióng!</p>
            <button id="nextStoryButton" class="start-btn"><i class="fas fa-forward"></i> Tiếp theo</button>
        </div>
    </div>
</div>

<div class="game-wrapper">
    <div class="game-header-bar">
        <div class="header-left">
            <a href="<?= $base_url ?>/views/lessons/science.php" class="menu-btn">Menu</a>
            <button id="resetButton" class="reset-btn">Làm lại</button>
        </div>
        <div class="header-center">
            <h1>SẮP XẾP THÁP DINH DƯỠNG</h1>
            <p class="game-subtitle">Xếp món ăn đúng nhóm dinh dưỡng</p>
        </div>
        <div class="header-right">
            <div class="score-display">
                <span class="score-label">Điểm</span>
                <span id="score" class="score-value">0</span>
            </div>
        </div>
    </div>

    <div class="game-instructions">
        <div class="instruction-box">
            <i class="fas fa-lightbulb instruction-icon"></i>
            <h3>Hướng dẫn chơi</h3>
            <p>Hãy kéo các món ăn vào đúng nhóm của chúng trên tháp dinh dưỡng.</p>
        </div>
    </div>

    <div id="userFeedback"></div>
    <button id="finishButton" class="finish-btn" style="display: block; margin: 20px auto;">Kết thúc</button>

    <div id="gameContainer">
        <div id="foodBank">
            <h3>Hãy kéo các món ăn vào đúng nhóm của chúng trên tháp.</h3>
            <div class="food-items-container">
                <?php foreach ($foodItems as $food): ?>
                    <div class="food-item"
                        draggable="true"
                        id="<?= $food['id'] ?>"
                        data-group="<?= $food['group'] ?>"
                        data-name="<?= $food['name'] ?>" data-attempt="1"> <img src="<?= $base_url ?>/public/images/foods/<?= $food['img'] ?>" alt="<?= $food['name'] ?>">
                        <span><?= $food['name'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="pyramid">
            <div class="pyramid-level" id="level4" data-group="4">
                <span>Tầng 1: Hạn chế</span>
            </div>
            <div class="pyramid-level" id="level3" data-group="3">
                <span>Tầng 2: Ăn vừa phải</span>
            </div>
            <div class="pyramid-level" id="level2" data-group="2">
                <span>Tầng 3: Ăn nhiều</span>
            </div>
            <div class="pyramid-level" id="level1" data-group="1">
                <span>Tầng 4: Ăn đủ</span>
            </div>
        </div>
    </div>

    <div class="final-result" id="finalResult">
        <h2>Kết quả cuối cùng</h2>
        <div class="score-circle">
            <p class="final-score" id="finalScore">0</p>
            <span class="score-label">Điểm</span>
        </div>
        <div class="result-actions">
            <button class="play-again" onclick="location.reload()">Chơi lại</button>
            <a href="<?= $base_url ?>/views/lessons/science.php" class="back-to-lessons">Về bài học</a>
        </div>
    </div>

    <div class="game-hints">
        <h3><i class="fas fa-trophy"></i> Mẹo để đạt điểm cao</h3>
        <ul>
            <li>Nhớ nhóm thực phẩm: Đáy tháp là tinh bột, giữa là rau/trái cây, protein/sữa, đỉnh là chất béo</li>
            <li>Quan sát kỹ hình ảnh món ăn trước khi kéo thả</li>
        </ul>
    </div>
</div>

<script>
    // Avoid re-declaring baseUrl if other templates already define it
    window.baseUrl = window.baseUrl || "<?= $base_url ?>";
</script>

<script src="<?= $base_url ?>/public/JS/nutrition_game.js?v=<?= time() . rand(1000, 9999) ?>"></script>

<?php
require_once __DIR__ . '/../template/footer.php';
?>