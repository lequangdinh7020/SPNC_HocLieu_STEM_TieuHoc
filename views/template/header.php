<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$project_path = '/SPNC_HocLieu_STEM_TieuHoc';

$base_url = $protocol . '://' . $host . $project_path;

$current_page = basename($_SERVER['PHP_SELF']) ?? 'home.php';

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
        error_log("Header user load error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STEM Universe - Học liệu STEM Tiểu học</title>
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/header.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Baloo+2:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    <div class="logo-icon">🌟</div>
                    <div class="logo-text">
                        <h1>STEM Universe</h1>
                        <p>Hành trình khám phá tri thức</p>
                    </div>
                </div>
                
                <nav class="main-nav">
                    <a href="<?= $base_url ?>/views/home.php" class="nav-link <?php echo $current_page === 'home.php' ? 'active' : ''; ?>">Trang chủ</a>
                    <a href="<?= $base_url ?>/views/main_lesson.php" class="nav-link <?php echo $current_page === 'main_lesson.php' ? 'active' : ''; ?>">Bài học</a>
                    <a href="<?= $base_url ?>/views/achievements.php" class="nav-link <?php echo $current_page === 'achievements.php' ? 'active' : ''; ?>">Thành tích</a>
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
    <script>
        const baseUrl = '<?= $base_url ?>';
    </script>
    <script src="<?= $base_url ?>/public/JS/header.js?v=<?php echo time(); ?>"></script>

</body>
</html>
