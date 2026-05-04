<?php
// app/Core/Middleware.php

class Middleware {
    public function handle() {
        return true;
    }
}

class AuthMiddleware extends Middleware {
    public function handle() {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        return true;
    }
}

class GuestMiddleware extends Middleware {
    public function handle() {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }
        return true;
    }
}

class AdminMiddleware extends Middleware {
    public function handle() {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        return true;
    }
}

class SellerMiddleware extends Middleware {
    public function handle() {
        if (!Auth::isSeller()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        return true;
    }
}

class AffiliateMiddleware extends Middleware {
    public function handle() {
        if (!Auth::isAffiliate()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        return true;
    }
}

class SpamMiddleware extends Middleware {
    public function handle() {
        // 1. Check if user is temporarily banned
        if (Auth::check()) {
            $user = Auth::user();
            if ($user && !empty($user['banned_until'])) {
                $bannedUntil = strtotime($user['banned_until']);
                if ($bannedUntil > time()) {
                    $remaining = $bannedUntil - time();
                    $minutes = ceil($remaining / 60);
                    http_response_code(403);
                    die("Tài khoản của bạn đang bị tạm khóa do nghi ngờ spam. Vui lòng thử lại sau {$minutes} phút.");
                }
            }
        }

        // 2. Rate Limiting Logic
        $now = time();
        $ip = $_SERVER['REMOTE_ADDR'];
        $userId = Auth::id();
        
        // Track history in session
        if (!isset($_SESSION['request_history'])) {
            $_SESSION['request_history'] = [];
        }

        // Add current request
        $_SESSION['request_history'][] = $now;

        // Clean up history (older than 60 seconds)
        $_SESSION['request_history'] = array_filter($_SESSION['request_history'], function($t) use ($now) {
            return $t > ($now - 60);
        });

        $requestCount = count($_SESSION['request_history']);
        $threshold = 60; // 60 requests per minute

        if ($requestCount > $threshold) {
            // Trigger alert only once per 10 requests above threshold to avoid DB flood
            if ($requestCount % 10 === 0) {
                $this->logSpam($userId, $ip, $requestCount);
            }
            
            if ($requestCount > 100) { // Hard limit
                http_response_code(429);
                die("Bạn đang gửi quá nhiều yêu cầu. Vui lòng chậm lại.");
            }
        }

        return true;
    }

    private function logSpam($userId, $ip, $count) {
        $db = Database::getInstance();
        
        // Check if we already logged this recently to avoid duplicate alerts
        $recent = $db->fetchOne(
            "SELECT id FROM spam_alerts WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1",
            [$ip]
        );

        if (!$recent) {
            $db->insert('spam_alerts', [
                'user_id' => $userId,
                'ip_address' => $ip,
                'type' => 'request_flood',
                'description' => "Phát hiện spam request: {$count} req/min",
                'request_count' => $count
            ]);

            // Notify Admin via Telegram
            $adminUser = $db->fetchOne("SELECT telegram_chat_id FROM users WHERE role = 'admin' AND telegram_chat_id IS NOT NULL LIMIT 1");
            if ($adminUser) {
                $userName = Auth::check() ? Auth::user()['name'] : 'Khách';
                $msg = "🚨 <b>CẢNH BÁO SPAM REQUEST</b>\n";
                $msg .= "Người dùng: {$userName}\n";
                $msg .= "IP: {$ip}\n";
                $msg .= "Tốc độ: {$count} req/phút\n";
                $msg .= "Hành động: Đã ghi nhận cảnh báo.";
                Helper::sendTelegramMessage($adminUser['telegram_chat_id'], $msg);
            }
        }
    }
}
