<?php
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";
$skill_trees = [
    'khoa_hoc' => [
        'name' => 'Khoa học',
        'color' => '#4CAF50',
        'gradient' => 'linear-gradient(135deg, #2d7a3e 0%, #4a9d5f 100%)',
        'icon' => '🔬',
        'description' => 'Khám phá thế giới tự nhiên kỳ diệu',
        'page' => 'science', 
        'skills' => [
            [
                'title' => 'Thế giới tự nhiên',
                'icon' => '🌿',
                'color' => '#4CAF50',
                'lessons' => [
                    ['title' => 'Thế giới màu sắc', 'type' => 'TLCH - TC', 'xp' => 10, 'completed' => true],
                    ['title' => 'Mô hình các bộ phận của cây', 'type' => 'TC', 'xp' => 15, 'completed' => true],
                    ['title' => 'Ngày và đêm', 'type' => 'TLCH', 'xp' => 10, 'completed' => true]
                ]
            ],
            [
                'title' => 'An toàn & Sức khỏe',
                'icon' => '🛡️',
                'color' => '#FF9800',
                'lessons' => [
                    ['title' => 'Cẩm nang phòng tránh hỏa hoạn', 'type' => 'TC', 'xp' => 20, 'completed' => false],
                    ['title' => 'Bí kíp ăn uống lành mạnh', 'type' => 'TC', 'xp' => 15, 'completed' => false]
                ]
            ],
            [
                'title' => 'Cơ thể & Môi trường',
                'icon' => '👤',
                'color' => '#2196F3',
                'lessons' => [
                    ['title' => 'Các cơ quan trong cơ thể', 'type' => 'TC', 'xp' => 25, 'completed' => false],
                    ['title' => 'Khung xương kì diệu', 'type' => 'TC', 'xp' => 20, 'completed' => false],
                    ['title' => 'Thùng rác thân thiện', 'type' => 'TC', 'xp' => 15, 'completed' => false]
                ]
            ]
        ]
    ],
    'cong_nghe' => [
        'name' => 'Công nghệ',
        'color' => '#2196F3',
        'gradient' => 'linear-gradient(135deg, #0d5a7d 0%, #1a7db0 100%)',
        'icon' => '💻',
        'description' => 'Làm chủ công nghệ trong thời đại số',
        'page' => 'technology', 
        'skills' => [
            [
                'title' => 'Công nghệ cơ bản',
                'icon' => '🖥️',
                'color' => '#2196F3',
                'lessons' => [
                    ['title' => 'Các bộ phận của máy tính', 'type' => 'TC', 'xp' => 10, 'completed' => true],
                    ['title' => 'Em là người đánh máy', 'type' => 'TC', 'xp' => 15, 'completed' => false]
                ]
            ],
            [
                'title' => 'Sáng tạo số',
                'icon' => '🎨',
                'color' => '#9C27B0',
                'lessons' => [
                    ['title' => 'Em là họa sĩ máy tính', 'type' => 'Chia sẻ tác phẩm', 'xp' => 20, 'completed' => true],
                    ['title' => 'Tạo thiệp điện tử', 'type' => 'Chia sẻ tác phẩm', 'xp' => 25, 'completed' => false]
                ]
            ],
            [
                'title' => 'Lập trình & Internet',
                'icon' => '🌐',
                'color' => '#FF9800',
                'lessons' => [
                    ['title' => 'Lập trình viên nhí với Scratch', 'type' => 'TC', 'xp' => 30, 'completed' => false],
                    ['title' => 'An toàn trên Internet', 'type' => 'TLCH', 'xp' => 20, 'completed' => false]
                ]
            ]
        ]
    ],
    'ky_thuat' => [
        'name' => 'Kỹ thuật',
        'color' => '#FF9800',
        'gradient' => 'linear-gradient(135deg, #b8620e 0%, #d9792e 100%)',
        'icon' => '⚙️',
        'description' => 'Sáng tạo và xây dựng những điều tuyệt vời',
        'page' => 'engineering', 
        'skills' => [
            [
                'title' => 'Kỹ thuật đơn giản',
                'icon' => '🛠️',
                'color' => '#FF9800',
                'lessons' => [
                    ['title' => 'Dụng cụ gấp áo', 'type' => 'TC', 'xp' => 10, 'completed' => true],
                    ['title' => 'Hoa yêu thương nở rộ', 'type' => 'TC - TLCH', 'xp' => 15, 'completed' => true]
                ]
            ],
            [
                'title' => 'Xây dựng & Thiết kế',
                'icon' => '🏗️',
                'color' => '#795548',
                'lessons' => [
                    ['title' => 'Xây cầu giấy', 'type' => 'TC', 'xp' => 25, 'completed' => false],
                    ['title' => 'Tháp giấy cao nhất', 'type' => 'TC', 'xp' => 20, 'completed' => false]
                ]
            ],
            [
                'title' => 'Sáng chế sáng tạo',
                'icon' => '💡',
                'color' => '#4CAF50',
                'lessons' => [
                    ['title' => 'Chế tạo xe bong bóng', 'type' => 'TC', 'xp' => 30, 'completed' => false],
                    ['title' => 'Hệ thống lọc nước', 'type' => 'TC', 'xp' => 35, 'completed' => false]
                ]
            ]
        ]
    ],
    'toan' => [
        'name' => 'Toán học',
        'color' => '#9C27B0',
        'gradient' => 'linear-gradient(135deg, #5a1f72 0%, #7a389a 100%)',
        'icon' => '🔢',
        'description' => 'Khám phá vẻ đẹp của những con số',
        'page' => 'math', 
        'skills' => [
            [
                'title' => 'Số học cơ bản',
                'icon' => '123',
                'color' => '#9C27B0',
                'lessons' => [
                    ['title' => 'Đếm số', 'type' => 'TC', 'xp' => 10, 'completed' => false],
                    ['title' => 'Phép cộng kỳ diệu', 'type' => 'TC', 'xp' => 15, 'completed' => false]
                ]
            ],
            [
                'title' => 'Hình học & Không gian',
                'icon' => '🔺',
                'color' => '#2196F3',
                'lessons' => [
                    ['title' => 'Nhận biết hình học', 'type' => 'TC', 'xp' => 20, 'completed' => false],
                    ['title' => 'Tangram 3D', 'type' => 'TC', 'xp' => 25, 'completed' => false]
                ]
            ],
            [
                'title' => 'Toán học ứng dụng',
                'icon' => '🛒',
                'color' => '#4CAF50',
                'lessons' => [
                    ['title' => 'Siêu thị của bé', 'type' => 'TC', 'xp' => 30, 'completed' => false],
                    ['title' => 'Máy bắn đá mini', 'type' => 'TC', 'xp' => 35, 'completed' => false]
                ]
            ]
        ]
    ]
];

