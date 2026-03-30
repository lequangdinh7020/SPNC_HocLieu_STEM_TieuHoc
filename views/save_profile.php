<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../models/Database.php';
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$updates = [];
$params = [];

$oldAvatar = null;
try {
    $qa = $db->prepare('SELECT avatar FROM users WHERE id = :id LIMIT 1');
    $qa->execute([':id' => $userId]);
    $ar = $qa->fetch(PDO::FETCH_ASSOC);
    if ($ar && !empty($ar['avatar'])) $oldAvatar = $ar['avatar'];
} catch (Exception $e) {
}

if (isset($_POST['fullName'])) {
    $fullName = trim($_POST['fullName']);
    if ($fullName !== '') {
        $parts = preg_split('/\s+/', $fullName);
        $last = array_pop($parts);
        $first = implode(' ', $parts);
        if ($first === '') $first = $last;
        $updates[] = 'first_name = :first_name';
        $updates[] = 'last_name = :last_name';
        $params[':first_name'] = $first;
        $params[':last_name'] = $last;
    }
}

if (isset($_POST['class'])) {
    $updates[] = 'class = :class';
    $params[':class'] = trim($_POST['class']);
}


$uploadedFilename = null;
if (isset($_FILES['avatar']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
    $file = $_FILES['avatar'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime, $allowed)) {
            echo json_encode(['success' => false, 'message' => 'Loại file không hợp lệ']);
            exit;
        }

        if ($file['size'] > 2 * 1024 * 1024) { 
            echo json_encode(['success' => false, 'message' => 'Kích thước file quá lớn (max 2MB)']);
            exit;
        }

        $ext = $allowed[$mime];
        $uploadsDir = __DIR__ . '/../public/uploads/avatars';
        if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0755, true);

        $basename = time() . '-' . bin2hex(random_bytes(6));
        $uploadedFilename = $basename . $ext;
        $destination = $uploadsDir . '/' . $uploadedFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
            exit;
        }
        if ($oldAvatar) {
            $oldPath = __DIR__ . '/../public/uploads/avatars/' . $oldAvatar;
            if (is_file($oldPath)) @unlink($oldPath);
        }

        $updates[] = 'avatar = :avatar';
        $params[':avatar'] = $uploadedFilename;
    }
}

if (isset($_POST['delete_avatar']) && $_POST['delete_avatar']) {
    if ($oldAvatar) {
        $oldPath = __DIR__ . '/../public/uploads/avatars/' . $oldAvatar;
        if (is_file($oldPath)) @unlink($oldPath);
    }
    $updates[] = 'avatar = NULL';
}

if (!empty($updates)) {
    $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = :id';
    $params[':id'] = $userId;
    try {
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
        exit;
    }
}

$avatarUrl = null;
if ($uploadedFilename) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $projectPath = '/SPNC_HocLieu_STEM_TieuHoc';
    $avatarUrl = $protocol . '://' . $host . $projectPath . '/public/uploads/avatars/' . rawurlencode($uploadedFilename);
}

echo json_encode(['success' => true, 'message' => 'Cập nhật thành công', 'avatar_url' => $avatarUrl]);
exit;

?>
