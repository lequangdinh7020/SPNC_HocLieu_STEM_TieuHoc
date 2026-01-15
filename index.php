<?php
session_start();

// 1. TẢI CÁC FILE CẦN THIẾT
require_once 'models/Database.php';
require_once 'models/User.php';
require_once 'controllers/LessonController.php';
require_once 'controllers/AuthController.php';

// KIỂM TRA REMEMBER TOKEN - nếu session hết nhưng có cookie remember
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember'])) {
    try {
        $authController = new AuthController();
        $authController->checkRememberToken();
    } catch (Exception $e) {
        error_log("Remember token check error: " . $e->getMessage());
    }
}

// 2. PHÂN TÍCH URL
$request_uri = $_SERVER['REQUEST_URI'];

// Xóa tên thư mục con khỏi URL
$base_path = dirname($_SERVER['SCRIPT_NAME']);
$base_path = rtrim($base_path, '/\\');

$route = str_replace($base_path, '', $request_uri);

if (strpos($route, '?') !== false) {
    $route = substr($route, 0, strpos($route, '?'));
}
// Đặt route mặc định nếu rỗng
if (empty($route)) {
    $route = '/';
}

// 3. ĐIỀU HƯỚNG (ROUTING)
// Tái tạo logic từ tệp 'routes.php'

$lessonController = new LessonController();

