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

    public static function getIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    public static function buildQuery($params = []) {
        $query = $_GET;
        foreach ($params as $key => $value) {
            $query[$key] = $value;
        }
        return '?' . http_build_query($query);
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

    public static function formatWarranty($days) {
        $days = (int)($days ?? 0);
        return $days > 0 ? $days . ' ngày' : 'Không bảo hành';
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

    public static function slugify($string) {
        $string = trim((string)$string);
        if ($string === '') {
            return '';
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
            if ($converted !== false) {
                $string = $converted;
            }
        }

        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9]+/', '-', $string);
        $string = trim($string, '-');

        return $string !== '' ? $string : 'bai-viet';
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
    
    public static function renderArticleContent($content) {
        $content = trim((string)$content);
        if ($content === '') {
            return '';
        }

        $content = str_replace(["\r\n", "\r"], "\n", $content);
        
        // Match images like [img]...[/img] or a plain URL ending in an image extension on its own line
        $content = preg_replace_callback('/(?:^|\n)\[img\](https?:\/\/[^\s\[\]]+)\[\/img\](?:\n|$)/iu', function($m) {
            $src = e($m[1]);
            return "\n\n<figure class=\"article-media\"><div class=\"article-media-frame\"><img src=\"{$src}\" alt=\"Article image\" loading=\"lazy\"></div></figure>\n\n";
        }, $content);
        
        $content = preg_replace_callback('/(?:^|\n)(https?:\/\/\S+\.(?:jpg|jpeg|png|gif|webp|svg))(?:\?\S*)?(?:\n|$)/iu', function($m) {
            $src = e($m[1]);
            return "\n\n<figure class=\"article-media\"><div class=\"article-media-frame\"><img src=\"{$src}\" alt=\"Article image\" loading=\"lazy\"></div></figure>\n\n";
        }, $content);

        $blocks = preg_split("/\n\s*\n+/u", $content);
        $html = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            // If block is already a figure tag (from our preg_replace above), just add it
            if (strpos($block, '<figure') === 0) {
                $html[] = $block;
                continue;
            }

            if (preg_match('/^##\s+(.+)$/u', $block, $matches)) {
                $html[] = '<h3>' . self::formatArticleInline($matches[1]) . '</h3>';
                continue;
            }

            if (preg_match('/^#\s+(.+)$/u', $block, $matches)) {
                $html[] = '<h2>' . self::formatArticleInline($matches[1]) . '</h2>';
                continue;
            }

            $lines = preg_split("/\n/u", $block);
            $isList = true;
            foreach ($lines as $line) {
                if (!preg_match('/^\s*[-*]\s+.+$/u', $line)) {
                    $isList = false;
                    break;
                }
            }

            if ($isList) {
                $items = [];
                foreach ($lines as $line) {
                    $item = preg_replace('/^\s*[-*]\s+/u', '', trim($line));
                    $items[] = '<li>' . self::formatArticleInline($item) . '</li>';
                }
                $html[] = '<ul>' . implode('', $items) . '</ul>';
                continue;
            }

            $paragraph = implode("<br>\n", array_map(function ($line) {
                return self::formatArticleInline(trim($line));
            }, $lines));
            $html[] = '<p>' . $paragraph . '</p>';
        }

        return implode("\n", $html);
    }

    private static function formatArticleInline($text) {
        $text = e($text);
        $text = preg_replace('/\*\*(.+?)\*\*/u', '<strong>$1</strong>', $text);
        $text = preg_replace('/\*(.+?)\*/u', '<em>$1</em>', $text);
        $text = preg_replace_callback('/(https?:\/\/[^\s<]+)/iu', function ($matches) {
            $url = e($matches[1]);
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">' . $url . '</a>';
        }, $text);
        return $text;
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
    
    public static function uploadDocumentFile($file, $directory = 'downloads', $allowedExtensions = null, $maxSize = 52428800) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'message' => 'Không có file được upload'];
        }

        $allowedExtensions = $allowedExtensions ?: [
            'pdf', 'epub', 'mobi', 'azw3',
            'zip', 'rar', '7z',
            'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
            'txt', 'csv'
        ];

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File không được vượt quá 50MB'];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            return ['success' => false, 'message' => 'Định dạng file không được hỗ trợ'];
        }

        if (defined('ROOT_PATH')) {
            $uploadDir = ROOT_PATH . '/public/assets/' . $directory . '/';
        } else {
            $uploadDir = __DIR__ . '/../../public/assets/' . $directory . '/';
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
        $safeBaseName = trim($safeBaseName, '-');
        if ($safeBaseName === '') {
            $safeBaseName = 'file';
        }

        $filename = $safeBaseName . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file['name'],
                'path' => $directory . '/' . $filename
            ];
        }

        return ['success' => false, 'message' => 'Lỗi khi upload file'];
    }

    public static function getCategoryProductProfile($category) {
        $name = '';
        $slug = '';

        if (is_array($category)) {
            $name = mb_strtolower(trim((string)($category['name'] ?? '')));
            $slug = strtolower(trim((string)($category['slug'] ?? '')));
        } else {
            $name = mb_strtolower(trim((string)$category));
        }

        $haystack = trim($slug . ' ' . $name);
        $profiles = [
            [
                'match' => ['tai-lieu', 'tai lieu', 'ebook', 'e-book', 'document'],
                'key' => 'document',
                'label' => 'Tài liệu / Ebook',
                'icon' => 'fa-book-open',
                'description' => 'Bán ebook, template, file tài liệu hoặc tải xuống.',
                'suggested_product_type' => 'file',
                'stock_mode' => 'file',
                'stock_label' => 'Tải file giao cho khách',
                'stock_help' => 'Mỗi file upload được xem như 1 sản phẩm có thể giao cho khách.',
                'stock_placeholder' => 'Upload PDF, EPUB, DOCX, ZIP...',
                'note_label' => 'Ghi chú từ người mua (nếu cần)',
                'note_help' => 'Thường không cần bắt buộc ghi chú cho sản phẩm file.',
                'recommended_require_note' => false,
                'allowed_product_types' => ['file', 'link']
            ],
            [
                'match' => ['tai-khoan', 'tai khoan', 'account', 'email marketing', 'email-marketing'],
                'key' => 'account',
                'label' => 'Tài khoản / Email',
                'icon' => 'fa-user-shield',
                'description' => 'Bán account, email, cookie hoặc combo thông tin đăng nhập.',
                'suggested_product_type' => 'account',
                'stock_mode' => 'lines',
                'stock_label' => 'Nhập account theo từng dòng',
                'stock_help' => 'Mỗi dòng nên là 1 tài khoản hoàn chỉnh. Ví dụ: email|matkhau|ghi_chu.',
                'stock_placeholder' => "email1@gmail.com|matkhau1|ghi chu\nemail2@gmail.com|matkhau2|2FA backup",
                'note_label' => 'Yêu cầu khách nhập thông tin nhận hàng',
                'note_help' => 'Nên bật nếu bạn cần email nhận account, profile giao hàng hoặc yêu cầu cấu hình.',
                'recommended_require_note' => true,
                'allowed_product_types' => ['account', 'key']
            ],
            [
                'match' => ['phan-mem', 'phan mem', 'software', 'website', 'thiet-ke-website', 'thiet ke website', 'blockchain'],
                'key' => 'software',
                'label' => 'Phần mềm / Công cụ số',
                'icon' => 'fa-laptop-code',
                'description' => 'Bán key bản quyền, mã kích hoạt, link cài đặt hoặc tool số.',
                'suggested_product_type' => 'key',
                'stock_mode' => 'lines',
                'stock_label' => 'Nhập key, link hoặc mã kích hoạt',
                'stock_help' => 'Mỗi dòng là 1 key/link riêng. Có thể dùng định dạng KEY|LINK|GHI_CHU.',
                'stock_placeholder' => "XXXXX-XXXXX-XXXXX\nhttps://download.example.com/file.zip|Huong dan cai dat",
                'note_label' => 'Ghi chú bổ sung từ khách',
                'note_help' => 'Bật nếu cần email, domain, hoặc thông tin cài đặt từ người mua.',
                'recommended_require_note' => false,
                'allowed_product_types' => ['key', 'link', 'file']
            ],
            [
                'match' => ['ai', 'tang-tuong-tac', 'tang tuong tac', 'dich-vu-khac', 'dich vu khac', 'service'],
                'key' => 'service',
                'label' => 'Dịch vụ / AI',
                'icon' => 'fa-wand-magic-sparkles',
                'description' => 'Bán gói AI, setup dịch vụ, buff, tool theo slot hoặc xử lý thủ công.',
                'suggested_product_type' => 'service',
                'stock_mode' => 'lines',
                'stock_label' => 'Tạo slot/gói giao hàng',
                'stock_help' => 'Mỗi dòng được xem như 1 slot có thể bán. Không cần upload file.',
                'stock_placeholder' => "Slot AI Premium 30 ngay\nGoi setup chatbot cho 1 website",
                'note_label' => 'Bắt buộc khách nhập yêu cầu',
                'note_help' => 'Nên bật để người mua cung cấp prompt, tài khoản, link profile, website cần setup...',
                'recommended_require_note' => true,
                'allowed_product_types' => ['service', 'link']
            ],
        ];

        foreach ($profiles as $profile) {
            foreach ($profile['match'] as $keyword) {
                if ($keyword !== '' && str_contains($haystack, $keyword)) {
                    return $profile;
                }
            }
        }

        return [
            'key' => 'generic',
            'label' => 'Sản phẩm số',
            'icon' => 'fa-box-open',
            'description' => 'Cấu hình linh hoạt cho key, account, file hoặc link.',
            'suggested_product_type' => 'key',
            'stock_mode' => 'lines',
            'stock_label' => 'Nhập nội dung giao hàng',
            'stock_help' => 'Mỗi dòng tương ứng với 1 đơn vị sản phẩm.',
            'stock_placeholder' => "Noi dung 1\nNoi dung 2",
            'note_label' => 'Ghi chú từ người mua',
            'note_help' => 'Bật nếu bạn cần thêm thông tin để xử lý đơn.',
            'recommended_require_note' => false,
            'allowed_product_types' => ['key', 'account', 'file', 'link', 'service']
        ];
    }

    public static function encodeStockContent($type, array $payload) {
        return json_encode(array_merge(['type' => $type], $payload), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function parseStockContent($content) {
        $content = (string)$content;
        $decoded = json_decode($content, true);

        if (is_array($decoded) && !empty($decoded['type'])) {
            if ($decoded['type'] === 'file') {
                $path = $decoded['path'] ?? '';
                $name = $decoded['name'] ?? basename($path);
                return [
                    'type' => 'file',
                    'name' => $name,
                    'path' => $path,
                    'download_url' => $path ? asset($path) : '',
                    'display_text' => $name,
                    'raw' => $content
                ];
            }

            if ($decoded['type'] === 'link') {
                $url = $decoded['url'] ?? '';
                $label = $decoded['label'] ?? $url;
                return [
                    'type' => 'link',
                    'url' => $url,
                    'label' => $label,
                    'display_text' => $label,
                    'raw' => $content
                ];
            }

            if ($decoded['type'] === 'manual_delivery') {
                $message = $decoded['message'] ?? 'San pham ban giao thu cong.';
                if (!empty($decoded['order_code'])) {
                    $message = 'Đơn hàng #' . $decoded['order_code'] . ': ' . $message;
                }

                return [
                    'type' => 'manual_delivery',
                    'display_text' => $message,
                    'message' => $message,
                    'raw' => $content
                ];
            }

            return [
                'type' => 'text',
                'display_text' => $decoded['value'] ?? $decoded['text'] ?? $content,
                'raw' => $content
            ];
        }

        if (str_contains($content, 'Sản phẩm bàn giao thủ công. Vui lòng liên hệ Người bán qua mục Chat.')
            || str_contains($content, 'Đơn hàng #')) {
            return [
                'type' => 'manual_delivery',
                'display_text' => $content,
                'message' => $content,
                'raw' => $content
            ];
        }

        return [
            'type' => 'text',
            'display_text' => $content,
            'raw' => $content
        ];
    }

    public static function escape($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    public static function telegramEscape($string) {
        return htmlspecialchars((string)($string ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function truncate($string, $length = 100, $suffix = '...') {
        if (mb_strlen($string) <= $length) {
            return $string;
        }
        return mb_substr($string, 0, $length) . $suffix;
    }

    public static function sendTelegramMessage($chatId, $message, $botToken = null) {
        if (empty($chatId)) return false;

        $token = trim((string)$botToken);
        if ($token === '') {
            $db = Database::getInstance();
            $token = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'telegram_bot_token'")['value'] ?? '';
        }
        
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
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($response === false) {
            error_log("Telegram Curl Error: " . curl_error($ch));
        } elseif ($httpCode !== 200) {
            error_log("Telegram API Error (HTTP $httpCode): " . $response);
        }
        
        curl_close($ch);
        return $httpCode === 200;
    }

    public static function sendEmail($to, $subject, $message) {
        $siteName = $_ENV['SITE_NAME'] ?? 'AI CỦA TÔI';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: {$siteName} <noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">" . "\r\n";
        
        return @mail($to, $subject, $message, $headers);
    }

    public static function getSystemUserId() {
        static $cachedSystemUserId = null;

        if ($cachedSystemUserId !== null) {
            return $cachedSystemUserId;
        }

        $db = Database::getInstance();
        $systemUser = $db->fetchOne(
            "SELECT id
             FROM users
             WHERE role = 'admin' AND status NOT IN ('banned', 'deleted')
             ORDER BY id ASC
             LIMIT 1"
        );

        $cachedSystemUserId = (int)($systemUser['id'] ?? 1);
        return $cachedSystemUserId;
    }

    public static function getSystemDisplayName() {
        return 'AI CỦA TÔI';
    }

    public static function getSystemSetting($key, $default = null) {
        static $cache = [];

        $cacheKey = (string)$key;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $db = Database::getInstance();
        $setting = $db->fetchOne(
            "SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1",
            [$cacheKey]
        );

        $cache[$cacheKey] = $setting['setting_value'] ?? $default;
        return $cache[$cacheKey];
    }

    public static function getSettingValue($key, $default = null) {
        static $cache = [];

        $cacheKey = (string)$key;
        if (array_key_exists($cacheKey, $cache)) {
            return $cache[$cacheKey];
        }

        $db = Database::getInstance();
        $setting = $db->fetchOne(
            "SELECT value FROM settings WHERE key_name = ? LIMIT 1",
            [$cacheKey]
        );

        $cache[$cacheKey] = $setting['value'] ?? $default;
        return $cache[$cacheKey];
    }

    public static function getWalletTelegramSettings() {
        return [
            'support_username' => trim((string)self::getSettingValue('wallet_telegram_support_username', self::getSettingValue('telegram_support_username', '@specademy'))),
            'support_url' => trim((string)self::getSettingValue('wallet_telegram_support_url', self::getSettingValue('telegram_support_url', 'https://t.me/specademy'))),
            'chat_id' => trim((string)self::getSettingValue('wallet_telegram_chat_id', self::getSettingValue('telegram_chat_id', ''))),
            'bot_token' => trim((string)self::getSettingValue('wallet_telegram_bot_token', self::getSettingValue('telegram_bot_token', ''))),
        ];
    }

    public static function getMinimumDisputeHours() {
        return 24;
    }

    public static function getDisputeSellerResponseHours() {
        return max(1, (int)self::getSystemSetting('dispute_seller_response_hours', 24));
    }

    public static function sendSystemMessage($toUserId, $message) {
        $systemUserId = self::getSystemUserId();
        if ((int)$toUserId === $systemUserId) return;
        
        $db = Database::getInstance();
        
        // Tìm cuộc trò chuyện (Admin luôn đóng vai trò Seller trong các thông báo hệ thống)
        $conversation = $db->fetchOne(
            "SELECT * FROM conversations WHERE buyer_id = ? AND seller_id = ?",
            [$toUserId, $systemUserId]
        );
        
        if (!$conversation) {
            $convId = $db->insert('conversations', [
                'buyer_id' => $toUserId,
                'seller_id' => $systemUserId,
                'last_message' => $message,
                'last_message_at' => date('Y-m-d H:i:s'),
                'unread_count_buyer' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $convId = $conversation['id'];
            $db->query(
                "UPDATE conversations SET 
                    last_message = ?, 
                    last_message_at = NOW(), 
                    updated_at = NOW(),
                    unread_count_buyer = unread_count_buyer + 1
                WHERE id = ?",
                [$message, $convId]
            );
        }
        
        // Chèn tin nhắn
        $db->insert('messages', [
            'conversation_id' => $convId,
            'sender_id' => $systemUserId,
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
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
    $oldInput = Session::get('_old_input', []);
    return $oldInput[$key] ?? ($_POST[$key] ?? $default);
}

function save_old_input() {
    Session::set('_old_input', $_POST);
}

function clear_old_input() {
    Session::remove('_old_input');
}

function auth() {
    return Auth::user();
}
