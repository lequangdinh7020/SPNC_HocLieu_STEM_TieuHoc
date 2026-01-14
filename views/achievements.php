<?php
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";
require_once './template/header.php';
// Compute live stats for logged-in user
$completedCount = 0;
$certCount = 0;
$userXp = 0;
$progressPercent = 0;
$totalLessons = 20; // same base used on home.php
if (session_status() == PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../models/Database.php';
        $database = new Database();
        $db = $database->getConnection();

        // Completed games (distinct game_id, best score >= passing_score)
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

        // Certificates count
        $cstmt = $db->prepare("SELECT COUNT(*) as cnt FROM certificates WHERE user_id = :uid");
        $cstmt->execute([':uid' => $_SESSION['user_id']]);
        $crow = $cstmt->fetch(PDO::FETCH_ASSOC);
        $certCount = $crow ? (int)$crow['cnt'] : 0;

        // User XP
        $ustmt = $db->prepare("SELECT xp FROM users WHERE id = :uid LIMIT 1");
        $ustmt->execute([':uid' => $_SESSION['user_id']]);
        $urow = $ustmt->fetch(PDO::FETCH_ASSOC);
        $userXp = $urow ? (int)$urow['xp'] : 0;

        // Progress percent (reuse home.php logic)
        $done = $completedCount;
        $total = $totalLessons;
        $progressPercent = $total ? round(($done / $total) * 100) : 0;
    } catch (Exception $e) {
        error_log('Achievements load error: ' . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thành Tích - STEM Universe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Baloo+2:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/home.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/CSS/achievements.css?v=<?php echo time(); ?>   ">
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
        <section class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Thành Tích <span class="highlight">Của Bạn</span></h1>
                    <p>Nơi ghi nhận những nỗ lực và thành công trong hành trình khám phá STEM</p>
                </div>
                <div class="hero-visual">
                    <div class="floating-elements">
                        <div class="floating-element element-1">🏆</div>
                        <div class="floating-element element-2">🎓</div>
                        <div class="floating-element element-3">⭐</div>
                        <div class="floating-element element-4">📜</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📚</div>
                    <div class="stat-number"><?php echo htmlspecialchars($completedCount); ?></div>
                    <div class="stat-label">Bài học đã hoàn thành</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">🏆</div>
                    <div class="stat-number"><?php echo htmlspecialchars($certCount); ?></div>
                    <div class="stat-label">Chứng nhận nhận được</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⭐</div>
                    <div class="stat-number"><?php echo htmlspecialchars($userXp); ?></div>
                    <div class="stat-label">Điểm thành tích</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📈</div>
                    <div class="stat-number"><?php echo htmlspecialchars($progressPercent); ?>%</div>
                    <div class="stat-label">Tiến độ học tập</div>
                </div>
            </div>
        </section>

        <section class="certificates-section">
            <div class="section-header">
                <h2>Bộ Sưu Tập Chứng Nhận</h2>
                <p>Những bằng khen và chứng nhận bạn đã đạt được</p>
            </div>
            
            <div class="certificates-display">
                <button class="certificate-nav prev" onclick="changeCertificate(-1)">
                    <span class="nav-arrow">‹</span>
                </button>
                
                <div class="certificate-viewport">
                    <div class="certificate-wrapper">
                        <div class="certificate-paper" id="currentCertificate">
                        </div>
                    </div>
                </div>
                
                <button class="certificate-nav next" onclick="changeCertificate(1)">
                    <span class="nav-arrow">›</span>
                </button>
            </div>
            
            <div class="certificate-actions">
                <button class="action-btn download-btn" onclick="downloadCertificate()">
                    <i class="fas fa-download"></i>
                    Tải xuống
                </button>
                <button class="action-btn share-btn" onclick="shareCertificate()">
                    <i class="fas fa-share"></i>
                    Chia sẻ
                </button>
            </div>
        </section>
    </main>

    <?php require_once './template/footer.php'; ?>

    <script src="<?php echo $base_url; ?>/public/JS/achievements.js?v=<?php echo time(); ?>"></script>

</body>
</html>