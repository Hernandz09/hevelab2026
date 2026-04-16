<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$checks = [
    'Layout principal' => $root . '/app/Views/layouts/app.php',
    'Topbar componente' => $root . '/app/Views/components/topbar.php',
    'Sidebar componente' => $root . '/app/Views/components/sidebar.php',
    'Footer componente' => $root . '/app/Views/components/footer.php',
    'Router asincrono' => $root . '/public/assets/js/core/router.js',
    'Hook useAuth' => $root . '/public/assets/js/hooks/useAuth.js',
    'Hook useData' => $root . '/public/assets/js/hooks/useData.js',
    'Hook useTheme' => $root . '/public/assets/js/hooks/useTheme.js',
    'CSS componentes' => $root . '/public/assets/css/components/topbar.css',
    'Imagenes organizadas' => $root . '/public/assets/images/icons/logo.svg',
];

$failed = 0;

foreach ($checks as $name => $file) {
    if (!is_file($file)) {
        echo "[FAIL] {$name}: {$file}\n";
        $failed++;
    } else {
        echo "[OK] {$name}\n";
    }
}

$routerSource = file_get_contents($root . '/public/assets/js/core/router.js') ?: '';
$asyncRoutingReady = str_contains($routerSource, 'async navigate') && str_contains($routerSource, '/api/view/');
echo $asyncRoutingReady ? "[OK] Rutas asincronas\n" : "[FAIL] Rutas asincronas\n";
$failed += $asyncRoutingReady ? 0 : 1;

$lazySource = file_get_contents($root . '/public/assets/js/core/lazyLoader.js') ?: '';
$lazyReady = str_contains($lazySource, 'import(');
echo $lazyReady ? "[OK] Lazy loading\n" : "[FAIL] Lazy loading\n";
$failed += $lazyReady ? 0 : 1;

$hookIndex = file_get_contents($root . '/public/assets/js/hooks/index.js') ?: '';
$dynamicFlags = str_contains($hookIndex, 'this.flags') && str_contains($hookIndex, '/api/config/flags');
echo $dynamicFlags ? "[OK] Flags dinamicos de hooks\n" : "[FAIL] Flags dinamicos de hooks\n";
$failed += $dynamicFlags ? 0 : 1;

$perfSource = file_get_contents($root . '/public/assets/js/modules/productsModule.js') ?: '';
$asyncPerf = str_contains($perfSource, 'await') && str_contains($perfSource, 'fetchProducts');
echo $asyncPerf ? "[OK] Carga asincrona de datos\n" : "[FAIL] Carga asincrona de datos\n";
$failed += $asyncPerf ? 0 : 1;

require_once $root . '/app/Core/Autoloader.php';
\App\Core\Autoloader::register();

$router = new \App\Core\Router();
$router->get('/', static function (): void { echo 'OK_HOME'; });
$router->get('/dashboard', static function (): void { echo 'OK_DASH'; });

ob_start();
$router->dispatch(new \App\Core\Request('GET', '/'));
$out = ob_get_clean() ?: '';
$routerOk = str_contains($out, 'OK_HOME');
echo $routerOk ? "[OK] Match ruta /\n" : "[FAIL] Match ruta /\n";
$failed += $routerOk ? 0 : 1;

ob_start();
$router->dispatch(new \App\Core\Request('GET', '/dashboard'));
$out2 = ob_get_clean() ?: '';
$dashOk = str_contains($out2, 'OK_DASH');
echo $dashOk ? "[OK] Match ruta /dashboard\n" : "[FAIL] Match ruta /dashboard\n";
$failed += $dashOk ? 0 : 1;

if ($failed > 0) {
    echo "\nResultado: {$failed} validaciones fallaron.\n";
    exit(1);
}

echo "\nResultado: todas las validaciones pasaron.\n";
