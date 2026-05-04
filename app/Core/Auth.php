<?php
// app/Core/Auth.php

class Auth {
    
    public static function check() {
        return isset($_SESSION['user_id']);
    }
    
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$_SESSION['user_id']]);
        
        return $user ?: null;
    }
    
    public static function id() {
        return $_SESSION['user_id'] ?? null;
    }
    
    public static function login($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Update last active time
        self::updateLastActive();
        
        // Log login
        self::logAction('login');
    }
    
    public static function updateLastActive() {
        if (!self::check()) {
            return;
        }
        
        $db = Database::getInstance();
        
        try {
            $column = $db->fetchOne("SHOW COLUMNS FROM users LIKE 'last_active_at'");
            if (!$column) {
                return;
            }

            $db->query(
                "UPDATE users SET last_active_at = ? WHERE id = ?",
                [date('Y-m-d H:i:s'), self::id()]
            );
        } catch (Exception $e) {
            return;
        }
    }
    
    public static function logout() {
        self::logAction('logout');
        
        session_unset();
        session_destroy();
        
        // Start new session
        session_start();
    }
    
    public static function isAdmin() {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }
    
    public static function isSeller() {
        $user = self::user();
        return $user && in_array($user['role'], ['seller', 'admin']);
    }
    
    public static function isAffiliate() {
        $user = self::user();
        return $user && in_array($user['role'], ['affiliate', 'seller', 'admin']);
    }
    
    public static function hasRole($role) {
        $user = self::user();
        return $user && $user['role'] === $role;
    }
    
    public static function attempt($email, $password) {
        $db = Database::getInstance();
        
        // Check login attempts
        if (self::tooManyAttempts($email)) {
            return ['success' => false, 'message' => 'Quá nhiều lần đăng nhập sai. Vui lòng thử lại sau 15 phút.'];
        }
        
        $user = $db->fetchOne("SELECT * FROM users WHERE email = ? OR username = ?", [$email, $email]);
        
        if (!$user) {
            self::recordAttempt($email);
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
        }
        
        if (in_array($user['status'], ['banned', 'suspended'], true)) {
            return ['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa.'];
        }
        
        if (!password_verify($password, $user['password'])) {
            self::recordAttempt($email);
            return ['success' => false, 'message' => 'Email hoặc mật khẩu không đúng.'];
        }
        
        // Clear login attempts
        self::clearAttempts($email);
        
        // Login user
        self::login($user);
        
        return ['success' => true, 'user' => $user];
    }
    
    private static function tooManyAttempts($email) {
        $key = 'login_attempts_' . md5($email);
        $attempts = $_SESSION[$key] ?? [];
        
        // Remove attempts older than 15 minutes
        $attempts = array_filter($attempts, function($time) {
            return $time > time() - 900;
        });
        
        return count($attempts) >= 5;
    }
    
    private static function recordAttempt($email) {
        $key = 'login_attempts_' . md5($email);
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = [];
        }
        $_SESSION[$key][] = time();
    }
    
    private static function clearAttempts($email) {
        $key = 'login_attempts_' . md5($email);
        unset($_SESSION[$key]);
    }
    
    public static function logAction($action, $description = '') {
        $db = Database::getInstance();
        $db->insert('logs', [
            'user_id' => self::id(),
            'action' => $action,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
}
