<?php
$router->get('/science/color-game', [LessonController::class, 'showColorGame']);

$router->get('/science/nutrition', [LessonController::class, 'showNutritionGame']);

$router->post('/science/update-score', [LessonController::class, 'updateNutritionScore']);

$router->post('/views/lessons/update-number-score', [LessonController::class, 'updateNumberGameScore']);

$router->get('/forgot-password', function() {
	require __DIR__ . '/views/forgot-password.php';
});

$router->post('/auth/forgot-password/send-code', [AuthController::class, 'sendResetCode']);
$router->post('/auth/forgot-password/verify-code', [AuthController::class, 'verifyResetCode']);
$router->post('/auth/forgot-password/reset', [AuthController::class, 'resetPassword']);