$total_xp = 0;
$earned_xp = 0;
$total_lessons = 0;
$completed_lessons = 0;

// Setup DB connection for computing real counts from `games` and `scores`.
if (session_status() === PHP_SESSION_NONE) session_start();
$currentUserId = $_SESSION['user_id'] ?? null;
$db = null;
try {
    require_once __DIR__ . '/../models/Database.php';
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    error_log('main_lesson: DB connect error: ' . $e->getMessage());
    $db = null;
}

// Helper: resolve topic id from stem_fields by subject name (best-effort)
$topicIds = [];
if ($db) {
    $fetchTopic = $db->prepare('SELECT id FROM stem_fields WHERE name LIKE :name LIMIT 1');
    foreach ($skill_trees as $subject_id => $subject) {
        try {
            $fetchTopic->execute([':name' => '%' . $subject['name'] . '%']);
            $r = $fetchTopic->fetch(PDO::FETCH_ASSOC);
            $topicIds[$subject_id] = $r ? (int)$r['id'] : null;
        } catch (Exception $e) {
            $topicIds[$subject_id] = null;
        }
    }
}

// Prefer explicit mapping if app uses fixed topic ids
$explicitTopicMap = [
    'toan' => 1,
    'khoa_hoc' => 2,
    'cong_nghe' => 3,
    'ky_thuat' => 4
];
foreach ($explicitTopicMap as $k => $v) {
    $topicIds[$k] = $v;
}

