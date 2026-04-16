<?php

declare(strict_types=1);

$modules = [
    '/modulo/laboratorio' => [\App\Controllers\HomeController::class, 'index'],
];

foreach ($modules as $path => $handler) {
    $router->get($path, $handler);
}

$router->get('/login', [\App\Controllers\AuthController::class, 'showLogin']);
$router->post('/login', [\App\Controllers\AuthController::class, 'login']);
$router->get('/register', [\App\Controllers\AuthController::class, 'showRegister']);
$router->post('/register', [\App\Controllers\AuthController::class, 'register']);
$router->get('/otp', [\App\Controllers\AuthController::class, 'showOtp']);
$router->post('/otp', [\App\Controllers\AuthController::class, 'verifyOtp']);
$router->get('/forgot', [\App\Controllers\AuthController::class, 'showForgot']);
$router->post('/forgot', [\App\Controllers\AuthController::class, 'requestReset']);
$router->get('/reset-password', [\App\Controllers\AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [\App\Controllers\AuthController::class, 'resetPassword']);
$router->get('/logout', [\App\Controllers\AuthController::class, 'logout']);

$router->post('/api/legacy/face-login', [\App\Controllers\LegacyApiController::class, 'faceLogin']);
$router->post('/api/face-login', [\App\Controllers\LegacyApiController::class, 'faceLogin']);
$router->get('/users', [\App\Controllers\AdminController::class, 'users']);
$router->get('/settings', [\App\Controllers\AdminController::class, 'settings']);

$router->get('/api/usuarios/{action}', [\App\Controllers\UsersApiController::class, 'handle']);
$router->post('/api/usuarios/{action}', [\App\Controllers\UsersApiController::class, 'handle']);
$router->get('/api/system-config', [\App\Controllers\SystemConfigApiController::class, 'read']);
$router->post('/api/system-config', [\App\Controllers\SystemConfigApiController::class, 'save']);

$router->get('/api/dashboard/overview', [\App\Controllers\DashboardController::class, 'overview']);
$router->post('/api/dashboard/cleanup-expired-otp', [\App\Controllers\DashboardController::class, 'cleanupExpiredOtp']);

$router->get('/api/legacy/validater/verificar_otp', [\App\Controllers\AuthController::class, 'showOtp']);
$router->post('/api/legacy/validater/verificar_otp', [\App\Controllers\AuthController::class, 'verifyOtp']);
