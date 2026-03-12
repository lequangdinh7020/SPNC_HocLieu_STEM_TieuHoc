<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../models/Database.php';

$userId = null;
if (!empty($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
}

$result = ['success' => true, 'topics' => [], 'logged_in' => ($userId !== null)];

if (!$userId) {
    echo json_encode($result);
    exit;
}

try {
    $database = new Database();
    $conn     = $database->getConnection();

    $stmt = $conn->prepare("
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
    $stmt->execute([':uid' => $userId, ':uid2' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $threshold = 90;
    foreach ($rows as $r) {
        $total    = (int)$r['total_games'];
        $done     = (int)$r['completed_games'];
        $pct      = $total > 0 ? (int)round(($done / $total) * 100) : 0;
        $eligible = $pct >= $threshold;
        $needed   = $eligible ? 0 : max(0, (int)ceil($total * $threshold / 100) - $done);

        $result['topics'][] = [
            'topic_id'              => (int)$r['topic_id'],
            'topic_name'            => $r['topic_name'],
            'total_games'           => $total,
            'completed_games'       => $done,
            'completion_percent'    => $pct,
            'eligible'              => $eligible,
            'has_certificate'       => (bool)(int)$r['has_certificate'],
            'certificate_issued_at' => $r['certificate_issued_at'],
            'games_needed'          => $needed,
        ];
    }
} catch (Exception $e) {
    error_log('get_topic_status error: ' . $e->getMessage());
    $result['success'] = false;
    $result['error']   = 'Lỗi hệ thống';
}

echo json_encode($result);
?>
