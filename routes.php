<?php
// --- TRÒ CHƠI PHA MÀU (Color Mixing Game) ---
$router->get('/science/color-game', [LessonController::class, 'showColorGame']);

// --- TRÒ CHƠI THÁP DINH DƯỠNG (Nutrition Game) ---
$router->get('/science/nutrition', [LessonController::class, 'showNutritionGame']);

// 2. Đường dẫn API (POST) để cập nhật điểm
$router->post('/science/update-score', [LessonController::class, 'updateNutritionScore']);

// --- TRÒ CHƠI ĐẾM SỐ (Number Game) ---
$router->post('/views/lessons/update-number-score', [LessonController::class, 'updateNumberGameScore']);

// --- QUÊN MẬT KHẨU (Forgot Password) ---
$router->get('/forgot-password', function() {
	require __DIR__ . '/views/forgot-password.php';
});

// API endpoints handled by AuthController
$router->post('/auth/forgot-password/send-code', [AuthController::class, 'sendResetCode']);
$router->post('/auth/forgot-password/verify-code', [AuthController::class, 'verifyResetCode']);
$router->post('/auth/forgot-password/reset', [AuthController::class, 'resetPassword']);