switch ($route) {
    // Support pretty route used by some front-end links
    case '/science/color-game':
        $lessonController->showColorGame();
        break;

    // --- CÁC ROUTE CỦA GAME ---
    case '/views/lessons/science_color_game':
        $lessonController->showColorGame();
        break;
        
    case '/views/lessons/science_nutrition_game':
        $lessonController->showNutritionGame();
        break;
    
    case '/views/lessons/update-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateNutritionScore();
        }
        break;
    case '/science/update-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateNutritionScore();
        }
        break;

    case '/views/lessons/science_plant_game':
        $lessonController->showPlantGame();
        break;

    case '/views/lessons/update-plant-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updatePlantScore();
        }
        break;

    case '/views/lessons/science_trash_game':
        $lessonController->showTrashGame();
        break;
    case '/views/lessons/update-trash-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateTrashScore();
        }
        break;
    case '/science/update-trash-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateTrashScore();
        }
        break;
    case 'trash_score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateTrashScore();
        }
        break;

    case '/views/lessons/science_day_night':
        $lessonController->showDayNightLesson();
        break;

    case '/views/lessons/technology_family_tree_game':
        $lessonController->showFamilyTree();
        break;
    case '/views/lessons/update-family-tree-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateFamilyTreeScore();
        }
        break;

    case '/views/lessons/technology_coding_game':
        $lessonController->showCodingGame();
        break;
    case '/views/lessons/update-coding-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateCodingScore();
        }
        break;

    case '/science/commit-quiz':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->commitQuizScore();
        }
        break;

    case '/views/lessons/technology_computer_parts':
        $lessonController->showComputerPartsGame();
        break;
    case '/views/lessons/update-computer-parts-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateComputerPartsScore();
        }
        break;

    case '/views/lessons/technology_typing_thach_sanh':
        $lessonController->showThachSanhGame();
        break;
    case '/views/lessons/update-thach-sanh-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateThachSanhScore();
        }
        break;
    case '/views/lessons/reset-thach-sanh-commit':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->resetThachSanhCommit();
        }
        break;

    case '/views/lessons/technology_painter_game':
        $lessonController->showPainterGame();
        break;
    case '/views/lessons/update-painter-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updatePainterScore();
        }
        break;

    case '/views/lessons/engineering_flower_mechanism':
        $lessonController->showFlowerMechanismGame();
        break;
    case '/views/lessons/update-flower-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateFlowerScore();
        }
        break;

    case '/views/lessons/engineering_bridge_game':
        $lessonController->showBridgeGame();
        break;
        
    case '/views/lessons/engineering_car_builder':
        // Chuyển sang engineering_water_pipe
        $lessonController->showPipeGame();
        break;

    case '/views/lessons/engineering_water_filter':
        $lessonController->showWaterFilterGame();
        break;

    case '/views/lessons/math_angle_game':
        $lessonController->showMathAngleGame();
        break;
    case '/views/lessons/update-angle-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateAngleGameScore();
        }
        break;

    case '/views/lessons/math_shapes_challenge':
        $lessonController->showMathShapesGame();
        break;
    case '/views/lessons/update-shapes-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateShapesScore();
        }
        break;

    case '/views/lessons/math_number_game':
        $lessonController->showMathNumberGame();
        break;
    case '/views/lessons/update-number-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateNumberScore();
        }
        break;

    case '/views/lessons/math_tangram_3d':
        $lessonController->showTangramGame();
        break;
    case '/views/lessons/update-tangram-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateTangramScore();
        }
        break;

    case '/views/lessons/update-tower-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateTowerScore();
        }
        break;

    case '/views/lessons/math_time_game':
        $lessonController->showTimeGame();
        break;
    case '/views/lessons/update-time-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateTimeScore();
        }
        break;
    
    case '/views/lessons/engineering_tower_game':
        // Route mới cho trò chơi xây tháp
        $lessonController->showTowerGame();
        break;
        
    case '/views/lessons/engineering_room_decor':
        // Route mới cho trò sắp xếp căn phòng
        $lessonController->showRoomDecorGame();
        break;
    case '/views/lessons/update-room-decor-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateRoomDecorScore();
        }
        break;

    case '/views/lessons/update-water-filter-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateWaterFilterScore();
        }
        break;

    case '/views/lessons/engineering_water_pipe':
        // Route mới cho hệ thống dẫn nước
        $lessonController->showPipeGame();
        break;

    case '/views/lessons/update-water-pipe-score':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $lessonController->updateWaterPipeScore();
        }
        break;

    // --- FORGOT PASSWORD VIEW ---
    case '/forgot-password':
        // serve view directly
        require __DIR__ . '/views/forgot-password.php';
        break;

    // --- AUTH APIs (forgot password) ---
    case '/auth/forgot-password/send-code':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new AuthController();
            echo $auth->sendResetCode();
        }
        break;

    case '/auth/forgot-password/verify-code':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new AuthController();
            echo $auth->verifyResetCode();
        }
        break;

    case '/auth/forgot-password/reset':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new AuthController();
            echo $auth->resetPassword();
        }
        break;
    
    // --- ROUTE CHO TRANG CHỦ ---
    case '/':
    case '/index.php':
        // Gọi hàm hiển thị trang chủ
        showHomePage();
        break;
        
    default:
        // 404 Not Found
        // Hiển thị trang chủ nếu không tìm thấy
        showHomePage();
        break;
}

/**
 * HÀM HIỂN THỊ TRANG CHỦ
 */
