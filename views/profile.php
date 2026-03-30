<?php
$current_page = 'profile';

$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$base_url = rtrim($base_url, '/\\');

require_once './template/header.php';

$user = [
    'id' => null,
    'username' => 'Khách',
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'class' => '',
    'avatar' => null,
    'role' => 'user'
];

$stats = [
    'total_lessons' => 0,
    'works_count' => 0,
    'scores_count' => 0,
    'active_days' => 0,
    'avg_score' => 0,
    'total_xp' => 0,
    'completed_lessons' => 0
];

if (!empty($_SESSION['user_id'])) {
    try {
    require_once __DIR__ . '/../models/Database.php';
        $database = new Database();
        $db = $database->getConnection();

        if ($db) {
            $stmt = $db->prepare("SELECT id, username, email, first_name, last_name, class, avatar, role FROM users WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $_SESSION['user_id']]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $user = array_merge($user, $row);
            }

            $completedStmt = $db->prepare(<<<'SQL'
    SELECT COUNT(*) as cnt FROM (
      SELECT s.game_id, MAX(s.score_percentage) as best
      FROM scores s
      WHERE s.user_id = :uid
      GROUP BY s.game_id
    ) b JOIN games g ON b.game_id = g.id
    WHERE g.passing_score IS NOT NULL AND b.best >= g.passing_score
    SQL
            );
            $completedStmt->execute([':uid' => $_SESSION['user_id']]);
            $completedRow = $completedStmt->fetch(PDO::FETCH_ASSOC);
            $completedCount = $completedRow ? (int)$completedRow['cnt'] : 0;

            $cstmt = $db->prepare("SELECT COUNT(*) as cnt FROM certificates WHERE user_id = :uid");
            $cstmt->execute([':uid' => $_SESSION['user_id']]);
            $crow = $cstmt->fetch(PDO::FETCH_ASSOC);
            $certCount = $crow ? (int)$crow['cnt'] : 0;

            $ustmt = $db->prepare("SELECT xp FROM users WHERE id = :uid LIMIT 1");
            $ustmt->execute([':uid' => $_SESSION['user_id']]);
            $urow = $ustmt->fetch(PDO::FETCH_ASSOC);
            $userXp = $urow ? (int)$urow['xp'] : 0;

            $avgStmt = $db->prepare(<<<'SQL'
    SELECT IFNULL(ROUND(AVG(best), 1), 0) as avg_best_score FROM (
      SELECT MAX(s.score_percentage) as best
      FROM scores s
      WHERE s.user_id = :uid
      GROUP BY s.game_id
    ) a
    SQL
            );
            $avgStmt->execute([':uid' => $_SESSION['user_id']]);
            $avgRow = $avgStmt->fetch(PDO::FETCH_ASSOC);
            $avgScore = $avgRow ? (float)$avgRow['avg_best_score'] : 0;

            $statsStmt = $db->prepare("SELECT
                (SELECT COUNT(*) FROM games) AS total_lessons,
                (SELECT COUNT(*) FROM works WHERE user_id = :uid) AS works_count
                ");
            $statsStmt->execute([':uid' => $_SESSION['user_id']]);
            $statsData = $statsStmt->fetch(PDO::FETCH_ASSOC);
            if ($statsData) {
                $stats = array_merge($stats, $statsData);
            }

            $scoresStmt = $db->prepare("SELECT COUNT(*) as scores_count, COUNT(DISTINCT DATE(created_at)) as active_days FROM scores WHERE user_id = :uid");
            $scoresStmt->execute([':uid' => $_SESSION['user_id']]);
            $scoresData = $scoresStmt->fetch(PDO::FETCH_ASSOC);
            if ($scoresData) {
                $stats = array_merge($stats, $scoresData);
            }
            error_log('Profile stats DEBUG: user_id=' . $_SESSION['user_id'] . ', scoresData=' . json_encode($scoresData) . ', stats after merge=' . json_encode($stats));
        }
    } catch (Exception $e) {
        error_log('Profile load error: ' . $e->getMessage());
    }
}

$displayName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: ($user['username'] ?? 'Khách');
$profileRole = ($user['role'] ?? 'Học sinh') === 'admin' ? 'Quản trị viên' : 'Học sinh';
$avatarHtml = '<div class="avatar-large">👦</div>';
if (!empty($user['avatar'])) {
    $avatarPath = rtrim($base_url, '/') . '/public/uploads/avatars/' . rawurlencode($user['avatar']);
    $avatarHtml = '<img src="' . $avatarPath . '" alt="avatar" class="avatar-img" />';
}

echo '<!-- DEBUG: user_id=' . $_SESSION['user_id'] . ', user=' . json_encode($user) . ', stats=' . json_encode($stats) . ' -->';

$lessonsCount = isset($stats['total_lessons']) ? (int)$stats['total_lessons'] : 0;
$achievementsCount = isset($stats['works_count']) ? (int)$stats['works_count'] : 0;
$daysLearning = isset($stats['active_days']) ? (int)$stats['active_days'] : 0;
$completedLessons = isset($completedCount) ? $completedCount : 0;
$certificatesCount = isset($certCount) ? $certCount : 0;
error_log('Final values: daysLearning=' . $daysLearning . ', active_days=' . $stats['active_days']);

function getLevelFromXp($xp) {
    if ($xp <= 100) {
        return 'Sao Nhí Lấp Lánh';
    } elseif ($xp <= 200) {
        return 'Phi Hành Gia Tập Sự';
    } elseif ($xp <= 300) {
        return 'Thuyền Trưởng Không Gian';
    } else {
        return 'Người Chinh Phục Vũ Trụ';
    }
}

$levelBadge = getLevelFromXp($userXp);

$totalLessonsCount = 20;
$progressPercent = $totalLessonsCount ? round(($completedLessons / $totalLessonsCount) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - STEM Universe</title>

    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/profile.css?v=<?php echo time(); ?>">
    
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

    <main class="profile-container">
        <div class="container">
            <div class="profile-content">

                <div class="profile-header">
                    <div class="profile-avatar-section">
                        <div class="profile-avatar">
                            <div id="currentAvatar"><?php echo $avatarHtml; ?></div>
                            <button class="edit-avatar-btn" id="editAvatarBtn">
                                <i class="fas fa-camera"></i>
                            </button>
                        </div>
                        <div class="profile-info">
                            <h1 class="profile-name" id="displayName"><?php echo htmlspecialchars($displayName); ?></h1>
                            <p class="profile-role"><?php echo htmlspecialchars($profileRole); ?></p>
                            <div class="profile-stats">
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $completedLessons; ?></span>
                                    <span class="stat-label">Bài học</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $certificatesCount; ?></span>
                                    <span class="stat-label">Thành tích</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number"><?php echo $daysLearning; ?></span>
                                    <span class="stat-label">Ngày học</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="edit-profile-btn" id="editProfileBtn">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa hồ sơ
                    </button>
                </div>

                <div class="profile-tabs">
                    <button class="tab-btn active" data-tab="info">Thông tin cá nhân</button>
                    <button class="tab-btn" data-tab="progress">Tiến độ học tập</button>
                </div>

                <div class="tab-content">

                    <div class="tab-pane active" id="info-tab">
                        <div class="info-card-grid">

                            <div class="info-section">
                                <h3 class="section-header"><i class="fas fa-user"></i> Thông tin cơ bản</h3>
                                <div class="basic-info-grid">
                                    <div class="info-row">
                                        <span class="info-label">Họ và tên:</span>
                                        <span class="info-value" id="infoFullName"><?php echo htmlspecialchars($displayName); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Ngày sinh:</span>
                                        <span class="info-value" id="infoBirthDate">15/03/2015</span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Lớp:</span>
                                        <span class="info-value" id="infoClass"><?php echo htmlspecialchars($user['class'] ?? ''); ?></span>
                                    </div>
                                    <div class="info-row">
                                        <span class="info-label">Trường:</span>
                                        <span class="info-value" id="infoSchool"><?php echo htmlspecialchars($user['email'] ?? ''); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="info-section">
                                <h3 class="section-header"><i class="fas fa-graduation-cap"></i> Học tập</h3>
                                <div class="study-info-grid">
                                    <div class="study-item">
                                        <span class="info-label">Điểm trung bình</span>
                                        <span class="study-value"><?php echo $avgScore > 0 ? round($avgScore, 1) : '0'; ?>%</span>
                                    </div>
                                    <div class="study-item">
                                        <span class="info-label">Bài học hoàn thành</span>
                                        <span class="study-value"><?php echo $completedLessons . '/' . "20"; ?></span>
                                    </div>
                                    <div class="study-item">
                                        <span class="info-label">Cấp độ</span>
                                        <span class="level-badge"><?php echo htmlspecialchars($levelBadge); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="progress-tab">
                        <div class="progress-stats">
                            <div class="progress-card">
                                <h4>Tiến độ học tập</h4>
                                <div class="circular-progress-wrapper">
                                    <div class="circular-progress" data-progress="<?php echo $progressPercent; ?>">
                                        <div class="inner-circle">
                                            <span class="progress-value"><?php echo $progressPercent; ?>%</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="progress-text">Đã hoàn thành <?php echo $completedLessons; ?>/<?php echo $totalLessonsCount; ?> bài học</p>
                            </div>
                            
                            <div class="chart-container">
                                <h4 class="chart-title">Thời gian học tập trong tuần</h4>
                                <p class="chart-subtitle">(Tính theo phút)</p>
                                <div class="chart-bars">
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 80%"></div>
                                        <span>T2</span>
                                    </div>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 60%"></div>
                                        <span>T3</span>
                                    </div>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 90%"></div>
                                        <span>T4</span>
                                    </div>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 70%"></div>
                                        <span>T5</span>
                                    </div>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 85%"></div>
                                        <span>T6</span>
                                    </div>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 50%"></div>
                                        <span>T7</span>
                                    </div>
                                    <div class="chart-bar">
                                        <div class="bar-fill" style="height: 40%"></div>
                                        <span>CN</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chỉnh sửa hồ sơ</h3>
                <button class="close-modal" id="closeProfileModal">&times;</button>
            </div>
            <form id="profileForm">
                <div class="form-group">
                    <label class="form-label" for="fullName">Họ và tên</label>
                    <input type="text" id="fullName" class="form-input" value="<?php echo htmlspecialchars($displayName); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="birthDate">Ngày sinh</label>
                    <input type="date" id="birthDate" class="form-input" value="">
                </div>
                <div class="form-group">
                    <label class="form-label" for="class">Lớp</label>
                    <input type="text" id="class" class="form-input" value="<?php echo htmlspecialchars($user['class'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="school">Trường</label>
                    <input type="text" id="school" class="form-input" value="">
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelProfileEdit">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="editAvatarModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Thay đổi ảnh đại diện</h3>
                <button class="close-modal" id="closeAvatarModal">&times;</button>
            </div>
            <div class="avatar-upload">
                <div class="avatar-preview" id="avatarPreview">
                    <?php echo $avatarHtml; ?>
                </div>
                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
                <button class="upload-btn" id="uploadAvatarBtn">
                    <i class="fas fa-upload"></i>
                    Chọn ảnh từ thiết bị
                </button>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" id="cancelAvatarEdit">Hủy</button>
                <button type="button" class="btn btn-danger" id="deleteAvatarBtn">Xóa ảnh</button>
                <button type="button" class="btn btn-primary" id="saveAvatarBtn">Lưu ảnh</button>
            </div>
        </div>
    </div>

    <script src="<?php echo $base_url; ?>/public/JS/profile.js?v=<?php echo time(); ?>"></script>
</body>
</html>