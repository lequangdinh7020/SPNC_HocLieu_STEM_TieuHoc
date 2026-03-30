<?php
session_start();
require_once __DIR__ . '/controllers/AuthController.php';

$message = '';
try {
    $auth = new AuthController();
    if (isset($_GET['email']) && isset($_GET['code'])) {
        $email = trim($_GET['email']);
        $code = trim($_GET['code']);

        require_once __DIR__ . '/models/Database.php';
        require_once __DIR__ . '/models/User.php';
        $db = (new Database())->getConnection();
        $user = new User($db);

        if ($user->verifyEmail($email, $code)) {
            $_SESSION['success'] = "Xác thực email thành công. Bạn có thể đăng nhập.";
            header('Location: views/signin.php');
            exit;
        } else {
            $message = "Mã xác thực không hợp lệ hoặc đã hết hạn.";
        }
    } else {
        $message = "Liên kết xác thực không hợp lệ.";
    }
} catch (Exception $e) {
    error_log('Verify error: ' . $e->getMessage());
    $message = 'Có lỗi khi xác thực. Vui lòng thử lại sau.';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Xác thực email - STEM Universe</title>
    <link rel="stylesheet" href="public/css/bootstrap.min.css">
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            font-family: Arial, Helvetica, sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }
        .box {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .icon {
            font-size: 48px;
            text-align: center;
            display: block;
            margin-bottom: 20px;
            color: #007bff;
        }
        .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            line-height: 1.5;
        }
        .links {
            text-align: center;
        }
        .links a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="box">
        <i class="fas fa-envelope-open-text icon"></i>
        <h1>Xác thực email</h1>
        <div class="message">
            <?php if (!empty($message)): ?>
                <p class="error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="links">
            <a href="views/signin.php" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i>
                Đăng nhập
            </a>
            <a href="views/signup.php" class="btn btn-outline-secondary">
                <i class="fas fa-user-plus"></i>
                Đăng ký tài khoản mới
            </a>
        </div>
    </div>

    <script src="public/js/bootstrap.min.js"></script>
</body>
</html>
