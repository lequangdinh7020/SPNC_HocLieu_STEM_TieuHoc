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
    'completed_xp' => 75,
    'current_streak' => 5,
    'character' => [
        'name' => 'Bạn Robot Công Nghệ',
        'avatar' => '🤖',
        'color' => '#2563EB',
        'welcome_message' => 'Xin chào! Mình là Robot Công Nghệ! Cùng mình khám phá 5 chủ đề công nghệ siêu thú vị nhé! 🤖✨'
    ],
    'stats' => [
        'completed' => 1,
        'current' => 1,
        'upcoming' => 7,
        'total_xp' => 75
    ],
    'topics' => [
        [
            'id' => 1,
            'title' => 'CÂY GIA ĐÌNH',
            'icon' => '🌳',
            'status' => 'completed',
            'color' => '#10B981',
            'description' => 'Tìm hiểu về các mối quan hệ gia đình qua cây phả hệ',
            'learning_time' => '20 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI CÂY GIA ĐÌNH',
                    'icon' => '🎮',
                    'description' => 'Xây dựng cây phả hệ gia đình',
                    'status' => 'completed',
                    'xp' => 25
                ]
            ]
        ],
        [
            'id' => 2,
            'title' => 'EM LÀ HỌA SĨ MÁY TÍNH',
            'icon' => '🎨',
            'status' => 'current',
            'color' => '#EC4899',
            'description' => 'Khám phá các công cụ vẽ đơn giản trên máy tính',
            'learning_time' => '25 phút',
            'activities' => [
                [
                    'type' => 'tutorial',
                    'title' => 'GIỚI THIỆU CÔNG CỤ VẼ',
                    'icon' => '📝',
                    'description' => 'Tìm hiểu các công cụ vẽ cơ bản',
                    'status' => 'current',
                    'xp' => 30
                ],
                [
                    'type' => 'share',
                    'title' => 'CHIA SẺ TÁC PHẨM',
                    'icon' => '🖼️',
                    'description' => 'Chia sẻ bức vẽ của bạn với mọi người',
                    'status' => 'upcoming',
                    'xp' => 20
                ]
            ]
        ],
        [
            'id' => 3,
            'title' => 'AN TOÀN TRÊN INTERNET',
            'icon' => '🛡️',
            'status' => 'upcoming',
            'color' => '#F59E0B',
            'description' => 'Học các quy tắc cơ bản khi sử dụng Internet',
            'learning_time' => '18 phút',
            'activities' => [
                [
                    'type' => 'video',
                    'title' => 'QUY TẮC INTERNET',
                    'icon' => '📺',
                    'description' => 'Xem video về an toàn trên mạng',
                    'status' => 'upcoming',
                    'xp' => 25
                ],
                [
                    'type' => 'question',
                    'title' => 'TRẢ LỜI CÂU HỎI',
                    'icon' => '❓',
                    'description' => 'Kiểm tra kiến thức an toàn mạng',
                    'status' => 'upcoming',
                    'xp' => 25
                ]
            ]
        ],
        [
            'id' => 4,
            'title' => 'SƠN TINH (LẬP TRÌNH KHỐI)',
            'icon' => '🧩',
            'status' => 'upcoming',
            'color' => '#8B5CF6',
            'description' => 'Làm quen với lập trình qua nền tảng Scratch',
            'learning_time' => '30 phút',
            'activities' => [
                [
                    'type' => 'video',
                    'title' => 'GIỚI THIỆU SCRATCH',
                    'icon' => '📺',
                    'description' => 'Xem video giới thiệu về Scratch',
                    'status' => 'upcoming',
                    'xp' => 30
                ],
                [
                    'type' => 'game',
                    'title' => 'THỰC HÀNH SCRATCH',
                    'icon' => '🎮',
                    'description' => 'Thực hành lập trình đơn giản',
                    'status' => 'upcoming',
                    'xp' => 40
                ]
            ]
        ],
        [
            'id' => 5,
            'title' => 'CÁC BỘ PHẬN CỦA MÁY TÍNH',
            'icon' => '💻',
            'status' => 'upcoming',
            'color' => '#6366F1',
            'description' => 'Tìm hiểu các thành phần cơ bản của máy tính',
            'learning_time' => '22 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'GHÉP BỘ PHẬN MÁY TÍNH',
                    'icon' => '🧩',
                    'description' => 'Trò chơi ghép các bộ phận máy tính',
                    'status' => 'upcoming',
                    'xp' => 35
                ]
            ]
        ],
        [
            'id' => 6,
            'title' => 'TẠO MỘT TẤM THIỆP ĐIỆN TỬ',
            'icon' => '💌',
            'status' => 'upcoming',
            'color' => '#EC4899',
            'description' => 'Học cách tạo thiệp điện tử và chia sẻ tác phẩm',
            'learning_time' => '28 phút',
            'activities' => [
                [
                    'type' => 'tutorial',
                    'title' => 'THIẾT KẾ THIỆP',
                    'icon' => '🎨',
                    'description' => 'Học cách thiết kế thiệp điện tử',
                    'status' => 'upcoming',
                    'xp' => 30
                ],
                [
                    'type' => 'share',
                    'title' => 'CHIA SẺ TÁC PHẨM',
                    'icon' => '📤',
                    'description' => 'Chia sẻ thiệp với bạn bè',
                    'status' => 'upcoming',
                    'xp' => 25
                ]
            ]
        ],
        [
            'id' => 7,
            'title' => 'EM LÀ NGƯỜI ĐÁNH MÁY',
            'icon' => '⌨️',
            'status' => 'upcoming',
            'color' => '#10B981',
            'description' => 'Rèn luyện kỹ năng đánh máy nhanh và chính xác',
            'learning_time' => '35 phút',
            'activities' => [
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI ĐÁNH MÁY',
                    'icon' => '🎮',
                    'description' => 'Luyện tập đánh máy qua trò chơi',
                    'status' => 'upcoming',
                    'xp' => 40
                ],
                [
                    'type' => 'practice',
                    'title' => 'THỰC HÀNH TỐC KÝ',
                    'icon' => '⚡',
                    'description' => 'Luyện tập tốc độ đánh máy',
                    'status' => 'upcoming',
                    'xp' => 35
                ]
            ]
        ],
        [
            'id' => 8,
            'title' => 'TÌM KIẾM THÔNG TIN HỮU ÍCH',
            'icon' => '🔍',
            'status' => 'upcoming',
            'color' => '#F59E0B',
            'description' => 'Học kỹ năng tìm kiếm thông tin trên Internet',
            'learning_time' => '32 phút',
            'activities' => [
                [
                    'type' => 'tutorial',
                    'title' => 'KỸ NĂNG TÌM KIẾM',
                    'icon' => '📚',
                    'description' => 'Học cách tìm kiếm hiệu quả',
                    'status' => 'upcoming',
                    'xp' => 35
                ],
                [
                    'type' => 'practice',
                    'title' => 'THỰC HÀNH TÌM KIẾM',
                    'icon' => '🔎',
                    'description' => 'Thực hành tìm kiếm thông tin',
                    'status' => 'upcoming',
                    'xp' => 30
                ]
            ]
        ],
        [
            'id' => 9,
            'title' => 'KHÁM PHÁ THẾ GIỚI QUA BẢN ĐỒ SỐ',
            'icon' => '🗺️',
            'status' => 'upcoming',
            'color' => '#3B82F6',
            'description' => 'Tìm hiểu về bản đồ số và khám phá thế giới',
            'learning_time' => '26 phút',
            'activities' => [
                [
                    'type' => 'explore',
                    'title' => 'KHÁM PHÁ BẢN ĐỒ',
                    'icon' => '🌍',
                    'description' => 'Khám phá thế giới qua bản đồ số',
                    'status' => 'upcoming',
                    'xp' => 30
                ],
                [
                    'type' => 'game',
                    'title' => 'TRÒ CHƠI ĐỊA LÝ',
                    'icon' => '🎯',
                    'description' => 'Trò chơi tìm hiểu địa lý',
                    'status' => 'upcoming',
                    'xp' => 25
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
                        <div class="stat-value">75</div>
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
            
            <div class="planet planet-1 completed" data-planet="1">🌳</div>
            <div class="planet planet-2 current" data-planet="2">🎨</div>
            <div class="planet planet-3" data-planet="3">⌨️</div>
            <div class="planet planet-4" data-planet="4">🧩</div>
            <div class="planet planet-5" data-planet="5">💻</div>
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
                    <span class="status" id="infoStatus">Đã hoàn thành</span>
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
    <script>window.baseUrl = "<?php echo $base_url; ?>";</script>
    <script src="<?php echo $base_url; ?>/public/JS/technology.js?v=<?= time() ?>"></script>
</body>
</html>