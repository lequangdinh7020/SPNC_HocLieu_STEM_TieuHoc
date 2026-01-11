<?php
session_start();
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";

$science_data = [
    'name' => 'KHÁM PHÁ KHOA HỌC',
    'color' => '#22C55E',
    'gradient' => 'linear-gradient(135deg, #22C55E 0%, #4ADE80 100%)',
    'icon' => '🔬',
    'description' => 'Cùng khám phá thế giới diệu kỳ!',
    'total_xp' => 280, 
    'completed_xp' => 100,
    'current_streak' => 7,
    'character' => [
        'name' => 'Bạn Khủng Long Khoa Học',
        'avatar' => '🦖',
        'color' => '#10B981',
        'welcome_message' => 'Chào bạn nhỏ! Mình là Khủng Long Khoa Học! Cùng mình khám phá các chủ đề siêu thú vị nhé! 🦖✨'
    ],
    'stats' => [
        'completed' => 2,
        'current' => 1,
        'upcoming' => 7, 
        'total_xp' => 100
    ],
    'topics' => [
        [
            'id' => 1,
            'title' => 'THẾ GIỚI MÀU SẮC',
            'icon' => '🎨',
            'status' => 'completed',
            'color' => '#22C55E',
            'description' => 'Khám phá bí mật của màu sắc qua các hoạt động thú vị',
            'learning_time' => '15 phút',
            'activities' => [
                [ 'type' => 'game', 'title' => 'TRÒ CHƠI PHA MÀU', 'icon' => '🎮', 'status' => 'completed', 'xp' => 25 ]
            ]
        ],
        [ 
            'id' => 2,
            'title' => 'BÍ KÍP ĂN UỐNG LÀNH MẠNH',
            'icon' => '🍎',
            'status' => 'completed',
            'color' => '#10B981',
            'description' => 'Học cách chọn thực phẩm tốt cho sức khỏe',
            'learning_time' => '20 phút',
            'activities' => [
                [ 'type' => 'game', 'title' => 'TRÒ CHƠI THÁP DINH DƯỠNG', 'icon' => '🧩', 'status' => 'completed', 'xp' => 50 ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'NGÀY VÀ ĐÊM',
            'icon' => '🌓',
            'status' => 'current',
            'color' => '#3B82F6',
            'description' => 'Khám phá bí mật của thời gian và thiên văn',
            'learning_time' => '12 phút',
            'activities' => [
                [ 'type' => 'question', 'title' => 'TRẢ LỜI CÂU HỎI', 'icon' => '🌞', 'status' => 'current', 'xp' => 50 ]
            ]
        ],

        [ 
            'id' => 4,
            'title' => 'THÙNG RÁC THÂN THIỆN',
            'icon' => '🗑️',
            'status' => 'upcoming',
            'color' => '#84CC16',
            'description' => 'Học cách phân loại rác bảo vệ môi trường',
            'learning_time' => '16 phút',
            'activities' => [
                [ 'type' => 'game', 'title' => 'TRÒ CHƠI PHÂN LOẠI RÁC', 'icon' => '♻️', 'status' => 'upcoming', 'xp' => 30 ]
            ]
        ],

        [
            'id' => 5,
            'title' => 'CÁC BỘ PHẬN CỦA CÂY',
            'icon' => '🌱',
            'status' => 'upcoming',
            'color' => '#16a085',
            'description' => 'Học cách nhận biết các bộ phận của cây',
            'learning_time' => '10 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI LẮP GHÉP',
                    'icon' => '🌿',
                    'description' => 'Lắp ghép các bộ phận của cây',
                    'status' => 'upcoming',
                    'xp' => 30
                ]
            ]
        ]
    ]
];

$subject = $science_data;
$current_page = 'science';
$progress_percentage = ($subject['completed_xp'] / $subject['total_xp']) * 100;
$first_visit = !isset($_SESSION['science_visited']);
$_SESSION['science_visited'] = true;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Mặt Trời Khoa Học - STEM Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/science.css?v=<?= time() ?>">
</head>
<body>
    <div class="cosmic-universe">
        <div class="stars"></div>
    </div>

    <div class="universe-container">
        <header class="cosmic-header">
            <div class="header-content">
                <div class="mission-control">
                    <a href="<?php echo $base_url; ?>/views/main_lesson.php" class="nav-button">
                        <i class="fas fa-home"></i>
                    </a>
                </div>
                
                <div class="mission-title">
                    <h1>HỆ MẶT TRỜI KHOA HỌC</h1>
                    <p>Khám phá 5 hành tinh tri thức</p>
                </div>
                
                <div class="mission-stats">
                    <div class="stat-orb xp-orb">
                        <div class="stat-value">100</div>
                        <div class="stat-label">XP</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="solar-system">

            <div class="sun">🔬</div>

            <div class="orbit orbit-1"></div>
            <div class="orbit orbit-2"></div>
            <div class="orbit orbit-3"></div>
            <div class="orbit orbit-4"></div>
            <div class="orbit orbit-5"></div>
            
            <div class="planet planet-1 completed" data-planet="1">🎨</div>
            <div class="planet planet-2 completed" data-planet="2">🍎</div>
            <div class="planet planet-3 current" data-planet="3">🌓</div>
            <div class="planet planet-4" data-planet="4">🗑️</div>
            <div class="planet planet-5" data-planet="5">🌱</div>
        </section>
    </div>

    <div class="planet-info-overlay" id="planetInfoOverlay">
        <div class="planet-info">
            <button class="close-button" id="closeInfo">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="info-header">
                <div class="info-icon" id="infoIcon">🎨</div>
                <div class="info-title">
                    <h3 id="infoName">THẾ GIỚI MÀU SẮC</h3>
                    <span class="status" id="infoStatus">Đã hoàn thành</span>
                </div>
            </div>
            
            <p class="info-description" id="infoDescription">
                Khám phá bí mật của màu sắc qua các hoạt động thú vị và trò chơi pha màu
            </p>
            
            <div class="activities-section">
                <h4 class="activities-title">Hoạt động</h4>
                <div class="activities-grid" id="activitiesGrid">
                </div>
            </div>
        </div>
    </div>

    <button class="cosmic-character" id="characterBtn">
        🦖
    </button>
    <script>window.baseUrl = "<?php echo $base_url; ?>";</script>
    <script src="<?php echo $base_url; ?>/public/JS/science.js?v=<?= time() ?>"></script>

</body>
</html>
