<?php
// public/index.php

// Xác định đường dẫn root
// Nếu trên hosting, thư mục gốc nằm ngoài public_html
define('ROOT_PATH', dirname(__DIR__));

// Load environment variables
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, '"');
    }
}

// Error reporting
if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Start session
session_start();

// Autoload core classes
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/app/Core/' . $class . '.php',
        ROOT_PATH . '/app/Controllers/' . $class . '.php',
        ROOT_PATH . '/app/Models/' . $class . '.php',
        ROOT_PATH . '/app/Services/' . $class . '.php',
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Load Helper functions (phải load trước khi dùng)
require_once ROOT_PATH . '/app/Core/Helper.php';
require_once ROOT_PATH . '/app/Core/Middleware.php';

// Load routes
$router = new Router();
require_once ROOT_PATH . '/routes/web.php';

// Check maintenance mode & Update activity
try {
    $db = Database::getInstance();
    
    // Maintenance
    $maintenance = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'maintenance_mode'");
    if ($maintenance && $maintenance['value'] == '1') {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (strpos($uri, '/admin') !== 0 && strpos($uri, '/login') !== 0 && strpos($uri, '/logout') !== 0 && strpos($uri, '/auth') !== 0) {
            http_response_code(503);
            require_once ROOT_PATH . '/app/Views/errors/503.php';
            exit;
        }
    }

    // Activity
    if (Auth::check()) {
        $db->query("UPDATE users SET last_active_at = NOW() WHERE id = ?", [Auth::id()]);
    }
} catch (Exception $e) {
    // Ignore if DB is not set up
}

// Dispatch
$router->dispatch();
