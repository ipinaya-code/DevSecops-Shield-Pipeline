<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\GitHubController;
use App\Controllers\ScanController;
use App\Core\Database;
use App\Core\Router;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
} else {
    spl_autoload_register(static function (string $class) use ($root): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
        $file = $root . '/src/' . $relative . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

Database::init($root . '/data/app.sqlite');

$router = new Router();
$router->get('/', [AuthController::class, 'showLogin']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);

// GitHub API routes
$router->get('/api/github/repos', [GitHubController::class, 'getRepositories']);
$router->post('/api/github/clone', [GitHubController::class, 'cloneRepository']);

// Scan routes
$router->post('/api/scan/start', [ScanController::class, 'startScan']);
$router->get('/api/scan/progress', [ScanController::class, 'getProgress']);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $path);
