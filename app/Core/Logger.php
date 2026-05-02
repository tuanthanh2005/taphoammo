<?php
// app/Core/Logger.php

class Logger {
    public static function error($message, $context = []) {
        try {
            $db = Database::getInstance();
            $userId = Auth::check() ? Auth::id() : null;
            
            $data = [
                'user_id' => $userId,
                'error_message' => $message,
                'error_code' => $context['code'] ?? null,
                'file' => $context['file'] ?? null,
                'line' => $context['line'] ?? null,
                'trace' => $context['trace'] ?? null,
                'url' => $_SERVER['REQUEST_URI'] ?? null,
                'method' => $_SERVER['REQUEST_METHOD'] ?? null,
                'ip_address' => Helper::getIpAddress(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ];
            
            $db->insert('error_logs', $data);
        } catch (Exception $e) {
            self::logToFile('ERROR', $message, $context);
        }
    }

    public static function logException(Throwable $e) {
        self::error($e->getMessage(), [
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }

    public static function activity($action, $userId = null) {
        try {
            $db = Database::getInstance();
            $userId = $userId ?: (Auth::check() ? Auth::id() : null);
            
            $db->insert('logs', [
                'user_id' => $userId,
                'action' => $action,
                'ip_address' => Helper::getIpAddress(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            self::logToFile('ACTIVITY', $action, ['user_id' => $userId]);
        }
    }

    private static function logToFile($level, $message, $context = []) {
        $logFile = __DIR__ . '/../../storage/logs/' . strtolower($level) . '.log';
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message} " . json_encode($context, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
