<?php
// app/Controllers/UserController.php

class UserController extends Controller {
    private function getWalletSettings() {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT key_name, value FROM settings WHERE key_name IN (
                'deposit_bank_code',
                'deposit_bank_name',
                'deposit_account_name',
                'deposit_account_number',
                'telegram_support_username',
                'telegram_support_url',
                'telegram_bot_token',
                'telegram_chat_id'
            )"
        );

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }

        return $settings;
    }

    private function sendTelegramMessage($message, $botToken, $chatId) {
        if (empty($botToken) || empty($chatId)) {
            return false;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        $payload = http_build_query([
            'chat_id' => $chatId,
            'text' => $message
        ]);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'content' => $payload,
                'timeout' => 10
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        return $response !== false;
    }
    
    public function dashboard() {
        $db = Database::getInstance();
        $userId = Auth::id();
        
        // Get statistics
        $stats = [
            'total_orders' => $db->fetchOne("SELECT COUNT(*) as total FROM orders WHERE user_id = ?", [$userId])['total'],
            'total_spent' => $db->fetchOne("SELECT SUM(total_amount) as total FROM orders WHERE user_id = ? AND payment_status = 'paid'", [$userId])['total'] ?? 0
        ];
        
        // Get recent orders
        $recentOrders = $db->fetchAll(
            "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$userId]
        );
        
        // Get wallet
        $walletService = new WalletService();
        $wallet = $walletService->getWallet($userId);
        
        $this->view('user/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'wallet' => $wallet
        ]);
    }
    
    public function profile() {
        $user = Auth::user();
        $this->view('user/profile', ['user' => $user]);
    }
    
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/profile');
            return;
        }
        
        CSRF::check();
        
        $userId = Auth::id();
        $name = $_POST['name'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $telegramChatId = $_POST['telegram_chat_id'] ?? '';
        
        $data = [
            'name' => $name,
            'phone' => $phone,
            'telegram_chat_id' => $telegramChatId
        ];
        
        // Handle avatar upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['avatar'], 'avatars');
            if ($upload['success']) {
                $data['avatar'] = $upload['path'];
            }
        }
        
        // Update password if provided
        if (!empty($_POST['new_password'])) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            $user = Auth::user();
            
            if (!password_verify($currentPassword, $user['password'])) {
                Session::setFlash('error', 'Mật khẩu hiện tại không đúng');
                $this->redirect('/user/profile');
                return;
            }
            
            if ($newPassword !== $confirmPassword) {
                Session::setFlash('error', 'Mật khẩu xác nhận không khớp');
                $this->redirect('/user/profile');
                return;
            }
            
            if (strlen($newPassword) < 6) {
                Session::setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự');
                $this->redirect('/user/profile');
                return;
            }
            
            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        $userModel = new User();
        $userModel->update($userId, $data);
        
        Session::setFlash('success', 'Cập nhật thông tin thành công');
        $this->redirect('/user/profile');
    }
    
    public function orders() {
        $orderModel = new Order();
        $page = $_GET['page'] ?? 1;
        
        $orders = $orderModel->getUserOrders(Auth::id(), $page, 20);
        
        $this->view('user/orders', [
            'orders' => $orders,
            'currentPage' => $page
        ]);
    }
    
    public function orderDetail($id) {
        $orderService = new OrderService();
        $order = $orderService->getOrderDetails($id, Auth::id());
        
        if (!$order) {
            http_response_code(404);
            echo "404 - Order Not Found";
            return;
        }
        
        $this->view('user/order-detail', ['order' => $order]);
    }
    
    public function wallet() {
        $walletService = new WalletService();
        $depositRequestModel = new DepositRequest();
        $wallet = $walletService->getWallet(Auth::id());
        $transactions = $walletService->getTransactions(Auth::id(), 50);
        $depositRequests = $depositRequestModel->getUserRequests(Auth::id());
        $walletSettings = $this->getWalletSettings();
        
        $this->view('user/wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'depositRequests' => $depositRequests,
            'walletSettings' => $walletSettings
        ]);
    }

    public function confirmDeposit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
        }

        CSRF::check();

        $amount = (int)($_POST['amount'] ?? 0);
        if ($amount < 50000 || $amount > 5000000) {
            $this->json(['success' => false, 'message' => 'Số tiền nạp phải từ 50.000đ đến 5.000.000đ'], 422);
        }

        $settings = $this->getWalletSettings();
        $botToken = trim($settings['telegram_bot_token'] ?? '');
        $chatId = trim($settings['telegram_chat_id'] ?? '');

        if ($botToken === '' || $chatId === '') {
            $this->json(['success' => false, 'message' => 'Telegram bot token hoặc chat id chưa được cấu hình'], 422);
        }

        $user = Auth::user();
        $transferContent = 'NAP' . str_pad((string)Auth::id(), 4, '0', STR_PAD_LEFT);
        $supportTelegram = trim($settings['telegram_support_username'] ?? '@specademy');
        $depositRequestModel = new DepositRequest();

        $depositCode = 'DEP' . date('Ymd') . rand(1000, 9999);

        $depositRequestId = $depositRequestModel->create([
            'user_id' => Auth::id(),
            'deposit_code' => $depositCode,
            'amount' => $amount,
            'transfer_code' => $transferContent,
            'bank_code' => trim($settings['deposit_bank_code'] ?? 'mb'),
            'bank_name' => trim($settings['deposit_bank_name'] ?? ''),
            'account_name' => trim($settings['deposit_account_name'] ?? ''),
            'account_number' => trim($settings['deposit_account_number'] ?? ''),
            'status' => 'pending'
        ]);

        $message = implode("\n", [
            'YEU CAU XAC NHAN NAP TIEN',
            'Request ID: ' . $depositRequestId,
            'User ID: ' . Auth::id(),
            'Ten: ' . ($user['name'] ?? ''),
            'Username: ' . ($user['username'] ?? ''),
            'Email: ' . ($user['email'] ?? ''),
            'So tien: ' . number_format($amount, 0, ',', '.') . 'đ',
            'Noi dung CK: ' . $transferContent,
            'Telegram ho tro: ' . $supportTelegram
        ]);

        $sent = $this->sendTelegramMessage($message, $botToken, $chatId);

        if (!$sent) {
            $this->json([
                'success' => true,
                'message' => 'Yêu cầu nạp tiền đã được tạo và đang chờ admin duyệt.'
            ]);
        }

        $this->json([
            'success' => true,
            'message' => 'Admin đã nhận được thông tin nạp tiền. Yêu cầu của bạn đang chờ duyệt.'
        ]);
    }
}
