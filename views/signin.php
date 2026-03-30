<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../views/home.php');
    exit;
}

try {
    require_once '../controllers/AuthController.php';

    $authController = new AuthController();

    if (!isset($_SESSION['user_id']) && $authController->checkRememberToken()) {
        header('Location: ../views/home.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? true : false;
        
        $loginResult = $authController->login($username, $password, $remember);
        if ($loginResult === true) {
            $_SESSION['success'] = "Đăng nhập thành công!";
            header('Location: ../views/home.php');
            exit;
        } elseif ($loginResult === 'not_verified') {
            $error = "Email chưa được xác thực. Vui lòng kiểm tra email và xác thực trước khi đăng nhập.";
        } else {
            $error = "Tên đăng nhập hoặc mật khẩu không đúng";
        }
    }
} catch (Exception $e) {
    $error = "Hệ thống đang bảo trì. Vui lòng thử lại sau.";
    error_log("Login error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - STEM Universe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../public/CSS/signin.css">
</head>
<body>
    <div class="static-bg"></div>

    <div class="auth-container">
        <div class="auth-form">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h1 class="auth-title">Đăng nhập</h1>
                <p class="auth-subtitle">
                    Tiếp tục hành trình khám phá vũ trụ STEM
                </p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><?php echo $error; ?></p>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <p><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Tên đăng nhập
                    </label>
                    <input type="text" id="username" name="username" required 
                           placeholder="Nhập tên đăng nhập"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Mật khẩu
                    </label>
                    <div class="password-input-container">
                        <input type="password" id="password" name="password" required 
                               placeholder="Nhập mật khẩu">
                        <button type="button" class="toggle-password" data-target="password">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Ghi nhớ đăng nhập
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Quên mật khẩu?</a>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Đăng nhập
                </button>
            </form>

            <div class="auth-footer">
                <div class="auth-link">
                    <span>Chưa có tài khoản?</span>
                    <a href="signup.php" class="link">
                        <i class="fas fa-user-plus"></i>
                        Đăng ký ngay
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../public/JS/signin.js"></script>
</body>
</html>
