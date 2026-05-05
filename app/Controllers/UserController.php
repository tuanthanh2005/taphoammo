<?php
// app/Controllers/UserController.php

class UserController extends Controller
{
    private function getWalletSettings()
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT key_name, value FROM settings WHERE key_name IN (
                'deposit_bank_code',
                'deposit_bank_name',
                'deposit_account_name',
                'deposit_account_number',
                'telegram_support_username',
                'telegram_support_url',
                'wallet_telegram_support_username',
                'wallet_telegram_support_url',
                'wallet_telegram_bot_token',
                'wallet_telegram_chat_id',
                'telegram_bot_token',
                'telegram_chat_id'
            )"
        );

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value'];
        }

        $walletTelegram = Helper::getWalletTelegramSettings();
        $settings['wallet_telegram_support_username'] = $walletTelegram['support_username'];
        $settings['wallet_telegram_support_url'] = $walletTelegram['support_url'];
        $settings['wallet_telegram_bot_token'] = $walletTelegram['bot_token'];
        $settings['wallet_telegram_chat_id'] = $walletTelegram['chat_id'];

        return $settings;
    }

    private function sendTelegramMessage($message, $botToken, $chatId)
    {
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

    public function dashboard()
    {
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

    public function profile()
    {
        $user = Auth::user();
        $this->view('user/profile', ['user' => $user]);
    }

    public function updateProfile()
    {
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

    public function orders()
    {
        $orderModel = new Order();
        $page = $_GET['page'] ?? 1;

        $orders = $orderModel->getUserOrders(Auth::id(), $page, 10);

        $this->view('user/orders', [
            'orders' => $orders,
            'currentPage' => $page
        ]);
    }

    public function orderDetail($id)
    {
        $orderService = new OrderService();
        $order = $orderService->getOrderDetails($id, Auth::id());

        if (!$order) {
            http_response_code(404);
            echo "404 - Order Not Found";
            return;
        }

        // Lấy thông tin khiếu nại (nếu có)
        $db = Database::getInstance();
        $disputes = $db->fetchAll("SELECT * FROM disputes WHERE order_id = ?", [$id]);
        $disputeCountsByItem = [];
        foreach ($disputes as $d) {
            $itemId = (int) ($d['order_item_id'] ?? 0);
            if ($itemId > 0) {
                $disputeCountsByItem[$itemId] = ($disputeCountsByItem[$itemId] ?? 0) + 1;
            }
        }
        $activeDispute = null;
        foreach ($disputes as $d) {
            if (in_array($d['status'], ['open', 'under_review'])) {
                $activeDispute = $d;
                break;
            }
        }

        // Lấy thông tin đánh giá
        $reviews = $db->fetchAll("SELECT * FROM reviews WHERE order_id = ? AND user_id = ?", [$id, Auth::id()]);
        $reviewsByProduct = [];
        foreach ($reviews as $r) {
            $reviewsByProduct[$r['product_id']] = $r;
        }

        $this->view('user/order-detail', [
            'order' => $order,
            'disputes' => $disputes,
            'disputeCount' => count($disputes),
            'disputeCountsByItem' => $disputeCountsByItem,
            'activeDispute' => $activeDispute,
            'reviewsByProduct' => $reviewsByProduct
        ]);
    }

    public function submitDispute($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/orders/' . $id);
            return;
        }

        CSRF::check();

        $reason = $_POST['reason'] ?? 'other';
        $description = trim($_POST['description'] ?? '');
        $orderItemId = (int) ($_POST['order_item_id'] ?? 0);

        if (empty($description)) {
            Session::setFlash('error', 'Vui lòng nhập chi tiết vấn đề.');
            $this->redirect('/user/orders/' . $id);
            return;
        }

        $evidenceImages = [];
        if (isset($_FILES['evidence_images']) && !empty($_FILES['evidence_images']['name'][0])) {
            $files = $_FILES['evidence_images'];
            $count = count($files['name']);
            if ($count > 3) {
                Session::setFlash('error', 'Chỉ được upload tối đa 3 ảnh.');
                $this->redirect('/user/orders/' . $id);
                return;
            }

            for ($i = 0; $i < $count; $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileArray = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    $upload = Helper::uploadFile($fileArray, 'disputes');
                    if (is_array($upload) && $upload['success']) {
                        $evidenceImages[] = $upload['path'];
                    }
                }
            }
        }

        require_once __DIR__ . '/../Services/DisputeService.php';
        $disputeService = new DisputeService();
        $result = $disputeService->createDispute(Auth::id(), $id, $reason, $description, $evidenceImages, $orderItemId ?: null);

        if ($result['success']) {
            Session::setFlash('success', 'Đã gửi khiếu nại. Admin sẽ sớm xử lý.');
        } else {
            Session::setFlash('error', $result['message']);
        }

        $this->redirect('/user/orders/' . $id);
    }

    public function submitReview($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/user/orders/' . $id);
            return;
        }

        CSRF::check();

        $rating = (int) ($_POST['rating'] ?? 5);
        $comment = trim($_POST['comment'] ?? '');
        $productId = (int) ($_POST['product_id'] ?? 0);

        if ($rating < 1 || $rating > 5) {
            Session::setFlash('error', 'Số sao không hợp lệ.');
            $this->redirect('/user/orders/' . $id);
            return;
        }

        $db = Database::getInstance();

        // Check if already reviewed
        $exists = $db->fetchOne("SELECT id FROM reviews WHERE user_id = ? AND order_id = ? AND product_id = ?", [Auth::id(), $id, $productId]);
        if ($exists) {
            Session::setFlash('error', 'Bạn đã đánh giá sản phẩm này rồi.');
            $this->redirect('/user/orders/' . $id);
            return;
        }

        $db->query(
            "INSERT INTO reviews (user_id, product_id, order_id, rating, comment, status) VALUES (?, ?, ?, ?, ?, 'approved')",
            [Auth::id(), $productId, $id, $rating, $comment]
        );

        // Update product rating_avg and rating_count
        $stats = $db->fetchOne("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM reviews WHERE product_id = ? AND status = 'approved'", [$productId]);
        $db->query("UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?", [$stats['avg_r'], $stats['cnt'], $productId]);

        Session::setFlash('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
        $this->redirect('/user/orders/' . $id);
    }

    public function disputes()
    {
        require_once __DIR__ . '/../Models/Dispute.php';
        $disputeModel = new Dispute();
        $disputes = $disputeModel->getByUser(Auth::id());

        $this->view('user/disputes', [
            'disputes' => $disputes
        ]);
    }

    public function wallet()
    {
        $walletService = new WalletService();
        $depositRequestModel = new DepositRequest();
        $wallet = $walletService->getWallet(Auth::id());
        $transactions = $walletService->getTransactions(Auth::id(), 10);
        $depositRequests = $depositRequestModel->getUserRequests(Auth::id());
        $walletSettings = $this->getWalletSettings();

        $this->view('user/wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'depositRequests' => $depositRequests,
            'walletSettings' => $walletSettings
        ]);
    }

    public function initiateDeposit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
        }

        try {
            CSRF::check();
            $amount = (int) ($_POST['amount'] ?? 0);
            if ($amount < 50000 || $amount > 5000000) {
                $this->json(['success' => false, 'message' => 'Số tiền nạp phải từ 50.000đ đến 5.000.000đ'], 422);
            }

            $db = Database::getInstance();
            $userId = Auth::id();

            // Rate limit check
            $countLastHour = $db->fetchOne(
                "SELECT COUNT(*) as total FROM deposit_requests WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                [$userId]
            )['total'];

            if ($countLastHour >= 10) { // Nâng lên 10 cho thoải mái
                $this->json(['success' => false, 'message' => 'Bạn đã tạo quá nhiều yêu cầu nạp tiền. Vui lòng thử lại sau.'], 429);
            }

            $settings = $this->getWalletSettings();
            $sepayEnabled = ($db->fetchOne("SELECT value FROM settings WHERE key_name = 'sepay_enabled'")['value'] ?? '0') === '1';

            $transferCode = 'NAP' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT) . '_' . strtoupper(substr(md5(time() . $userId), 0, 4));
            $depositCode = 'DEP' . date('Ymd') . rand(1000, 9999);

            $depositRequestModel = new DepositRequest();
            $depositId = $depositRequestModel->create([
                'user_id' => $userId,
                'deposit_code' => $depositCode,
                'amount' => $amount,
                'transfer_code' => $transferCode,
                'bank_code' => trim($settings['deposit_bank_code'] ?? 'KienLongBank'),
                'bank_name' => trim($settings['deposit_bank_name'] ?? 'KienLongBank'),
                'account_name' => trim($settings['deposit_account_name'] ?? 'TRAN THANH TUAN'),
                'account_number' => trim($settings['deposit_account_number'] ?? '101499100004608842'),
                'status' => 'pending'
            ]);

            if ($sepayEnabled) {
                require_once __DIR__ . '/../Services/SePayService.php';
                $sepay = new SePayService();
                $checkout = $sepay->createCheckout([
                    'order_id' => $depositCode,
                    'amount' => $amount,
                    'description' => $transferCode,
                    'return_url' => url('/user/wallet'),
                    'cancel_url' => url('/user/wallet'),
                    // Webhook URL nên là URL tuyệt đối mà SePay có thể gọi tới
                    'webhook_url' => url('/webhook/sepay')
                ]);

                if ($checkout && isset($checkout['success']) && $checkout['success']) {
                    $this->json([
                        'success' => true,
                        'is_sepay' => true,
                        'checkout_url' => $checkout['data']['checkout_url'] ?? '',
                        'qr_url' => $checkout['data']['qr_url'] ?? '',
                        'transfer_code' => $transferCode,
                        'amount' => $amount
                    ]);
                    return;
                } else {
                    Logger::error('SePay Checkout Error: ' . json_encode($checkout));
                    // Fallback to manual if SePay fails
                }
            }

            // Standard VietQR or SePay fallback
            $bankCode = trim($settings['deposit_bank_code'] ?? 'KienLongBank');
            $accountNumber = trim($settings['deposit_account_number'] ?? '101499100004608842');
            $accountName = trim($settings['deposit_account_name'] ?? 'TRAN THANH TUAN');

            if (strtolower($bankCode) === 'kienlongbank' || strtolower($bankCode) === 'klb') {
                $qrUrl = "https://qr.sepay.vn/img?acc={$accountNumber}&bank=KienLongBank&amount={$amount}&des=" . urlencode($transferCode);
            } else {
                $qrUrl = "https://img.vietqr.io/image/{$bankCode}-{$accountNumber}-compact2.png?accountName=" . urlencode($accountName) . "&addInfo=" . urlencode($transferCode) . "&amount={$amount}";
            }

            $this->json([
                'success' => true,
                'is_sepay' => false,
                'qr_url' => $qrUrl,
                'transfer_code' => $transferCode,
                'amount' => $amount
            ]);

        } catch (Exception $e) {
            Logger::logException($e);
            $this->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function confirmDeposit()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Phương thức không hợp lệ'], 405);
        }

        try {
            Logger::activity('Yêu cầu nạp tiền: ' . ($_POST['amount'] ?? 0) . 'đ');
            CSRF::check();

            $amount = (int) ($_POST['amount'] ?? 0);
            if ($amount < 50000 || $amount > 5000000) {
                $this->json(['success' => false, 'message' => 'Số tiền nạp phải từ 50.000đ đến 5.000.000đ'], 422);
            }

            $db = Database::getInstance();
            $countLastHour = $db->fetchOne(
                "SELECT COUNT(*) as total FROM deposit_requests WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)",
                [Auth::id()]
            )['total'];

            if ($countLastHour >= 5) {
                $db->insert('spam_alerts', [
                    'user_id' => Auth::id(),
                    'type' => 'rate_limit_exceeded',
                    'description' => 'Người dùng vượt quá giới hạn nạp tiền (5 lần/giờ)'
                ]);
                $this->json(['success' => false, 'message' => 'Bạn đã nạp tối đa 5 lần trong vòng 1 tiếng. Vui lòng thử lại sau.'], 429);
            }

            $settings = $this->getWalletSettings();
            $botToken = trim($settings['wallet_telegram_bot_token'] ?? $settings['telegram_bot_token'] ?? '');
            $chatId = trim($settings['wallet_telegram_chat_id'] ?? $settings['telegram_chat_id'] ?? '');

            if ($botToken === '' || $chatId === '') {
                // Chúng ta vẫn cho phép tạo yêu cầu dù không có telegram
                // Logger::error('Telegram bot token hoặc chat id chưa được cấu hình cho việc nạp tiền');
            }

            $user = Auth::user();
            $transferContent = trim($_POST['transfer_code'] ?? '');
            if (empty($transferContent)) {
                $transferContent = 'NAP' . str_pad((string) Auth::id(), 4, '0', STR_PAD_LEFT) . '_' . strtoupper(substr(md5(time()), 0, 4));
            }
            $supportTelegram = trim($settings['wallet_telegram_support_username'] ?? $settings['telegram_support_username'] ?? '@specademy');
            $depositRequestModel = new DepositRequest();

            $depositCode = 'DEP' . date('Ymd') . rand(1000, 9999);

            $depositRequestId = $depositRequestModel->create([
                'user_id' => Auth::id(),
                'deposit_code' => $depositCode,
                'amount' => $amount,
                'transfer_code' => $transferContent,
                'bank_code' => trim($settings['deposit_bank_code'] ?? 'KienLongBank'),
                'bank_name' => trim($settings['deposit_bank_name'] ?? 'KienLongBank'),
                'account_name' => trim($settings['deposit_account_name'] ?? 'TRAN THANH TUAN'),
                'account_number' => trim($settings['deposit_account_number'] ?? '101499100004608842'),
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

            $this->json([
                'success' => true,
                'message' => 'Yêu cầu nạp tiền đã được tạo và đang chờ admin duyệt.'
            ]);

        } catch (Exception $e) {
            Logger::logException($e);
            $this->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    public function checkDepositStatus()
    {
        $transferCode = $_GET['code'] ?? '';
        if (empty($transferCode)) {
            $this->json(['success' => false, 'message' => 'Mã không hợp lệ'], 400);
        }

        $db = Database::getInstance();
        $deposit = $db->fetchOne(
            "SELECT status FROM deposit_requests WHERE user_id = ? AND transfer_code = ? ORDER BY id DESC LIMIT 1",
            [Auth::id(), $transferCode]
        );

        if ($deposit) {
            $this->json([
                'success' => true,
                'status' => $deposit['status']
            ]);
        } else {
            $this->json(['success' => false, 'message' => 'Không tìm thấy yêu cầu'], 404);
        }
    }
}
