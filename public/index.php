<?php
// ============================================================
// FRONT CONTROLLER — public/index.php
// ============================================================
// CHECKPOINT C: Không có Auth/Session — scope đề bài thuần túy
// Luồng: Request → autoload → .env → routes → dispatch
// ============================================================

declare(strict_types=1);
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/autoload.php';

// Load .env
$dotenv = BASE_PATH . '/.env';
if (file_exists($dotenv)) {
    foreach (file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

// Session chỉ dùng cho CSRF token và Flash message
session_name($_ENV['SESSION_NAME'] ?? 'de13_session');
session_start();

// BASE_URL
define('BASE_URL', $_ENV['APP_URL'] ?? 'http://localhost/PersonalFinanceManagement-VanQuang/public');

// Router
$router = new \App\Core\Router();
require BASE_PATH . '/routes.php';

// Parse URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($scriptName !== '/' && str_starts_with($uri, $scriptName)) {
    $uri = substr($uri, strlen($scriptName));
}
if (str_starts_with($uri, '/public')) $uri = substr($uri, 7);
if ($uri === '' || $uri === false) $uri = '/';

$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);
