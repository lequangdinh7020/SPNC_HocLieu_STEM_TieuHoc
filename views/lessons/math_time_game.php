<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = 'https';
}
$host = $_SERVER['HTTP_HOST'];
$project_path = '/SPNC_HocLieu_STEM_TieuHoc';
$base_url = $protocol . '://' . $host . $project_path;

$currentLevel = [
    'title' => 'Học Xem Giờ - Cấp 1',
    'desc' => 'Chỉnh đồng hồ theo yêu cầu',
    'questions' => [
        ['h' => 3, 'm' => 0],
        ['h' => 6, 'm' => 30],
        ['h' => 9, 'm' => 15],
        ['h' => 12, 'm' => 0],
        ['h' => 2, 'm' => 45]
    ]
];
$totalLevels = 5;

$userName  = '';
$userEmail = '';
$avatarHtml = '<div class="avatar">👦</div>';

if (!empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../../models/Database.php';
        $database = new Database();
        $db = $database->getConnection();

        if ($db) {
            $stmt = $db->prepare("SELECT username, email, first_name, last_name, avatar FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
                $userName = $fullName !== '' ? $fullName : ($user['username'] ?? '');
                $userEmail = $user['email'] ?? '';

                if (!empty($user['avatar'])) {
                    $avatarPath = $base_url . '/public/uploads/avatars/' . rawurlencode($user['avatar']);
                    $avatarHtml = "<img src=\"{$avatarPath}\" alt=\"avatar\" class=\"avatar-img\" />";
                }
            }
        }
    } catch (Exception $e) {
        error_log("User load error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Học Xem Giờ - STEM Universe</title>
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Baloo+2:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/home.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/time_game.css?v=<?php echo time(); ?>">
    
    <style>
        body { 
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            overflow-x: hidden; 
            overflow-y: auto;
            font-family: 'Quicksand', sans-serif; 
            margin: 0; 
            padding: 0; 
        }
        .time-game { 
            min-height: calc(100vh - 200px);
            padding-bottom: 50px;
        }
    </style>
</head>
<body>
    <div class="bg-elements">
        <div class="bg-circle circle-1"></div>
        <div class="bg-circle circle-2"></div>
        <div class="bg-circle circle-3"></div>
        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>
    </div>

    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo" onclick="window.history.back()">
                    <div class="logo-icon"><img src="<?= $base_url ?>/public/images/logo.png" alt="STEM Universe Logo" style="width: 100%; height: 100%; object-fit: contain;"></div>
                    <div class="logo-text">
                        <h1>STEM Universe</h1>
                        <p>Hành trình khám phá tri thức</p>
                    </div>
                </div>

                <nav class="main-nav">
                    <a href="<?= $base_url ?>/views/home.php" class="nav-link">Trang chủ</a>
                    <a href="<?= $base_url ?>/views/main_lesson.php" class="nav-link">Bài học</a>
                    <a href="<?= $base_url ?>/views/achievements.php" class="nav-link">Thành tích</a>
                </nav>

                <div class="header-actions">
                    <div class="user-menu">
                        <div class="user-avatar" id="userAvatar">
                            <?= $avatarHtml ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="user-dropdown-overlay" id="dropdownOverlay"></div>
    <div class="user-dropdown" id="userDropdown">
        <div class="dropdown-header">
            <div class="user-info">
                <div class="avatar-large-dropdown">
                    <?= $avatarHtml ?>
                </div>
                <div class="user-details">
                    <p class="user-name"><?= htmlspecialchars($userName ?: 'Khách') ?></p>
                    <p class="user-email"><?= htmlspecialchars($userEmail ?: '') ?></p>
                </div>
            </div>
        </div>

        <div class="dropdown-section">
            <a href="<?= $base_url ?>/views/profile.php" class="dropdown-item">
                <i class="fas fa-user"></i>
                <span>Xem hồ sơ</span>
            </a>
            <button class="dropdown-item logout-btn" id="logoutBtn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Đăng xuất</span>
            </button>
        </div>
    </div>

    <div class="game-wrapper time-game"><br><br><br><br>
        
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
                <canvas id="clockCanvas" width="350" height="350"></canvas>
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
    <script src="<?= $base_url ?>/public/JS/header.js"></script>
    <script src="<?= $base_url ?>/public/JS/time_game.js"></script>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <div class="logo-icon" style="width: 65px; height: 65px;"><img src="<?= $base_url ?>/public/images/logo.png" alt="STEM Universe Logo" style="width: 100%; height: 100%; object-fit: contain;"></div>
                        <span>STEM Universe</span>
                    </div>
                    <p>Khám phá thế giới STEM đầy sáng tạo. Nền tảng học liệu tương tác cho học sinh tiểu học Việt Nam.</p>
                </div>
                <div class="footer-section">
                    <h4>Khám phá</h4>
                    <a href="#">Tất cả bài học</a>
                    <a href="#">Thử thách STEM</a>
                    <a href="#">Tài nguyên giáo viên</a>
                </div>
                <div class="footer-section">
                    <h4>Kết nối</h4>
                    <div class="social-links">
                        <a href="https://www.facebook.com/" class="social-link" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/" class="social-link" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/" class="social-link" title="YouTube">
                            <i class="fab fa-youtube"></i>
                        </a>
                        <a href="https://www.linkedin.com/" class="social-link" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 STEM Universe. Được phát triển với ❤️ dành cho giáo dục STEM Việt Nam.</p>
            </div>
        </div>
    </footer>

    <script src="<?= $base_url ?>/public/JS/home.js"></script>

</body>
</html>