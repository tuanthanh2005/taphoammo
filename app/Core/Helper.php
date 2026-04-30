<?php
// app/Core/Helper.php

class Helper {
    
    public static function url($path = '') {
        $baseUrl = $_ENV['APP_URL'] ?? '';
        if (empty($baseUrl)) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl = $protocol . '://' . $host;
        }
        $baseUrl = rtrim($baseUrl, '/');
        $path = '/' . ltrim($path, '/');
        return $baseUrl . $path;
    }
    
    public static function asset($path) {
        return self::url('assets/' . ltrim($path, '/'));
    }
    
    public static function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    public static function back() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirect($referer);
    }
    
    public static function formatMoney($amount) {
        return number_format($amount ?? 0, 0, ',', '.') . 'đ';
    }

    public static function formatCompactMoney($amount) {
        $amount = (float)($amount ?? 0);
        if ($amount <= 0) return '0đ';
        if ($amount < 1000) return number_format($amount, 0, ',', '.') . 'đ';
        if ($amount < 1000000) {
            $value = $amount / 1000;
            if (floor($value) == $value) return number_format($value, 0, ',', '.') . 'K';
            $formatted = number_format($value, 1, ',', '.');
            return rtrim(rtrim($formatted, '0'), ',') . 'K';
        }
        $value = $amount / 1000000;
        if (floor($value) == $value) return number_format($value, 0, ',', '.') . 'M';
        $formatted = number_format($value, 1, ',', '.');
        return rtrim(rtrim($formatted, '0'), ',') . 'M';
    }
    
    public static function formatDate($date, $format = 'd/m/Y H:i') {
        return date($format, strtotime($date));
    }
    
    public static function timeAgo($datetime) {
        $time = strtotime($datetime);
        $diff = time() - $time;
        if ($diff < 60) return $diff . ' giây trước';
        elseif ($diff < 3600) return floor($diff / 60) . ' phút trước';
        elseif ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
        elseif ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
        else return date('d/m/Y', $time);
    }
    
    public static function generateSlug($string) {
        $string = strtolower($string);
        $string = preg_replace('/[áàảãạăắằẳẵặâấầẩẫậ]/u', 'a', $string);
        $string = preg_replace('/[éèẻẽẹêếềểễệ]/u', 'e', $string);
        $string = preg_replace('/[íìỉĩị]/u', 'i', $string);
        $string = preg_replace('/[óòỏõọôốồổỗộơớờởỡợ]/u', 'o', $string);
        $string = preg_replace('/[úùủũụưứừửữự]/u', 'u', $string);
        $string = preg_replace('/[ýỳỷỹỵ]/u', 'y', $string);
        $string = preg_replace('/đ/u', 'd', $string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        return trim($string, '-');
    }
    
    public static function generateCode($prefix = '', $length = 8) {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $prefix . $code;
    }
    
    public static function uploadFile($file, $directory = 'uploads') {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'Không có file được upload'];
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Chỉ chấp nhận file ảnh JPG, PNG, GIF, WEBP'];
        }
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File không được vượt quá 5MB'];
        }
        if (defined('ROOT_PATH')) {
            $uploadDir = ROOT_PATH . '/public/assets/' . $directory . '/';
        } else {
            $uploadDir = __DIR__ . '/../../public/assets/' . $directory . '/';
        }
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename, 'path' => $directory . '/' . $filename];
        }
        return ['success' => false, 'message' => 'Lỗi khi upload file'];
    }
    
    public static function escape($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public static function truncate($string, $length = 100, $suffix = '...') {
        if (mb_strlen($string) <= $length) {
            return $string;
        }
        return mb_substr($string, 0, $length) . $suffix;
    }

    public static function sendTelegramMessage($chatId, $message) {
        if (empty($chatId)) return false;
        
        $db = Database::getInstance();
        $token = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'telegram_bot_token'")['value'] ?? '';
        
        if (empty($token)) return false;
        
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response !== false;
    }
}

// Helper functions
function url($path = '') {
    return Helper::url($path);
}

function asset($path) {
    return Helper::asset($path);
}

function e($string) {
    return Helper::escape($string);
}

function money($amount) {
    return Helper::formatMoney($amount);
}

function compact_money($amount) {
    return Helper::formatCompactMoney($amount);
}

function csrf_field() {
    return CSRF::field();
}

function csrf_token() {
    return CSRF::generateToken();
}

function old($key, $default = '') {
    return $_POST[$key] ?? $default;
}

function auth() {
    return Auth::user();
}
