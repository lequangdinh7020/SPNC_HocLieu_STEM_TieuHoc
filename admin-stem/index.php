<?php
session_start();
require_once __DIR__ . '/includes/config.php';

checkAdminLogin();

// Lấy dữ liệu từ database
$conn = getDBConnection();

// 1. Tổng số học liệu
$stmt_lessons = mysqli_query($conn, "SELECT COUNT(*) as total FROM games");
$row_lessons = mysqli_fetch_assoc($stmt_lessons);
$total_lessons = $row_lessons['total'] ?? 0;

// 2. Tổng số người dùng
$stmt_users = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$row_users = mysqli_fetch_assoc($stmt_users);
$total_users = $row_users['total'] ?? 0;

// 3. Tổng lượt xem (số bản ghi trong bảng scores)
$stmt_views = mysqli_query($conn, "SELECT COUNT(*) as total FROM scores");
$row_views = mysqli_fetch_assoc($stmt_views);
$total_views = $row_views['total'] ?? 0;

// 4. Hoạt động gần đây (users vừa tạo account)
$activities = [];
$stmt_activity = mysqli_query($conn, "SELECT id, first_name, last_name, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
while ($act = mysqli_fetch_assoc($stmt_activity)) {
    $activities[] = $act;
}

// 5. Học liệu phổ biến (game với nhiều lượt chơi nhất)
$popular_materials = [];
$stmt_popular = mysqli_query($conn, "
    SELECT 
        g.id,
        g.game_name,
        IFNULL(sf.name, 'Không xác định') as topic_name,
        COUNT(s.id) as play_count,
        IFNULL(ROUND(AVG(s.score_percentage), 1), 0) as avg_score
    FROM games g
    LEFT JOIN scores s ON g.id = s.game_id
    LEFT JOIN stem_fields sf ON g.topic_id = sf.id
    GROUP BY g.id, g.game_name, sf.name
    ORDER BY play_count DESC
    LIMIT 5
");
while ($material = mysqli_fetch_assoc($stmt_popular)) {
    $popular_materials[] = $material;
}

// 6. Thống kê theo từng field (Toán, Khoa học, Công nghệ, Kỹ thuật)
$field_stats = [];
$stmt_fields = mysqli_query($conn, "
    SELECT 
        sf.id,
        sf.name,
        sf.icon,
        sf.color,
        COUNT(DISTINCT g.id) as lesson_count,
        COUNT(DISTINCT CASE WHEN s.id IS NOT NULL THEN s.user_id END) as unique_players,
        COUNT(DISTINCT s.id) as total_plays,
        IFNULL(ROUND(AVG(max_scores.max_score), 1), 0) as avg_best_score
    FROM stem_fields sf
    LEFT JOIN games g ON sf.id = g.topic_id
    LEFT JOIN (
        SELECT game_id, MAX(score_percentage) as max_score 
        FROM scores 
        GROUP BY game_id
    ) max_scores ON g.id = max_scores.game_id
    LEFT JOIN scores s ON g.id = s.game_id
    GROUP BY sf.id, sf.name, sf.icon, sf.color
    ORDER BY total_plays DESC
");
while ($field = mysqli_fetch_assoc($stmt_fields)) {
    $field_stats[] = $field;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Học liệu STEM Tiểu học</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-atom"></i>
                <h2>STEM Admin</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tổng quan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="statistics.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Thống kê</span>
                        </a>
                    </li>
                    <li class="nav-item" style="display: none;">
                        <a href="learning_materials.php">
                            <i class="fas fa-book"></i>
                            <span>Học liệu</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Người dùng</span>
                        </a>
                    </li>
                    <li class="nav-item" style="display: none;">
                        <a href="settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Cài đặt</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="admin-info">
                    <div class="admin-avatar">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="admin-details">
                        <h4><?php echo $_SESSION['admin_username'] ?? 'Admin'; ?></h4>
                        <p>Quản trị viên</p>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </aside>

        <main class="main-content">
            <div class="header">
                <div class="header-title">
                    <h1>Tổng quan hệ thống</h1>
                    <p>Quản lý học liệu STEM cho học sinh Tiểu học</p>
                </div>
                <div class="header-actions">
                    <div class="date-info">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="current-date"></span>
                    </div>
                    <button class="btn-notification">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge"><?php echo count($activities); ?></span>
                    </button>
                </div>
            </div>

            <div class="content-wrapper">
                <div class="welcome-card">
                    <div class="welcome-text">
                        <h2>Chào mừng!</h2>
                        <p>Giám sát và quản lý hệ thống học liệu STEM một cách hiệu quả</p>
                    </div>
                    <div class="welcome-stats">
                        <div class="stat-item">
                            <i class="fas fa-book"></i>
                            <h3><?php echo $total_lessons; ?></h3>
                            <p>Học liệu</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-users"></i>
                            <h3><?php echo $total_users; ?></h3>
                            <p>Người dùng</p>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-eye"></i>
                            <h3><?php echo number_format($total_views); ?></h3>
                            <p>Lượt xem</p>
                        </div>
                    </div>
                </div>

                <div class="stats-overview">
                    <?php 
                    $icons = ['flask', 'laptop-code', 'cogs', 'calculator'];
                    $colors = ['science', 'technology', 'engineering', 'math'];
                    $index = 0;
                    
                    foreach ($field_stats as $field): 
                    ?>
                    <div class="stat-card">
                        <div class="stat-icon <?php echo $colors[$index % 4]; ?>">
                            <i class="fas fa-<?php echo $icons[$index % 4]; ?>"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo htmlspecialchars($field['name']); ?></h3>
                            <div class="stat-details">
                                <span class="stat-value"><?php echo $field['total_plays']; ?></span>
                                <span class="stat-change <?php echo $field['avg_best_score'] > 70 ? 'positive' : ($field['avg_best_score'] > 50 ? 'neutral' : 'warning'); ?>">
                                    <?php echo $field['avg_best_score']; ?>%
                                </span>
                            </div>
                            <p class="stat-sub"><?php echo $field['lesson_count']; ?> học liệu</p>
                        </div>
                    </div>
                    <?php 
                    $index++;
                    endforeach; 
                    ?>
                </div>

                <div class="content-row">
                    <div class="content-col">
                        <div class="content-box">
                            <div class="box-header">
                                <h3>
                                    <i class="fas fa-tasks"></i>
                                    Hoạt động gần đây
                                </h3>
                            </div>
                            <div class="activity-list">
                                <?php 
                                if (empty($activities)): 
                                ?>
                                    <div class="activity-item">
                                        <p style="padding: 20px; text-align: center; color: #999;">Chưa có hoạt động nào</p>
                                    </div>
                                <?php 
                                else:
                                    foreach ($activities as $activity): 
                                        $role_text = $activity['role'] === 'admin' ? 'quản trị viên' : 'học sinh/giáo viên';
                                        $full_name = trim($activity['first_name'] . ' ' . $activity['last_name']);
                                        $time_ago = time_elapsed_string($activity['created_at']);
                                ?>
                                <div class="activity-item">
                                    <div class="activity-icon success">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="activity-content">
                                        <p>Người dùng mới đăng ký: <?php echo htmlspecialchars($role_text . ' ' . $full_name); ?></p>
                                        <span class="activity-time"><?php echo $time_ago; ?></span>
                                    </div>
                                </div>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="content-section">
                        <div class="section-header">
                            <h3>
                                <i class="fas fa-fire"></i>
                                Học liệu phổ biến
                            </h3>
                        </div>
                        <div class="popular-materials">
                            <?php 
                            if (empty($popular_materials)): 
                            ?>
                                <div style="padding: 20px; text-align: center; color: #999;">Chưa có dữ liệu</div>
                            <?php 
                            else:
                                $rank = 1;
                                foreach ($popular_materials as $material): 
                            ?>
                            <div class="material-item">
                                <div class="material-rank"><?php echo $rank; ?></div>
                                <div class="material-info">
                                    <h4><?php echo htmlspecialchars($material['game_name']); ?></h4>
                                    <div class="material-meta">
                                        <span class="material-category"><?php echo htmlspecialchars($material['topic_name']); ?></span>
                                        <span class="material-views"><i class="fas fa-play"></i> <?php echo $material['play_count']; ?> lượt</span>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                $rank++;
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/index.js"></script>
    <script>
        // Hiển thị ngày hiện tại
        const dateElement = document.getElementById('current-date');
        const today = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateElement.textContent = today.toLocaleDateString('vi-VN', options);
    </script>
</body>
</html>

<?php
// Helper function để tính thời gian đã trôi qua
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'năm',
        'm' => 'tháng',
        'w' => 'tuần',
        'd' => 'ngày',
        'h' => 'giờ',
        'i' => 'phút',
        's' => 'giây',
    );
    foreach($string as $k => &$v) {
        if($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? '' : '');
        } else {
            unset($string[$k]);
        }
    }

    if(!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' trước' : 'vừa xong';
}
?>
