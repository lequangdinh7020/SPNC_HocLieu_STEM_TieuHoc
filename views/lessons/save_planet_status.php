<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

// Cho phép debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Khởi tạo session nếu chưa có
if (!isset($_SESSION['planet_status'])) {
    $_SESSION['planet_status'] = [];
}

// Nhận dữ liệu từ POST
$input = json_decode(file_get_contents('php://input'), true);
$planet_id = isset($input['planet_id']) ? intval($input['planet_id']) : null;
$status = isset($input['status']) ? $input['status'] : null;

// Debug log
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Planet ID: $planet_id, Status: $status\n", FILE_APPEND);

if ($planet_id && $status && in_array($status, ['not-started', 'current', 'completed'])) {
    // Lưu trạng thái vào session
    $_SESSION['planet_status'][$planet_id] = $status;
    
    echo json_encode([
        'success' => true,
        'message' => 'Planet status saved',
        'planet_id' => $planet_id,
        'status' => $status,
        'session_data' => $_SESSION['planet_status'] // Trả về để debug
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data',
        'input' => $input,
        'planet_id' => $planet_id,
        'status' => $status
    ]);
}
?>