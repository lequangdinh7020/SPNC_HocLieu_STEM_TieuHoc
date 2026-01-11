<?php
session_start();
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";

$engineering_data = [
    'name' => 'KHÁM PHÁ KỸ THUẬT',
    'color' => '#F59E0B',
    'gradient' => 'linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%)',
    'icon' => '⚙️',
    'description' => 'Sáng tạo và xây dựng thế giới quanh em!',
    'total_xp' => 280,
    'completed_xp' => 60,
    'current_streak' => 4,
    'character' => [
        'name' => 'Bạn Thợ Máy Thông Thái',
        'avatar' => '👷‍♂️',
        'color' => '#D97706',
        'welcome_message' => 'Chào nhà kỹ sư nhí! Mình là Thợ Máy Thông Thái! Cùng mình chế tạo 5 dự án siêu thú vị nhé! 👷‍♂️✨'
    ],
    'stats' => [
        'completed' => 1,
        'current' => 1,
        'upcoming' => 7,
        'total_xp' => 60
    ],
    'topics' => [
        [
            'id' => 1,
            'title' => 'XÂY THÁP (CÂY TRE TRĂM ĐỐT)',
            'icon' => '🎋',
            'status' => 'completed',
            'color' => '#3B82F6',
            'description' => 'Học cách xây tháp vững chắc từ câu chuyện Cây tre trăm đốt',
            'learning_time' => '25 phút',
            'activities' => [
                [
                    'type' => 'tutorial',
                    'title' => 'THỬ THÁCH XÂY THÁP',
                    'icon' => '🏗️',
                    'description' => 'Học kỹ thuật xây dựng tháp cao và vững chắc',
                    'status' => 'completed',
                    'xp' => 30
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'SẮP XẾP CĂN PHÒNG CỦA EM',
            'icon' => '🏠',
            'status' => 'current',
            'color' => '#EC4899',
            'description' => 'Thiết kế và sắp xếp không gian sống gọn gàng, hợp lý',
            'learning_time' => '30 phút',
            'activities' => [
                [
                    'type' => 'tutorial',
                    'title' => 'THIẾT KẾ KHÔNG GIAN',
                    'icon' => '🎨',
                    'description' => 'Học về bố cục và sắp xếp đồ đạc thông minh',
                    'status' => 'current',
                    'xp' => 35
                ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'XÂY CẦU GIẤY',
            'icon' => '🌉',
            'status' => 'current',
            'color' => '#8B5CF6',
            'description' => 'Thiết kế và xây dựng cầu từ giấy A4 chịu lực',
            'learning_time' => '35 phút',
            'activities' => [
                [
                    'type' => 'challenge',
                    'title' => 'THỬ THÁCH CẦU GIẤY',
                    'icon' => '🌉',
                    'description' => 'Xây cầu chịu được trọng lượng lớn nhất',
                    'status' => 'current',
                    'xp' => 35
                ]
            ]
        ],
        [
            'id' => 4,
            'title' => 'HỆ THỐNG DẪN NƯỚC',
            'icon' => '🚰',
            'status' => 'current',
            'color' => '#06B6D4',
            'description' => 'Tìm hiểu và thiết kế hệ thống dẫn nước đơn giản',
            'learning_time' => '28 phút',
            'activities' => [
                [
                    'type' => 'experiment',
                    'title' => 'TRÒ CHƠI DẪN NƯỚC',
                    'icon' => '🧪',
                    'description' => 'Chế tạo và thử nghiệm hệ thống dẫn nước',
                    'status' => 'current',
                    'xp' => 40
                ]
            ]
        ],
        [
            'id' => 5,
            'title' => 'HỆ THỐNG LỌC NƯỚC CƠ BẢN',
            'icon' => '💧',
            'status' => 'current',
            'color' => '#06B6D4',
            'description' => 'Tìm hiểu và chế tạo hệ thống lọc nước đơn giản',
            'learning_time' => '40 phút',
            'activities' => [
                [
                    'type' => 'experiment',
                    'title' => 'CHẾ TẠO BỘ LỌC',
                    'icon' => '🧪',
                    'description' => 'Tự làm hệ thống lọc nước từ vật liệu đơn giản',
                    'status' => 'current',
                    'xp' => 40
                ]
            ]
        ]
    ]
];

$subject = $engineering_data;
$current_page = 'engineering';
$progress_percentage = ($subject['completed_xp'] / $subject['total_xp']) * 100;
$first_visit = !isset($_SESSION['engineering_visited']);
$_SESSION['engineering_visited'] = true;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ Mặt Trời Kỹ Thuật - STEM Universe</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/engineering.css?v=<?= time() ?>">
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
                    <h1>HỆ MẶT TRỜI KỸ THUẬT</h1>
                    <p>Khám phá 5 hành tinh sáng tạo</p>
                </div>
                
                <div class="mission-stats">
                    <div class="stat-orb xp-orb">
                        <div class="stat-value">60</div>
                        <div class="stat-label">XP</div>
                    </div>
                </div>
            </div>
        </header>

        <section class="solar-system">
            <div class="sun">⚙️</div>

            <div class="orbit orbit-1"></div>
            <div class="orbit orbit-2"></div>
            <div class="orbit orbit-3"></div>
            <div class="orbit orbit-4"></div>
            <div class="orbit orbit-5"></div>
            
            <div class="planet planet-1 completed" data-planet="1">🎋</div>
            <div class="planet planet-2 current" data-planet="2">🏠</div>
            <div class="planet planet-3" data-planet="3">🌉</div>
            <div class="planet planet-4" data-planet="4">🚰</div>
            <div class="planet planet-5" data-planet="5">💧</div>
        </section>
    </div>

    <div class="planet-info-overlay" id="planetInfoOverlay">
        <div class="planet-info">
            <button class="close-button" id="closeInfo">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="info-header">
                <div class="info-icon" id="infoIcon">🎋</div>
                <div class="info-title">
                    <h3 id="infoName">XÂY THÁP (CÂY TRE TRĂM ĐỐT)</h3>
                    <span class="status" id="infoStatus">Đã hoàn thành</span>
                </div>
            </div>
            
            <p class="info-description" id="infoDescription">
                Học cách xây tháp vững chắc từ câu chuyện Cây tre trăm đốt
            </p>
            
            <div class="activities-section">
                <h4 class="activities-title">Hoạt động</h4>
                <div class="activities-grid" id="activitiesGrid">
                </div>
            </div>
        </div>
    </div>

    <button class="cosmic-character" id="characterBtn">
        👷‍♂️
    </button>
    <script>window.baseUrl = "<?php echo $base_url; ?>";</script>
    <script src="<?php echo $base_url; ?>/public/JS/engineering.js?v=<?= time() ?>"></script>
</body>
</html>