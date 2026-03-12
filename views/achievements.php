<?php
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/SPNC_HocLieu_STEM_TieuHoc";
require_once './template/header.php';
$completedCount  = 0;
$certCount       = 0;
$userXp          = 0;
$progressPercent = 0;
$totalLessons    = 20;
$avgScore        = 0;
$topicStatusData = [];
$studentFullName = '';
$isLoggedIn      = false;

if (session_status() == PHP_SESSION_NONE) session_start();
if (!empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
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

        $cstmt = $db->prepare("SELECT COUNT(*) as cnt FROM certificates WHERE user_id = :uid");
        $cstmt->execute([':uid' => $_SESSION['user_id']]);
        $crow = $cstmt->fetch(PDO::FETCH_ASSOC);
        $certCount = $crow ? (int)$crow['cnt'] : 0;

        $ustmt = $db->prepare("SELECT xp, first_name, last_name FROM users WHERE id = :uid LIMIT 1");
        $ustmt->execute([':uid' => $_SESSION['user_id']]);
        $urow = $ustmt->fetch(PDO::FETCH_ASSOC);
        $userXp = $urow ? (int)$urow['xp'] : 0;
        if ($urow) {
            $studentFullName = trim(($urow['first_name'] ?? '') . ' ' . ($urow['last_name'] ?? ''));
        }
        if (empty($studentFullName) && !empty($_SESSION['full_name'])) {
            $studentFullName = $_SESSION['full_name'];
        }

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

        $done = $completedCount;
        $total = $totalLessons;
        $progressPercent = $total ? round(($done / $total) * 100) : 0;

        $topicStmt = $db->prepare("
            SELECT
                sf.id          AS topic_id,
                sf.name        AS topic_name,
                COUNT(DISTINCT g.id) AS total_games,
                COALESCE(SUM(CASE
                    WHEN us.best_score IS NOT NULL
                     AND (g.passing_score IS NULL OR us.best_score >= g.passing_score)
                    THEN 1 ELSE 0 END), 0) AS completed_games,
                MAX(CASE WHEN cert.user_id IS NOT NULL THEN 1 ELSE 0 END) AS has_certificate,
                MAX(cert.issued_at) AS certificate_issued_at
            FROM stem_fields sf
            LEFT JOIN games g ON g.topic_id = sf.id
            LEFT JOIN (
                SELECT game_id, MAX(score_percentage) AS best_score
                FROM scores
                WHERE user_id = :uid
                GROUP BY game_id
            ) us ON us.game_id = g.id
            LEFT JOIN certificates cert
                ON cert.topic_id = sf.id AND cert.user_id = :uid2
            GROUP BY sf.id, sf.name
            ORDER BY sf.id ASC
        ");
        $topicStmt->execute([':uid' => $_SESSION['user_id'], ':uid2' => $_SESSION['user_id']]);
        $topicRows = $topicStmt->fetchAll(PDO::FETCH_ASSOC);

        $threshold = 90;
        foreach ($topicRows as $r) {
            $ttotal = (int)$r['total_games'];
            $tdone  = (int)$r['completed_games'];
            $tpct   = $ttotal > 0 ? (int)round(($tdone / $ttotal) * 100) : 0;
            $telig  = $tpct >= $threshold;
            $needed = $telig ? 0 : max(0, (int)ceil($ttotal * $threshold / 100) - $tdone);
            $topicStatusData[] = [
                'topic_id'              => (int)$r['topic_id'],
                'topic_name'            => $r['topic_name'],
                'total_games'           => $ttotal,
                'completed_games'       => $tdone,
                'completion_percent'    => $tpct,
                'eligible'              => $telig,
                'has_certificate'       => (bool)(int)$r['has_certificate'],
                'certificate_issued_at' => $r['certificate_issued_at'],
                'games_needed'          => $needed,
            ];
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&family=Baloo+2:wght@400;500;600;700&family=Montserrat:wght@400;500;600;700;800;900&family=Dancing+Script:wght@700&display=swap" rel="stylesheet">
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
                    <div class="stat-number"><?php echo htmlspecialchars(round($avgScore, 1)); ?>%</div>
                    <div class="stat-label">Điểm trung bình</div>
                </div>
            </div>
        </section>

        <section class="topics-section">
            <div class="section-header">
                <h2>Lĩnh Vực STEM</h2>
                <p>Hoàn thành <strong>90%</strong> bài học trong mỗi lĩnh vực để tạo và tải chứng nhận</p>
            </div>
            <div class="topics-carousel">
                <button class="topic-nav topic-nav-prev" id="topicPrev" onclick="shiftTopic(-1)" aria-label="Trước">&#8249;</button>
                <div class="topics-viewport">
                    <div class="topics-track" id="topicsGrid">
                        <div class="topic-loading">
                            <div class="loading-spinner"></div>
                            <span>Đang tải dữ liệu...</span>
                        </div>
                    </div>
                </div>
                <button class="topic-nav topic-nav-next" id="topicNext" onclick="shiftTopic(1)" aria-label="Tiếp">&#8250;</button>
            </div>
            <div class="topics-dots" id="topicsDots"></div>
        </section>
    </main>

    <div class="cert-modal-overlay" id="certModal" style="display:none" role="dialog" aria-modal="true" aria-labelledby="certModalTitle">
        <div class="cert-modal-box">
            <div class="cert-modal-header">
                <div>
                    <h3 id="certModalTitle">Chứng nhận</h3>
                    <p class="cert-modal-desc">Xem trước và tải xuống chứng nhận của bạn</p>
                </div>
                <button class="cert-modal-close" onclick="closeCertModal()" aria-label="Đóng">✕</button>
            </div>

            <div class="cert-modal-body">
                <div class="cert-loading" id="certLoading">
                    <div class="loading-spinner"></div>
                    <span>Đang tạo chứng nhận...</span>
                </div>
                <canvas id="certCanvas" style="display:none"></canvas>
            </div>

            <div class="cert-modal-footer">
                <div class="cert-modal-actions">
                    <button class="btn-download-cert" onclick="downloadCanvas()">
                        <i class="fas fa-download"></i> Tải xuống PNG
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php require_once './template/footer.php'; ?>

    <script>
        var achievementsData = {
            loggedIn:    <?php echo $isLoggedIn ? 'true' : 'false'; ?>,
            studentName: <?php echo json_encode($studentFullName, JSON_UNESCAPED_UNICODE); ?>,
            topics:      <?php echo json_encode($topicStatusData, JSON_UNESCAPED_UNICODE); ?>
        };
        var ISSUE_CERT_URL = <?php echo json_encode($base_url . '/api/issue-certificate'); ?>;
    </script>
    <script src="<?php echo $base_url; ?>/public/JS/achievements.js?v=<?php echo time(); ?>"></script>

</body>
</html>