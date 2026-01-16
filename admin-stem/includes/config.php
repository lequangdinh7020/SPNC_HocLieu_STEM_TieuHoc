<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'learning_app');

function getDBConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Kết nối database thất bại: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, 'utf8');
    return $conn;
}

function checkAdminLogin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        $base = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
        header('Location: ' . $base . '/login.php');
        exit();
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>