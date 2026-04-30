<?php
// config/app.php

return [
    'name' => $_ENV['APP_NAME'] ?? 'AI CỦA TÔI',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => $_ENV['APP_DEBUG'] ?? false,
    'timezone' => 'Asia/Ho_Chi_Minh',
];
