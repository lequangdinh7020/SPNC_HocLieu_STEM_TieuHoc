<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$base_url = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/SPNC_HocLieu_STEM_TieuHoc';


$subjects = [
    'khoa_hoc' => [
        'name' => 'Khoa học',
        'color' => '#4CAF50',
        'gradient' => 'linear-gradient(135deg, #2d7a3e 0%, #4a9d5f 100%)',
        'icon' => '🔬',
        'lessons' => [
            ['title' => 'Thế giới màu sắc', 'type' => 'Trò chơi', 'has_video' => true, 'status' => 'complete', 'url' => 'science_color_game'],
            ['title' => 'Bí kíp ăn uống lành mạnh', 'type' => 'Trò chơi', 'has_video' => true, 'status' => 'complete', 'url' => 'science_nutrition_game'],
            ['title' => 'Ngày và đêm', 'type' => 'Trả lời câu hỏi', 'has_video' => true, 'status' => 'complete', 'url' => 'science_day_night'],
            ['title' => 'Thùng rác thân thiện', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'science_trash_game'],
            ['title' => 'Các bộ phận của cây', 'type' => 'Trò chơi', 'has_video' => true, 'status' => 'complete', 'url' => 'science_plant_game'],
        ]
    ],
    'cong_nghe' => [
        'name' => 'Công nghệ',
        'color' => '#2196F3',
        'gradient' => 'linear-gradient(135deg, #0d5a7d 0%, #1a7db0 100%)',
        'icon' => '💻',
        'lessons' => [
            ['title' => 'Cây gia đình', 'type' => 'Trò chơi', 'has_video' => true, 'status' => 'complete', 'url' => 'technology_family_tree_game'],
            ['title' => 'Em là họa sĩ máy tính', 'type' => 'Chia sẻ tác phẩm', 'has_video' => true, 'status' => 'complete', 'url' => 'technology_painter_game'],
            ['title' => 'Em là người đánh máy', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'technology_typing_thach_sanh'],
            ['title' => 'Sơn Tinh (lập trình khối)', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'technology_coding_game'],
            ['title' => 'Các bộ phận của máy tính', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'technology_computer_parts'],
        ]
    ],
    'ky_thuat' => [
        'name' => 'Kỹ thuật',
        'color' => '#FF9800',
        'gradient' => 'linear-gradient(135deg, #b8620e 0%, #d9792e 100%)',
        'icon' => '⚙️',
        'lessons' => [
            ['title' => 'Xây tháp', 'type' => 'Trò chơi', 'has_video' => true, 'status' => 'complete', 'url' => 'engineering_tower_game'],
            ['title' => 'Sắp xếp căn phòng của em', 'type' => 'Trò chơi', 'has_video' => true, 'status' => 'complete', 'url' => 'engineering_room_decor'],
            ['title' => 'Xây cầu', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'engineering_bridge_game'],
            ['title' => 'Hệ thống dẫn nước', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'engineering_water_pipe'],
            ['title' => 'Hệ thống lọc nước cơ bản', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'engineering_water_filter'],
        ]
    ],
    'toan' => [
        'name' => 'Toán học',
        'color' => '#9C27B0',
        'gradient' => 'linear-gradient(135deg, #5a1f72 0%, #7a389a 100%)',
        'icon' => '🔢',
        'lessons' => [
            ['title' => 'Hậu Nghệ bắn mặt trời', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'math_angle_game'],
            ['title' => 'Nhận biết hình học', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'math_shapes_challenge'],
            ['title' => 'Tangram 3D', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'math_tangram_3d'],
            ['title' => 'Đếm số thông minh', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'math_number_game'],
            ['title' => 'Đồng hồ và thời gian', 'type' => 'Trò chơi', 'has_video' => false, 'status' => 'incomplete', 'url' => 'math_time_game'],
        ]
    ]
];

