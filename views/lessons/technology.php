<?php
session_start();
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";

$technology_data = [
    'name' => 'KHÁM PHÁ CÔNG NGHỆ',
    'color' => '#3B82F6',
    'gradient' => 'linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%)',
    'icon' => '💻',
    'description' => 'Khám phá thế giới công nghệ đầy thú vị!',
    'total_xp' => 300,
    'completed_xp' => 0, 
    'current_streak' => 0, 
    'character' => [
        'name' => 'Bạn Robot Công Nghệ',
        'avatar' => '🤖',
        'color' => '#2563EB',
        'welcome_message' => 'Xin chào! Mình là Robot Công Nghệ! Cùng mình khám phá 5 chủ đề công nghệ siêu thú vị nhé! 🤖✨'
    ],
    'stats' => [
        'completed' => 0, 
        'current' => 0, 
        'upcoming' => 5, 
        'total_xp' => 0 
    ],
    'topics' => [
        [
            'id' => 1,
            'title' => 'CÂY GIA ĐÌNH',
            'icon' => '🌳',
            'status' => 'not-started', 
            'color' => '#10B981',
            'description' => 'Tìm hiểu về các mối quan hệ gia đình qua cây phả hệ',
            'learning_time' => '20 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI CÂY GIA ĐÌNH',
                    'icon' => '🎮',
                    'description' => 'Xây dựng cây phả hệ gia đình',
                    'status' => 'not-started', 
                    'xp' => 25
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'EM LÀ HỌA SĨ MÁY TÍNH',
            'icon' => '🎨',
            'status' => 'not-started', 
            'color' => '#EC4899',
            'description' => 'Khám phá các công cụ vẽ đơn giản trên máy tính',
            'learning_time' => '25 phút',
            'activities' => [
                [
                    'type' => 'share',
                    'title' => 'CHIA SẺ TÁC PHẨM',
                    'icon' => '🖼️',
                    'description' => 'Chia sẻ bức vẽ của bạn với mọi người',
                    'status' => 'not-started', 
                    'xp' => 20
                ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'EM LÀ NGƯỜI ĐÁNH MÁY',
            'icon' => '⌨️',
            'status' => 'not-started',
            'color' => '#3B82F6',
            'description' => 'Rèn luyện kỹ năng đánh máy nhanh và chính xác',
            'learning_time' => '35 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI ĐÁNH MÁY',
                    'icon' => '🎮',
                    'description' => 'Luyện tập đánh máy qua trò chơi',
                    'status' => 'not-started',
                    'xp' => 40
                ]
            ]
        ],
        [
            'id' => 4,
            'title' => 'SƠN TINH (LẬP TRÌNH KHỐI)',
            'icon' => '🧩',
            'status' => 'not-started',
            'color' => '#8B5CF6',
            'description' => 'Làm quen với lập trình các khối lệnh',
            'learning_time' => '30 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'THỰC HÀNH SCRATCH',
                    'icon' => '🎮',
                    'description' => 'Thực hành lập trình đơn giản',
                    'status' => 'not-started',
                    'xp' => 40
                ]
            ]
        ],
        [
            'id' => 5,
            'title' => 'CÁC BỘ PHẬN CỦA MÁY TÍNH',
            'icon' => '💻',
            'status' => 'not-started',
            'color' => '#6366F1',
            'description' => 'Tìm hiểu các thành phần cơ bản của máy tính',
            'learning_time' => '22 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'GHÉP BỘ PHẬN MÁY TÍNH',
                    'icon' => '🧩',
                    'description' => 'Trò chơi ghép các bộ phận máy tính',
                    'status' => 'not-started',
                    'xp' => 35
                ]
            ]
        ]
    ]
];

$subject = $technology_data;
$current_page = 'technology';
$progress_percentage = ($subject['completed_xp'] / $subject['total_xp']) * 100;
$first_visit = !isset($_SESSION['technology_visited']);
$_SESSION['technology_visited'] = true;

if (!isset($_SESSION['tech_planet_status'])) {
    $_SESSION['tech_planet_status'] = [
        1 => 'not-started',
        2 => 'not-started',
        3 => 'not-started',
        4 => 'not-started',
        5 => 'not-started'
    ];
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Mặt Trời Công Nghệ - STEM Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/technology.css?v=<?= time() ?>">
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
                    <h1>HỆ MẶT TRỜI CÔNG NGHỆ</h1>
                    <p>Khám phá 5 hành tinh tri thức</p>
                </div>
                
                <div class="mission-stats">
                    <div class="stat-orb xp-orb">
                        <div class="stat-value">0</div>
                        <div class="stat-label">XP</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="solar-system">
            <div class="sun">💻</div>

            <div class="orbit orbit-1"></div>
            <div class="orbit orbit-2"></div>
            <div class="orbit orbit-3"></div>
            <div class="orbit orbit-4"></div>
            <div class="orbit orbit-5"></div>
            
            <div class="planet planet-1 not-started" data-planet="1">🌳</div>
            <div class="planet planet-2 not-started" data-planet="2">🎨</div>
            <div class="planet planet-3 not-started" data-planet="3">⌨️</div>
            <div class="planet planet-4 not-started" data-planet="4">🧩</div>
            <div class="planet planet-5 not-started" data-planet="5">💻</div>
        </section>
    </div>

    <div class="planet-info-overlay" id="planetInfoOverlay">
        <div class="planet-info">
            <button class="close-button" id="closeInfo">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="info-header">
                <div class="info-icon" id="infoIcon">🌳</div>
                <div class="info-title">
                    <h3 id="infoName">CÂY GIA ĐÌNH</h3>
                    <span class="status" id="infoStatus">Chưa học</span>
                </div>
            </div>
            
            <p class="info-description" id="infoDescription">
                Tìm hiểu về các mối quan hệ gia đình qua cây phả hệ
            </p>
            
            <div class="activities-section">
                <h4 class="activities-title">Hoạt động</h4>
                <div class="activities-grid" id="activitiesGrid">
                </div>
            </div>
        </div>
    </div>

    <button class="cosmic-character" id="characterBtn">
        🤖
    </button>
    <script>
        window.baseUrl = "<?php echo $base_url; ?>";
        window.techPlanetStatuses = <?php echo json_encode($_SESSION['tech_planet_status']); ?>;
    </script>
    <script src="<?php echo $base_url; ?>/public/JS/technology.js?v=<?= time() ?>"></script>
</body>
</html>