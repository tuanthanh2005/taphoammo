<?php
define('ROOT_PATH', __DIR__);
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, '"');
    }
}
spl_autoload_register(function ($class) {
    $paths = [ROOT_PATH . '/app/Core/' . $class . '.php'];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

$db = Database::getInstance();
$db->query("UPDATE users SET name = 'Hệ thống NPC', username = 'NPC' WHERE id = 1");
echo "Admin renamed to NPC System.\n";
unlink(__FILE__);
