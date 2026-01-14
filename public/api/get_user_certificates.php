<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../models/Database.php';

$db = new Database();
$conn = $db->getConnection();

$result = ['success' => false, 'student' => null, 'certificates' => []];

if (empty($_SESSION['user_id'])) {
    echo json_encode($result);
    exit;
}

$uid = (int)$_SESSION['user_id'];
try {
    // Student name
    $ust = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = :id LIMIT 1");
    $ust->execute([':id' => $uid]);
    $urow = $ust->fetch(PDO::FETCH_ASSOC);
    $student = $urow ? trim(($urow['first_name'] ?? '') . ' ' . ($urow['last_name'] ?? '')) : null;

    // Certificates with topic info
    $cstmt = $conn->prepare("SELECT c.topic_id, sf.name as topic_name, c.issued_at FROM certificates c JOIN stem_fields sf ON c.topic_id = sf.id WHERE c.user_id = :uid");
    $cstmt->execute([':uid' => $uid]);
    $rows = $cstmt->fetchAll(PDO::FETCH_ASSOC);
    $certs = [];
    foreach ($rows as $r) {
        $certs[] = [
            'topic_id' => (int)$r['topic_id'],
            'topic_name' => $r['topic_name'],
            'issued_at' => $r['issued_at'] ?? null
        ];
    }

    $result['success'] = true;
    $result['student'] = $student;
    $result['certificates'] = $certs;
} catch (Exception $e) {
    $result['success'] = false;
    $result['error'] = $e->getMessage();
}

echo json_encode($result);

?>
