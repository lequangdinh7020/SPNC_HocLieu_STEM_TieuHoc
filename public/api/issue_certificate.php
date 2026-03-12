<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để nhận chứng chỉ']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input    = json_decode($rawInput, true);
$topicId  = isset($input['topic_id']) ? (int)$input['topic_id'] : 0;

if ($topicId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Chủ đề không hợp lệ']);
    exit;
}

require_once __DIR__ . '/../../models/Database.php';

try {
    $database = new Database();
    $conn     = $database->getConnection();
    $userId   = (int)$_SESSION['user_id'];

    $topicStmt = $conn->prepare("SELECT id FROM stem_fields WHERE id = :tid LIMIT 1");
    $topicStmt->execute([':tid' => $topicId]);
    if (!$topicStmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Chủ đề không tồn tại']);
        exit;
    }

    $stmt = $conn->prepare("
        SELECT
            COUNT(DISTINCT g.id) AS total_games,
            COALESCE(SUM(CASE
                WHEN us.best_score IS NOT NULL
                 AND (g.passing_score IS NULL OR us.best_score >= g.passing_score)
                THEN 1 ELSE 0 END), 0) AS completed_games
        FROM games g
        LEFT JOIN (
            SELECT game_id, MAX(score_percentage) AS best_score
            FROM scores
            WHERE user_id = :uid
            GROUP BY game_id
        ) us ON us.game_id = g.id
        WHERE g.topic_id = :tid
    ");
    $stmt->execute([':uid' => $userId, ':tid' => $topicId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || (int)$row['total_games'] === 0) {
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài học trong chủ đề này']);
        exit;
    }

    $total = (int)$row['total_games'];
    $done  = (int)$row['completed_games'];
    $pct   = (int)round(($done / $total) * 100);

    if ($pct < 90) {
        $needed = max(0, (int)ceil($total * 0.9) - $done);
        echo json_encode([
            'success' => false,
            'message' => "Bạn cần hoàn thành thêm {$needed} bài học nữa để nhận chứng chỉ (hiện tại đạt {$pct}%)"
        ]);
        exit;
    }

    $ins = $conn->prepare(
        "INSERT IGNORE INTO certificates (user_id, topic_id, issued_at) VALUES (:uid, :tid, NOW())"
    );
    $ins->execute([':uid' => $userId, ':tid' => $topicId]);
    $alreadyExisted = $ins->rowCount() === 0;

    echo json_encode([
        'success'         => true,
        'already_existed' => $alreadyExisted,
        'message'         => $alreadyExisted
            ? 'Chứng nhận đã được cấp trước đó'
            : 'Chứng nhận đã được cấp thành công!',
    ]);
} catch (Exception $e) {
    error_log('issue_certificate error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
}
?>
