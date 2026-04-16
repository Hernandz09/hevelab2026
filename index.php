<?php

declare(strict_types=1);

date_default_timezone_set('America/Bogota');

require_once __DIR__ . '/app/Core/Autoloader.php';

use App\Core\Autoloader;
use App\Core\Request;
use App\Core\Router;
use App\Controllers\ApiController;
use App\Controllers\ConfigController;
use App\Controllers\DashboardController;
use App\Controllers\HomeController;

Autoloader::register();

$router = new Router();
$request = Request::capture();

$router->get('/', [HomeController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/products', [HomeController::class, 'products']);

$router->get('/api/view/{page}', [ApiController::class, 'page']);
$router->get('/api/products', [ApiController::class, 'products']);
$router->get('/api/config/flags', [ConfigController::class, 'getFlags']);
$router->post('/api/config/flags', [ConfigController::class, 'saveFlags']);

require __DIR__ . '/routes/web.php';

$router->dispatch($request);
