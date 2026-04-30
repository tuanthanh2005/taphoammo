<?php
// app/Core/Session.php

class Session {
    
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public static function flash($key, $value = null) {
        if ($value === null) {
            $value = self::get($key);
            self::remove($key);
            return $value;
        }
        
        self::set($key, $value);
    }
    
    public static function setFlash($key, $value) {
        self::set('_flash_' . $key, $value);
    }
    
    public static function getFlash($key) {
        $value = self::get('_flash_' . $key);
        self::remove('_flash_' . $key);
        return $value;
    }
    
    public static function hasFlash($key) {
        return self::has('_flash_' . $key);
    }
}
