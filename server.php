<?php

/**
 * Laravel/PHP built-in web server routing.
 * This file allows us to emulate Apache's "mod_rewrite" functionality from the
 * built-in PHP web server. This provides a convenient way to test a PHP
 * application without having installed a "real" web server software here.
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
);

if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    $filePath = __DIR__.'/public'.$uri;
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    $mimeTypes = [
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg'=> 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp'=> 'image/webp',
        'ico' => 'image/x-icon',
    ];
    
    $contentType = $mimeTypes[$ext] ?? mime_content_type($filePath);
    if (!$contentType) $contentType = 'application/octet-stream';
    
    header("Content-Type: $contentType");
    readfile($filePath);
    exit;
}

// Redirect all other requests to index.php
require_once __DIR__.'/public/index.php';
