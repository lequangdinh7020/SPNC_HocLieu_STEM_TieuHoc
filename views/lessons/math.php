<?php
session_start();
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";

$math_data = [
    'name' => 'KHÁM PHÁ TOÁN HỌC',
    'color' => '#8B5CF6',
    'gradient' => 'linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%)',
    'icon' => '🧮',
    'description' => 'Khám phá thế giới số học đầy màu sắc!',
    'total_xp' => 290,
    'completed_xp' => 0, 
    'current_streak' => 0,
    'character' => [
        'name' => 'Bạn Thỏ Toán Học',
        'avatar' => '🐰',
        'color' => '#7C3AED',
        'welcome_message' => 'Chào bạn nhỏ! Mình là Thỏ Toán Học! Cùng mình khám phá 5 chủ đề toán học siêu vui nhé! 🐰✨'
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
            'title' => 'HẬU NGHỆ BẮN MẶT TRỜI',
            'icon' => '🎯',
            'status' => 'not-started',
            'color' => '#EF4444',
            'description' => 'Trò chơi máy bắn đá mini học về lực và góc bắn',
            'learning_time' => '22 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'CHẾ TẠO MÁY BẮN ĐÁ',
                    'icon' => '🎮',
                    'description' => 'Trò chơi chế tạo máy bắn đá từ vật liệu đơn giản',
                    'status' => 'not-started', 
                    'xp' => 35
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'NHẬN BIẾT HÌNH HỌC',
            'icon' => '🔺',
            'status' => 'not-started', 
            'color' => '#3B82F6',
            'description' => 'Trò chơi học về các hình học qua thử thách',
            'learning_time' => '18 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'THỬ THÁCH HÌNH HỌC',
                    'icon' => '🧩',
                    'description' => 'Trò chơi phân loại các hình học khác nhau',
                    'status' => 'not-started', 
                    'xp' => 25
                ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'TANGRAM 3D',
            'icon' => '🧩',
            'status' => 'not-started',
            'color' => '#10B981',
            'description' => 'Trò chơi tangram không gian 3 chiều thú vị',
            'learning_time' => '25 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'GIỚI THIỆU TANGRAM 3D',
                    'icon' => '🎮',
                    'description' => 'Trò chơi làm quen với tangram 3D',
                    'status' => 'not-started',
                    'xp' => 30
                ],
                [
                    'type' => 'game',
                    'title' => 'GHÉP HÌNH TANGRAM 3D',
                    'icon' => '🔷',
                    'description' => 'Thử thách ghép hình với tangram 3D',
                    'status' => 'not-started',
                    'xp' => 40
                ]
            ]
        ],
        [
            'id' => 4,
            'title' => 'ĐẾM SỐ THÔNG MINH',
            'icon' => '🔢',
            'status' => 'not-started',
            'color' => '#F59E0B',
            'description' => 'Trò chơi học đếm số và nhận biết số thú vị',
            'learning_time' => '20 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI ĐẾM SỐ',
                    'icon' => '🎲',
                    'description' => 'Trò chơi học đếm từ 1 đến 100',
                    'status' => 'not-started',
                    'xp' => 25
                ]
            ]
        ],
        [
            'id' => 5,
            'title' => 'ĐỒNG HỒ THỜI GIAN',
            'icon' => '⏰',
            'status' => 'not-started',
            'color' => '#EC4899',
            'description' => 'Trò chơi học xem đồng hồ và quản lý thời gian',
            'learning_time' => '28 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'QUẢN LÝ THỜI GIAN',
                    'icon' => '⏳',
                    'description' => 'Trò chơi thực hành quản lý thời gian hàng ngày',
                    'status' => 'not-started',
                    'xp' => 45
                ]
            ]
        ]
    ]
];

$subject = $math_data;
$current_page = 'math';
$progress_percentage = ($subject['completed_xp'] / $subject['total_xp']) * 100;
$first_visit = !isset($_SESSION['math_visited']);
$_SESSION['math_visited'] = true;

if (!isset($_SESSION['math_planet_status'])) {
    $_SESSION['math_planet_status'] = [
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
    <title>Hệ Mặt Trời Toán Học - STEM Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/math.css?v=<?= time() ?>">
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
                    <h1>HỆ MẶT TRỜI TOÁN HỌC</h1>
                    <p>Khám phá 5 hành tinh số học</p>
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
            <div class="sun">🧮</div>

            <div class="orbit orbit-1"></div>
            <div class="orbit orbit-2"></div>
            <div class="orbit orbit-3"></div>
            <div class="orbit orbit-4"></div>
            <div class="orbit orbit-5"></div>
            
            <div class="planet planet-1 not-started" data-planet="1">🎯</div>
            <div class="planet planet-2 not-started" data-planet="2">🔺</div>
            <div class="planet planet-3 not-started" data-planet="3">🧩</div>
            <div class="planet planet-4 not-started" data-planet="4">🔢</div>
            <div class="planet planet-5 not-started" data-planet="5">⏰</div>
        </section>
    </div>

    <div class="planet-info-overlay" id="planetInfoOverlay">
        <div class="planet-info">
            <button class="close-button" id="closeInfo">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="info-header">
                <div class="info-icon" id="infoIcon">🎯</div>
                <div class="info-title">
                    <h3 id="infoName">HẬU NGHỆ BẮN MẶT TRỜI</h3>
                    <span class="status" id="infoStatus">Chưa học</span>
                </div>
            </div>
            
            <p class="info-description" id="infoDescription">
                Trò chơi máy bắn đá mini học về lực và góc bắn
            </p>
            
            <div class="activities-section">
                <h4 class="activities-title">Hoạt động</h4>
                <div class="activities-grid" id="activitiesGrid">
                </div>
            </div>
        </div>
    </div>

    <button class="cosmic-character" id="characterBtn">
        🐰
    </button>
    <script>
        window.baseUrl = "<?php echo $base_url; ?>";
        window.mathPlanetStatuses = <?php echo json_encode($_SESSION['math_planet_status']); ?>;
    </script>
    <script src="<?php echo $base_url; ?>/public/JS/math.js?v=<?= time() ?>"></script>
</body>
</html>