$progress_percentage = 0; // will compute after accumulating totals
require_once './template/header.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bài học - STEM Universe</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/main_lesson.css?v=<?php echo time(); ?>">
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
        <section class="lessons-overview">
            <div class="overview-header">
                <h1>Hành trình học tập của bé</h1>
                <p>Chinh phục từng môn học và thu thập thật nhiều XP!</p>
            </div>

            <div class="subjects-grid">
                <?php foreach ($skill_trees as $subject_id => $subject): ?>
                    <?php 
                        $subject_lessons = 0;
                        $subject_completed = 0;
                        $subject_xp = 0;
                        $subject_earned_xp = 0;

                        $topicId = $topicIds[$subject_id] ?? null;
                        if ($db && $topicId) {
                            try {
                                // total games in this topic
                                $tstmt = $db->prepare('SELECT COUNT(*) AS cnt FROM games WHERE topic_id = :tid');
                                $tstmt->execute([':tid' => $topicId]);
                                $tc = $tstmt->fetch(PDO::FETCH_ASSOC);
                                $subject_lessons = (int)($tc['cnt'] ?? 0);

                                // distinct games played by the user in this topic
                                if (!empty($currentUserId)) {
                                    $cstmt = $db->prepare('SELECT COUNT(DISTINCT s.game_id) AS cnt FROM scores s JOIN games g ON s.game_id = g.id WHERE s.user_id = :uid AND g.topic_id = :tid');
                                    $cstmt->execute([':uid' => $currentUserId, ':tid' => $topicId]);
                                    $cc = $cstmt->fetch(PDO::FETCH_ASSOC);
                                    $subject_completed = (int)($cc['cnt'] ?? 0);
                                }

                                // XP: sum best xp_awarded per game for this user in the topic
                                if (!empty($currentUserId)) {
                                    $xpStmt = $db->prepare(<<<'SQL'
                                        SELECT IFNULL(SUM(b.best_xp),0) as total_xp FROM (
                                          SELECT s.game_id, MAX(s.xp_awarded) as best_xp
                                          FROM scores s
                                          WHERE s.user_id = :uid
                                          GROUP BY s.game_id
                                        ) b JOIN games g ON b.game_id = g.id
                                        WHERE g.topic_id = :tid
                                    SQL
                                    );
                                    $xpStmt->execute([':uid' => $currentUserId, ':tid' => $topicId]);
                                    $sx = $xpStmt->fetch(PDO::FETCH_ASSOC);
                                    $subject_earned_xp = (int)($sx['total_xp'] ?? 0);
                                } else {
                                    $subject_earned_xp = 0;
                                }

                                // Total possible XP for topic: sum games.xp
                                $xpTotalStmt = $db->prepare('SELECT IFNULL(SUM(xp),0) as totxp FROM games WHERE topic_id = :tid');
                                $xpTotalStmt->execute([':tid' => $topicId]);
                                $xpTotRow = $xpTotalStmt->fetch(PDO::FETCH_ASSOC);
                                $subject_xp = (int)($xpTotRow['totxp'] ?? 0);
                            } catch (Exception $e) {
                                error_log('main_lesson: error fetching subject stats: ' . $e->getMessage());
                                // fallback to static
                                foreach ($subject['skills'] as $skill) {
                                    foreach ($skill['lessons'] as $lesson) {
                                        $subject_lessons++;
                                        $subject_xp += $lesson['xp'];
                                        if ($lesson['completed']) {
                                            $subject_completed++;
                                            $subject_earned_xp += $lesson['xp'];
                                        }
                                    }
                                }
                            }
                        } else {
                            // fallback to static sample data when DB or topic mapping not available
                            foreach ($subject['skills'] as $skill) {
                                foreach ($skill['lessons'] as $lesson) {
                                    $subject_lessons++;
                                    $subject_xp += $lesson['xp'];
                                    if ($lesson['completed']) {
                                        $subject_completed++;
                                        $subject_earned_xp += $lesson['xp'];
                                    }
                                }
                            }
                        }

                        $subject_progress = $subject_lessons > 0 ? round(($subject_completed / $subject_lessons) * 100) : 0;

                        // accumulate global totals
                        $total_lessons += $subject_lessons;
                        $completed_lessons += $subject_completed;
                        $total_xp += $subject_xp;
                        $earned_xp += $subject_earned_xp;
                        ?>
                    
                    <div class="subject-card" 
                         data-subject-id="<?php echo $subject_id; ?>" 
                         data-page="<?php echo $subject['page']; ?>"
                         data-page-url="./lessons/<?php echo $subject['page']; ?>.php">
                        <div class="subject-card-header" style="background: <?php echo $subject['gradient']; ?>">
                            <div class="subject-main-info">
                                <div class="subject-icon"><?php echo $subject['icon']; ?></div>
                                <div class="subject-title">
                                    <h3><?php echo $subject['name']; ?></h3>
                                    <p><?php echo $subject['description']; ?></p>
                                </div>
                            </div>
                            <div class="subject-progress-circle">
                                <div class="circle-progress" style="--progress: <?php echo $subject_progress; ?>%">
                                    <span class="progress-text"><?php echo $subject_progress; ?>%</span>
                                </div>
                            </div>
                        </div>
                        <div class="subject-card-content">
                            <div class="subject-stats">
                                <div class="stat">
                                    <div class="stat-info">
                                        <span class="stat-number"><?php echo $subject_completed; ?>/<?php echo $subject_lessons; ?></span>
                                        <span class="stat-label">Bài học</span>
                                    </div>
                                </div>
                                <div class="stat">
                                    <div class="stat-info">
                                        <span class="stat-number"><?php echo $subject_earned_xp; ?> XP</span>
                                        <span class="stat-label">Điểm kinh nghiệm</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="continue-btn" 
                                    style="background: <?php echo $subject['color']; ?>"
                                    data-subject-id="<?php echo $subject_id; ?>"
                                    data-page="<?php echo $subject['page']; ?>"
                                    data-page-url="./lessons/<?php echo $subject['page']; ?>.php">
                                <?php echo $subject_completed > 0 ? 'Tiếp tục học' : 'Bắt đầu học'; ?>
                                <span class="btn-arrow">›</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php require_once './template/footer.php'; ?>

    <script src="<?php echo $base_url; ?>/public/JS/main_lesson.js?v=<?php echo time(); ?>"></script>
 
</body>
</html>