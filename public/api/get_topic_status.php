<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../models/Database.php';

$db = new Database();
$conn = $db->getConnection();

$userId = null;
if (!empty($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
} elseif (!empty($_GET['user_id'])) {
    $userId = (int)$_GET['user_id'];
}

$result = ['success' => true, 'completed_games' => []];

if (!$userId) {
    echo json_encode($result);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT DISTINCT g.id, g.game_name FROM scores s JOIN games g ON s.game_id = g.id WHERE s.user_id = :uid");
    $stmt->execute([':uid' => $userId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $completed = [];

    // helper: create a simple slug from game name
    $slugify = function($text) {
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = preg_replace('/[^A-Za-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        return $text;
    };

    foreach ($rows as $r) {
        if (empty($r['game_name'])) continue;
        $name = trim($r['game_name']);
        $completed[] = [
            'id' => (int)$r['id'],
            'name' => $name,
            'slug' => $slugify($name)
        ];
    }
    $result['completed_games'] = $completed;
} catch (Exception $e) {
    $result['success'] = false;
    $result['error'] = $e->getMessage();
}

echo json_encode($result);

?>
