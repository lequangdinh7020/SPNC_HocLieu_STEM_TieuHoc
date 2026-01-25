<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

// Khởi tạo session nếu chưa có
if (!isset($_SESSION['planet_status'])) {
    $_SESSION['planet_status'] = [
        1 => 'not-started',
        2 => 'not-started',
        3 => 'not-started',
        4 => 'not-started',
        5 => 'not-started'
    ];
}

echo json_encode([
    'success' => true,
    'planet_status' => $_SESSION['planet_status']
]);
?>