$search_results = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = strtolower($_GET['search']);
    foreach ($subjects as $subject) {
        foreach ($subject['lessons'] as $lesson) {
            if (strpos(strtolower($lesson['title']), $search_term) !== false) {
                $search_results[] = [
                    'subject' => $subject['name'],
                    'subject_gradient' => $subject['gradient'],
                    'lesson' => $lesson
                ];
            }
        }
    }
}

$total = $done = 0;
foreach ($subjects as $subject) {
    $total += count($subject['lessons']);
    foreach ($subject['lessons'] as $lesson) {
        if ($lesson['status'] === 'complete') $done++;
    }
}
$progress = $total ? round(($done / $total) * 100) : 0;

require_once './template/header.php';


$totalLessons = 20;
if (session_status() == PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../models/Database.php';
        $database = new Database();
        $db = $database->getConnection();
        $stmt = $db->prepare(<<<'SQL'
    SELECT COUNT(*) as cnt FROM (
      SELECT s.game_id, MAX(s.score_percentage) as best
      FROM scores s
      WHERE s.user_id = :uid
      GROUP BY s.game_id
    ) b JOIN games g ON b.game_id = g.id
    WHERE g.passing_score IS NOT NULL AND b.best >= g.passing_score
    SQL
        );
        $stmt->execute([':uid' => $_SESSION['user_id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $completedCount = $row ? (int)$row['cnt'] : 0;
    } catch (Exception $e) {
        error_log('Home progress load error: ' . $e->getMessage());
        $completedCount = 0;
    }
    $done = $completedCount;
    $total = $totalLessons;
    $progress = $total ? round(($done / $total) * 100) : 0;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STEM Universe - Học liệu STEM Tiểu học</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/home.css?v=<?php echo time(); ?>">
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

    <main class="container">
        <?php if (!empty($search_results)): ?>
            <section class="search-results-section">
                <div class="section-header">
                    <h2>Kết quả tìm kiếm cho "<?php echo htmlspecialchars($_GET['search']); ?>"</h2>
                    <p>Tìm thấy <?php echo count($search_results); ?> bài học</p>
                </div>
                <div class="results-grid">
                    <?php foreach ($search_results as $result): ?>
                        <div class="result-card">
                            <div class="result-badge" style="background: <?php echo $result['subject_gradient']; ?>">
                                <?php echo $result['subject']; ?>
                            </div>
                            <div class="result-content">
                                <h3><?php echo $result['lesson']['title']; ?></h3>
                                <div class="result-meta">
                                    <span class="lesson-type"><?php echo $result['lesson']['type']; ?></span>
                                    <?php if ($result['lesson']['has_video']): ?>
                                        <span class="video-tag">📹 Video</span>
                                    <?php endif; ?>
                                </div>
                                <button class="start-lesson-btn" onclick="openLesson('<?php echo $result['lesson']['title']; ?>')">
                                    Bắt đầu học
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <a href="?" class="back-home-btn">
                    <span>← Quay lại trang chủ</span>
                </a>
            </section>
        <?php else: ?>
            <section class="hero-section">
                <div class="hero-content">
                    <div class="hero-text">
                        <h2>Chào mừng đến với <span class="highlight">STEM Universe!</span></h2>
                        <p>Khám phá thế giới STEM đầy sáng tạo. Nền tảng học liệu tương tác cho học sinh tiểu học Việt Nam.</p>
                        <div class="hero-stats">
                            <div class="stat">
                                <div class="stat-number"><?php echo $total; ?></div>
                                <div class="stat-label">Bài học</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number"><?php echo $done; ?></div>
                                <div class="stat-label">Đã hoàn thành</div>
                            </div>
                            <div class="stat">
                                <div class="stat-number"><?php echo $progress; ?>%</div>
                                <div class="stat-label">Tiến độ</div>
                            </div>
                        </div>
                    </div>
                    <div class="hero-visual">
                        <div class="floating-elements">
                            <div class="floating-element element-1">🔬</div>
                            <div class="floating-element element-2">💻</div>
                            <div class="floating-element element-3">⚙️</div>
                            <div class="floating-element element-4">🔢</div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="progress-section">
                <div class="progress-card">
                    <h3>Tiến độ học tập của bạn</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                    <div class="progress-text">
                        <span>Đã hoàn thành: <?php echo $done; ?>/<?php echo $total; ?> bài học</span>
                        <span><?php echo $progress; ?>%</span>
                    </div>
                </div>
            </section>

            <section class="subjects-section">
                <div class="section-header">
                    <h2>Khám phá các môn học</h2>
                    <p>Chọn môn học yêu thích và bắt đầu hành trình</p>
                </div>
                
                <div class="subjects-container">
                    <button class="subjects-nav prev" onclick="scrollSubjects(-1)">
                        <span class="nav-arrow">‹</span>
                    </button>
                    
                    <div class="subjects-wrapper">
                        <div class="subjects-track" id="subjectsTrack">
                            <?php foreach ($subjects as $subject_id => $subject): ?>
                                <div class="subject-card" style="--subject-color: <?php echo $subject['color']; ?>">
                                    <div class="card-header" style="background: <?php echo $subject['gradient']; ?>">
                                        <div class="subject-icon"><?php echo $subject['icon']; ?></div>
                                        <div class="subject-info">
                                            <h3><?php echo $subject['name']; ?></h3>
                                        </div>
                                    </div>
                                    <div class="card-content">
                                        <div class="lessons-count">
                                            <span><?php echo count($subject['lessons']); ?> bài học</span>
                                        </div>
                                        <div class="lessons-list">
                                            <?php foreach ($subject['lessons'] as $index => $lesson): ?>
                                                <div class="lesson-item" 
                                                     onclick="openLesson('<?php echo $lesson['url']; ?>')">
                                                    <div class="lesson-preview"></div>
                                                    <div class="lesson-details">
                                                        <h4><?php echo $lesson['title']; ?></h4>
                                                        <div class="lesson-meta">
                                                            <span class="lesson-type"><?php echo $lesson['type']; ?></span>
                                                            <?php if ($lesson['has_video']): ?>
                                                                <span class="video-indicator" title="Có video">📹</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <button class="subjects-nav next" onclick="scrollSubjects(1)">
                        <span class="nav-arrow">›</span>
                    </button>
                </div>
                
                <div class="subjects-dots" id="subjectsDots"></div>
            </section>

            <section class="featured-section">
                <div class="section-header">
                    <h2>Bài học nổi bật</h2>
                    <p>Khám phá những bài học thú vị nhất</p>
                </div>
                <div class="featured-grid">
                    <div class="featured-card featured-1">
                        <div class="featured-badge">Phổ biến</div>
                        <h3>Thế giới màu sắc</h3>
                        <p>Khám phá sự kỳ diệu của màu sắc trong tự nhiên</p>
                        <button class="featured-btn" onclick="openLesson('Thế giới màu sắc')">Khám phá ngay</button>
                    </div>
                    <div class="featured-card featured-2">
                        <div class="featured-badge">Mới</div>
                        <h3>Em là họa sĩ máy tính</h3>
                        <p>Sáng tạo nghệ thuật với công cụ số</p>
                        <button class="featured-btn" onclick="openLesson('Em là họa sĩ máy tính')">Bắt đầu vẽ</button>
                    </div>
                    <div class="featured-card featured-3">
                        <div class="featured-badge">Thử thách</div>
                        <h3>Xây cầu giấy</h3>
                        <p>Kỹ thuật xây dựng với vật liệu đơn giản</p>
                        <button class="featured-btn" onclick="openLesson('Xây cầu giấy')">Nhận thử thách</button>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php require_once './template/footer.php'; ?>                                                       
    <script src="<?php echo $base_url; ?>/public/JS/main_lesson.js?v=<?php echo time(); ?>"></script>
    <script>
    function openLesson(lessonTitle) {
        window.location.href = `lessons/${encodeURIComponent(lessonTitle)}`;
    }
    </script>
</body>
</html>