function showHomePage() {
    // Các biến nằm trong phạm vi (scope) của hàm
    $isLoggedIn = isset($_SESSION['user_id']);
    $userName = $isLoggedIn ? $_SESSION['full_name'] : '';

    $stemFields = [];
    $dbError = false;
    
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            $query = "SELECT * FROM stem_fields ORDER BY id";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $stemFields = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $dbError = true;
        }
    } catch (Exception $e) {
        $dbError = true;
        error_log("Database error: " . $e->getMessage());
    }

    $base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";
    
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STEM Kids Việt - Khám phá vũ trụ STEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/CSS/style.css">
</head>
<body>
    <div class="cosmic-bg">
        <div class="nebula"></div>
        <div class="stars"></div>
        <div class="twinkling"></div>
        <div class="shooting-stars">
            <div class="shooting-star"></div>
            <div class="shooting-star"></div>
            <div class="shooting-star"></div>
        </div>
    </div>
    
    <main>
        <section class="cosmic-hero">
            <div class="cosmic-container">
                <div class="hero-orbit">
                    <div class="central-planet">
                        <div class="planet-glow"></div>
                        <div class="planet-surface">
                            <div class="planet-crater"></div>
                            <div class="planet-crater"></div>
                            <div class="planet-crater"></div>
                        </div>
                    </div>

                    <div class="moon-orbit orbit-1">
                        <div class="moon science-moon" data-tooltip="Khoa học">
                            <i class="fas fa-flask"></i>
                        </div>
                    </div>
                    <div class="moon-orbit orbit-2">
                        <div class="moon tech-moon" data-tooltip="Công nghệ">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                    <div class="moon-orbit orbit-3">
                        <div class="moon engineering-moon" data-tooltip="Kỹ thuật">
                            <i class="fas fa-cogs"></i>
                        </div>
                    </div>
                    <div class="moon-orbit orbit-4">
                        <div class="moon math-moon" data-tooltip="Toán học">
                            <i class="fas fa-calculator"></i>
                        </div>
                    </div>
                </div>

                <div class="hero-content">
                    <div class="cosmic-text">
                        <div class="cosmic-badge">
                            <span class="cosmic-sparkle">✨</span>
                            <span class="badge-text">Chào mừng đến vũ trụ</span>
                            <span class="cosmic-sparkle">✨</span>
                        </div>
                        <h1 class="cosmic-title">
                            <span class="title-glow">STEM</span>
                            <span class="title-neon">Universe</span>
                        </h1>
                        <p class="cosmic-description">
                            Nơi những giấc mơ nhỏ bay vào vũ trụ tri thức! 
                            Khám phá 4 hành tinh STEM đầy màu sắc và bí ẩn.
                        </p>
                        
                        <?php if ($dbError): ?>
                        <div class="cosmic-warning">
                            <div class="warning-orbit">
                                <i class="fas fa-satellite"></i>
                            </div>
                            <div class="warning-content">
                                <h4>Hệ thống đang khởi động</h4>
                                <p>Kích hoạt trạm vũ trụ để bắt đầu hành trình</p>
                                <div class="warning-actions">
                                    <a href="install.php" class="btn btn-neon">
                                        <i class="fas fa-rocket"></i>
                                        Khởi động hệ thống
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                            <div class="cosmic-stats">
                                <div class="stat-orbit">
                                    <div class="stat-planet">
                                        <div class="stat-number">4</div>
                                        <div class="stat-label">Hành tinh</div>
                                    </div>
                                </div>
                                <div class="stat-orbit">
                                    <div class="stat-planet">
                                        <div class="stat-number">∞</div>
                                        <div class="stat-label">Khám phá</div>
                                    </div>
                                </div>
                                <div class="stat-orbit">
                                    <div class="stat-planet">
                                        <div class="stat-number">100+</div>
                                        <div class="stat-label">Thử thách</div>
                                    </div>
                                </div>
                            </div>
                            <div class="cosmic-actions">
                                <?php if (!$isLoggedIn): ?>
                                    <div class="btn-group" style="display: flex; flex-direction: column; gap: 1.0rem;">
                                        <a href="<?php echo $base_url; ?>/views/signin.php" class="btn btn-neon btn-launch" style="width: 100%; max-width: 280px; justify-content: center; margin: 0;">
                                            <i class="fas fa-rocket"></i>
                                            <span>Bắt đầu phiêu lưu</span>
                                            <div class="rocket-trail"></div>
                                        </a>
                                        <a href="<?php echo $base_url; ?>/views/signup.php" class="btn btn-cosmic" style="width: 100%; max-width: 280px; justify-content: center; margin: 0;">
                                            <i class="fas fa-user-astronaut"></i>
                                            Gia nhập phi hành đoàn
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="astronaut-welcome">
                                        <div class="astronaut-avatar">
                                            <i class="fas fa-user-astronaut"></i>
                                        </div>
                                        <div class="welcome-message">
                                            <p>Chào mừng phi hành gia</p>
                                            <h4><?php echo htmlspecialchars($userName); ?>!</h4>
                                        </div>
                                    </div>
                                    <a href="<?php echo $base_url; ?>/views/home.php" class="btn btn-neon btn-launch">
                                        <i class="fas fa-play"></i>
                                        <span>Tiếp tục hành trình</span>
                                        <div class="rocket-trail"></div>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <?php if (!$dbError && !empty($stemFields)): ?>
        <section class="planets-section">
            <div class="cosmic-container">
                <div class="section-header">
                    <div class="section-orbit">
                        <div class="section-comet"></div>
                    </div>
                    <h2 class="section-title cosmic-title">
                        <span class="title-glow">🎯</span>
                        Hệ Mặt Trời STEM
                        <span class="title-glow">🌌</span>
                    </h2>
                    <p class="section-subtitle">Chọn một hành tinh để bắt đầu cuộc phiêu lưu vũ trụ</p>
                </div>
                
                <div class="planets-grid">
                    <?php foreach ($stemFields as $field): ?>
                    <div class="planet-card" data-planet="<?php echo strtolower($field['name']); ?>">
                        <div class="planet-orbit">
                            <div class="planet" style="--planet-color: <?php echo $field['color']; ?>">
                                <div class="planet-surface">
                                    <div class="planet-icon">
                                        <?php echo $field['icon']; ?>
                                    </div>
                                </div>
                                <div class="planet-ring"></div>
                                <div class="planet-glow"></div>
                            </div>
                            <div class="moon"></div>
                        </div>
                        <div class="planet-info">
                            <h3 class="planet-name"><?php echo htmlspecialchars($field['name']); ?></h3>
                            <p class="planet-description"><?php echo htmlspecialchars($field['description']); ?></p>
                            <div class="planet-actions">
                                <button class="btn btn-orbit" onclick="window.location.href='adventure.php?field=<?php echo $field['id']; ?>'">
                                    <span>Khám phá</span>
                                    <i class="fas fa-space-shuttle"></i>
                                </button>
                            </div>
                        </div>
                        <div class="star-field">
                            <div class="star"></div>
                            <div class="star"></div>
                            <div class="star"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="cosmic-features">
            <div class="cosmic-container">
                <div class="section-header">
                    <h2 class="section-title cosmic-title">
                        <span class="title-glow">🚀</span>
                        Tại sao chọn STEM Universe?
                        <span class="title-glow">🌟</span>
                    </h2>
                </div>
                <div class="features-grid">
                    <div class="feature-comet">
                        <div class="comet-head">
                            <i class="fas fa-gamepad"></i>
                        </div>
                        <div class="comet-tail"></div>
                        <div class="feature-content">
                            <h3>Học như chơi game</h3>
                            <p>Thiết kế bài học như các cấp độ game với thử thách và phần thưởng</p>
                        </div>
                    </div>
                    <div class="feature-comet">
                        <div class="comet-head">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="comet-tail"></div>
                        <div class="feature-content">
                            <h3>Huy hiệu vũ trụ</h3>
                            <p>Thu thập huy hiệu độc đáo từ các chòm sao STEM</p>
                        </div>
                    </div>
                    <div class="feature-comet">
                        <div class="comet-head">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="comet-tail"></div>
                        <div class="feature-content">
                            <h3>Bách khoa vũ trụ</h3>
                            <p>Kho tài liệu phong phú với hình ảnh 3D và mô phỏng</p>
                        </div>
                    </div>
                    <div class="feature-comet">
                        <div class="comet-head">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="comet-tail"></div>
                        <div class="feature-content">
                            <h3>Phi hành đoàn</h3>
                            <p>Kết nối với bạn bè trong cộng đồng phi hành gia nhí</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <script src="/SPNC_HocLieu_STEM_TieuHoc/public/JS/cosmic.js"></script>
</body>
</html>
<?php
}
?>