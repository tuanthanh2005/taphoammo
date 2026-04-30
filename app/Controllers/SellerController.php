<?php
// app/Controllers/SellerController.php

class SellerController extends Controller {
    
    public function dashboard() {
        $db = Database::getInstance();
        $sellerId = Auth::id();
        
        // Get statistics
        $stats = [
            'total_products' => $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE seller_id = ?", [$sellerId])['total'],
            'total_orders' => $db->fetchOne("SELECT COUNT(DISTINCT order_id) as total FROM order_items WHERE seller_id = ?", [$sellerId])['total'],
            'total_revenue' => $db->fetchOne("SELECT SUM(seller_amount) as total FROM order_items WHERE seller_id = ?", [$sellerId])['total'] ?? 0
        ];
        
        // Get wallet
        $walletService = new WalletService();
        $wallet = $walletService->getWallet($sellerId);
        
        // Get recent orders
        $recentOrders = $db->fetchAll(
            "SELECT oi.*, o.order_code, o.created_at, p.name as product_name, u.name as buyer_name
             FROM order_items oi
             LEFT JOIN orders o ON oi.order_id = o.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON o.user_id = u.id
             WHERE oi.seller_id = ?
             ORDER BY o.created_at DESC
             LIMIT 10",
            [$sellerId]
        );
        
        // Get best selling products
        $bestProducts = $db->fetchAll(
            "SELECT p.*, SUM(oi.quantity) as total_sold
             FROM products p
             LEFT JOIN order_items oi ON p.id = oi.product_id
             WHERE p.seller_id = ?
             GROUP BY p.id
             ORDER BY total_sold DESC
             LIMIT 5",
            [$sellerId]
        );
        
        $this->view('seller/dashboard', [
            'stats' => $stats,
            'wallet' => $wallet,
            'recentOrders' => $recentOrders,
            'bestProducts' => $bestProducts
        ]);
    }
    
    public function products() {
        $productModel = new Product();
        $page = $_GET['page'] ?? 1;
        
        $products = $productModel->getAll(['seller_id' => Auth::id(), 'status' => 'all'], $page, 20);
        
        $this->view('seller/products', [
            'products' => $products,
            'currentPage' => $page
        ]);
    }
    
    public function createProduct() {
        $db = Database::getInstance();
        $totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = ?", [Auth::id()])['count'] ?? 0;
        
        if ($totalProducts >= 10) {
            Session::setFlash('error', 'Bạn đã đạt giới hạn 10 sản phẩm. Vui lòng liên hệ Admin qua Telegram hoặc Email để nâng cấp.');
            $this->redirect('/seller/products');
            return;
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getActive();
        
        $this->view('seller/product-create', ['categories' => $categories]);
    }
    
    public function storeProduct() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/products/create');
            return;
        }
        
        CSRF::check();
        
        $db = Database::getInstance();
        $totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = ?", [Auth::id()])['count'] ?? 0;
        
        if ($totalProducts >= 10) {
            Session::setFlash('error', 'Bạn đã đạt giới hạn 10 sản phẩm. Vui lòng liên hệ Admin qua Telegram hoặc Email để nâng cấp.');
            $this->redirect('/seller/products');
            return;
        }
        
        $name = $_POST['name'] ?? '';
        $categoryId = $_POST['category_id'] ?? 0;
        $shortDescription = $_POST['short_description'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $salePrice = $_POST['sale_price'] ?? null;
        $productType = $_POST['product_type'] ?? 'key';
        
        // Validation
        if (empty($name) || empty($categoryId) || empty($price)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            $this->redirect('/seller/products/create');
            return;
        }
        
        $data = [
            'seller_id' => Auth::id(),
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Helper::generateSlug($name) . '-' . time(),
            'short_description' => $shortDescription,
            'description' => $description,
            'price' => $price,
            'sale_price' => $salePrice ?: null,
            'product_type' => $productType,
            'require_note' => isset($_POST['require_note']) ? 1 : 0,
            'status' => 'pending',
            'stock_quantity' => 0
        ];
        
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['thumbnail'], 'products');
            if ($upload['success']) {
                $data['thumbnail'] = $upload['path'];
            }
        }
        
        $productModel = new Product();
        $productId = $productModel->create($data);
        
        if ($productId) {
            Session::setFlash('success', 'Tạo sản phẩm thành công! Vui lòng chờ admin duyệt.');
            $this->redirect('/seller/products/stock/' . $productId);
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra');
            $this->redirect('/seller/products/create');
        }
    }
    
    public function editProduct($id) {
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        
        $categoryModel = new Category();
        $categories = $categoryModel->getActive();
        
        $this->view('seller/product-edit', [
            'product' => $product,
            'categories' => $categories
        ]);
    }
    
    public function updateProduct($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/products');
            return;
        }
        
        CSRF::check();
        
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        
        $name = $_POST['name'] ?? '';
        $categoryId = $_POST['category_id'] ?? 0;
        $shortDescription = $_POST['short_description'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $salePrice = $_POST['sale_price'] ?? null;
        $status = $_POST['status'] ?? 'active';
        
        $data = [
            'category_id' => $categoryId,
            'name' => $name,
            'short_description' => $shortDescription,
            'description' => $description,
            'price' => $price,
            'sale_price' => $salePrice ?: null,
            'require_note' => isset($_POST['require_note']) ? 1 : 0,
            'status' => $status
        ];
        
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['thumbnail'], 'products');
            if ($upload['success']) {
                $data['thumbnail'] = $upload['path'];
            }
        }
        
        $productModel->update($id, $data);
        
        Session::setFlash('success', 'Cập nhật sản phẩm thành công');
        $this->redirect('/seller/products');
    }
    
    public function manageStock($id) {
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        
        $db = Database::getInstance();
        $stocks = $db->fetchAll(
            "SELECT * FROM product_stocks WHERE product_id = ? ORDER BY created_at DESC",
            [$id]
        );
        
        $this->view('seller/product-stock', [
            'product' => $product,
            'stocks' => $stocks
        ]);
    }
    
    public function importStock() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/products');
            return;
        }
        
        CSRF::check();
        
        $productId = $_POST['product_id'] ?? 0;
        $stockContent = $_POST['stock_content'] ?? '';
        
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        
        // Split by lines
        $lines = explode("\n", trim($stockContent));
        $stockCount = count(array_filter(array_map('trim', $lines)));
        
        if ($stockCount == 0) {
            Session::setFlash('error', 'Vui lòng nhập ít nhất 1 mã hàng');
            $this->redirect('/seller/products/stock/' . $productId);
            return;
        }
        
        // Kiểm tra hệ thống escrow có bật không
        $escrowService = new EscrowService();
        $escrowEnabled = $escrowService->isEscrowEnabled();
        $depositRequired = 0;
        
        if ($escrowEnabled) {
            $productPrice = $product['sale_price'] ?? $product['price'];
            
            // processStockDeposit = tính + kiểm tra + trừ tiền luôn
            $depositResult = $escrowService->processStockDeposit(
                Auth::id(),
                $productId,
                $stockCount,
                $productPrice
            );
            
            if (!$depositResult['success']) {
                Session::setFlash('error', $depositResult['message']);
                $this->redirect('/seller/products/stock/' . $productId);
                return;
            }
            
            $depositRequired = $depositResult['deposit_amount'];
        }
        
        // Nhập stock
        $imported = 0;
        $db = Database::getInstance();
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $db->insert('product_stocks', [
                    'product_id' => $productId,
                    'seller_id' => Auth::id(),
                    'content' => $line,
                    'status' => 'available'
                ]);
                $imported++;
            }
        }
        
        // Update product stock quantity
        $productModel->updateStock($productId);
        
        if ($escrowEnabled) {
            Session::setFlash('success', "Đã nhập {$imported} mã hàng thành công! Đã trừ " . money($depositRequired) . " tiền cọc từ ví.");
        } else {
            Session::setFlash('success', "Đã nhập {$imported} mã hàng thành công");
        }
        
        $this->redirect('/seller/products/stock/' . $productId);
    }
    
    public function orders() {
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $orders = $db->fetchAll(
            "SELECT oi.*, o.order_code, o.created_at, o.payment_status, 
                    p.name as product_name, u.name as buyer_name
             FROM order_items oi
             LEFT JOIN orders o ON oi.order_id = o.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON o.user_id = u.id
             WHERE oi.seller_id = ?
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            [Auth::id()]
        );
        
        $this->view('seller/orders', [
            'orders' => $orders,
            'currentPage' => $page
        ]);
    }
    
    public function orderDetail($orderId) {
        $db = Database::getInstance();
        $sellerId = Auth::id();
        
        // Kiểm tra đơn hàng có thuộc seller không
        $orderItem = $db->fetchOne(
            "SELECT oi.*, o.order_code, o.created_at, o.payment_status, o.order_status, o.total_amount,
                    p.name as product_name, p.thumbnail, p.product_type,
                    u.name as buyer_name, u.email as buyer_email, u.username as buyer_username
             FROM order_items oi
             LEFT JOIN orders o ON oi.order_id = o.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON o.user_id = u.id
             WHERE oi.order_id = ? AND oi.seller_id = ?
             LIMIT 1",
            [$orderId, $sellerId]
        );
        
        if (!$orderItem) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            $this->redirect('/seller/orders');
            return;
        }
        
        // Lấy danh sách stock đã giao cho đơn này
        $stocks = $db->fetchAll(
            "SELECT * FROM product_stocks WHERE order_id = ? AND product_id = ?",
            [$orderId, $orderItem['product_id']]
        );
        
        $this->view('seller/order-detail', [
            'order' => $orderItem,
            'stocks' => $stocks
        ]);
    }
    
    public function updateOrderStatus($orderId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/orders');
            return;
        }

        CSRF::check();

        $db = Database::getInstance();
        $sellerId = Auth::id();
        $status = $_POST['item_status'] ?? '';
        $note = trim($_POST['seller_note'] ?? '');

        $allowed = ['processing', 'delivered', 'issue', 'refunded'];
        if (!in_array($status, $allowed)) {
            Session::setFlash('error', 'Trạng thái không hợp lệ');
            $this->redirect('/seller/orders/' . $orderId);
            return;
        }

        // Kiểm tra đơn hàng thuộc seller
        $item = $db->fetchOne(
            "SELECT oi.id FROM order_items oi WHERE oi.order_id = ? AND oi.seller_id = ?",
            [$orderId, $sellerId]
        );

        if (!$item) {
            Session::setFlash('error', 'Không tìm thấy đơn hàng');
            $this->redirect('/seller/orders');
            return;
        }

        $db->query(
            "UPDATE order_items SET item_status = ?, seller_note = ?, status_updated_at = NOW() WHERE order_id = ? AND seller_id = ?",
            [$status, $note, $orderId, $sellerId]
        );

        Session::setFlash('success', 'Đã cập nhật trạng thái đơn hàng');
        $this->redirect('/seller/orders/' . $orderId);
    }

    public function wallet() {
        $walletService = new WalletService();
        $wallet = $walletService->getWallet(Auth::id());
        $transactions = $walletService->getTransactions(Auth::id(), 50);
        
        $this->view('seller/wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions
        ]);
    }
    
    public function requestDeposit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/wallet');
            return;
        }
        
        CSRF::check();
        
        $amount = (int)($_POST['amount'] ?? 0);
        $note = trim($_POST['note'] ?? '');
        
        if ($amount < 100000) {
            Session::setFlash('error', 'Số tiền nạp tối thiểu là 100,000 VNĐ. Không chấp nhận nạp dưới 100k.');
            $this->redirect('/seller/wallet');
            return;
        }
        
        $db = Database::getInstance();
        $userId = Auth::id();
        
        // Kiểm tra số lần nạp pending hiện tại
        $pendingCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM deposit_requests WHERE user_id = ? AND status = 'pending'",
            [$userId]
        );
        
        if ($pendingCount['cnt'] >= 5) {
            Session::setFlash('error', '⚠️ Bạn đang có ' . $pendingCount['cnt'] . ' yêu cầu nạp tiền chưa được duyệt. Tối đa 5 yêu cầu pending cùng lúc. Vui lòng chờ admin duyệt các yêu cầu hiện tại trước khi tạo thêm.');
            $this->redirect('/seller/wallet');
            return;
        }
        
        // Kiểm tra giới hạn 5 lần trong 2 tiếng
        $twoHoursAgo = date('Y-m-d H:i:s', strtotime('-2 hours'));
        $recentCount = $db->fetchOne(
            "SELECT COUNT(*) as cnt FROM deposit_requests WHERE user_id = ? AND created_at >= ?",
            [$userId, $twoHoursAgo]
        );
        
        if ($recentCount['cnt'] >= 5) {
            // Tìm thời điểm có thể nạp tiếp
            $oldestRecent = $db->fetchOne(
                "SELECT created_at FROM deposit_requests WHERE user_id = ? AND created_at >= ? ORDER BY created_at ASC LIMIT 1",
                [$userId, $twoHoursAgo]
            );
            $nextAllowed = date('H:i d/m/Y', strtotime($oldestRecent['created_at']) + 7200);
            
            Session::setFlash('error', '🚫 Bạn đã tạo 5 yêu cầu nạp tiền trong vòng 2 giờ qua. Để tránh spam, hệ thống giới hạn tối đa 5 lần nạp mỗi 2 tiếng. Bạn có thể nạp tiếp sau: ' . $nextAllowed);
            $this->redirect('/seller/wallet');
            return;
        }
        
        // Tạo yêu cầu nạp tiền
        $depositCode = 'DEP' . date('Ymd') . rand(1000, 9999);
        $transferCode = 'NAPSELLER';
        $remaining = 5 - ($recentCount['cnt'] + 1); // Số lần còn lại sau khi tạo
        
        $depositId = $db->insert('deposit_requests', [
            'user_id'        => $userId,
            'deposit_code'   => $depositCode,
            'transfer_code'  => $transferCode,
            'amount'         => $amount,
            'note'           => $note,
            'bank_code'      => 'mb',
            'bank_name'      => 'MB Bank',
            'account_name'   => 'TRAN THANH TUAN',
            'account_number' => '0783704196',
            'status'         => 'pending'
        ]);
        
        $msg = "✅ Đã gửi yêu cầu nạp " . money($amount) . ". Mã đơn: {$depositCode}. Vui lòng chuyển khoản MB Bank STK 0783704196 - TRAN THANH TUAN với nội dung: NAPSELLER";
        if ($remaining > 0) {
            $msg .= " (Còn {$remaining} lần nạp trong 2 tiếng này)";
        } else {
            $msg .= " (Đã dùng hết 5 lần, chờ 2 tiếng để nạp tiếp)";
        }
        
        Session::setFlash('success', $msg);
        $this->redirect('/seller/wallet');
    }
    
    public function requestDeactivation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/wallet');
            return;
        }
        
        CSRF::check();
        
        $reason = trim($_POST['reason'] ?? '');
        
        $deactivationService = new SellerDeactivationService();
        $result = $deactivationService->requestDeactivation(Auth::id(), $reason);
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        $this->redirect('/seller/wallet');
    }
    
    public function cancelDeactivation() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/wallet');
            return;
        }
        
        CSRF::check();
        
        $deactivationService = new SellerDeactivationService();
        $result = $deactivationService->cancelRequest(Auth::id());
        
        if ($result['success']) {
            Session::setFlash('success', $result['message']);
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        $this->redirect('/seller/wallet');
    }
    
    public function withdrawals() {
        $withdrawalModel = new Withdrawal();
        $withdrawals = $withdrawalModel->getUserWithdrawals(Auth::id());
        
        $walletService = new WalletService();
        $wallet = $walletService->getWallet(Auth::id());
        
        $db = Database::getInstance();
        $minAmountSetting = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'min_withdraw_amount'");
        $feePercentSetting = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'seller_withdraw_fee_percent'");
        
        $minAmount = $minAmountSetting ? (int)$minAmountSetting['value'] : 50000;
        $feePercent = $feePercentSetting ? (float)$feePercentSetting['value'] : 5;
        
        $this->view('seller/withdrawals', [
            'withdrawals' => $withdrawals,
            'wallet' => $wallet,
            'minAmount' => $minAmount,
            'feePercent' => $feePercent
        ]);
    }
    
    public function requestWithdrawal() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/withdrawals');
            return;
        }
        
        CSRF::check();
        
        $amount = $_POST['amount'] ?? 0;
        $method = $_POST['method'] ?? '';
        $accountInfo = $_POST['account_info'] ?? '';
        
        if (empty($amount) || empty($method) || empty($accountInfo)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            $this->redirect('/seller/withdrawals');
            return;
        }
        
        $withdrawalService = new WithdrawalService();
        $result = $withdrawalService->requestWithdrawal(Auth::id(), $amount, $method, $accountInfo);
        
        if ($result['success']) {
            Session::setFlash('success', 'Gửi yêu cầu rút tiền thành công! Vui lòng chờ admin duyệt.');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        $this->redirect('/seller/withdrawals');
    }
    
    public function updateTelegram() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/dashboard');
            return;
        }
        
        CSRF::check();
        
        $telegramChatId = trim($_POST['telegram_chat_id'] ?? '');
        $userId = Auth::id();
        
        $userModel = new User();
        $userModel->update($userId, ['telegram_chat_id' => $telegramChatId]);
        
        // Cập nhật session user
        $user = Auth::user();
        $user['telegram_chat_id'] = $telegramChatId;
        Session::set('user', $user);
        
        Session::setFlash('success', 'Đã cập nhật Telegram Chat ID thành công!');
        $this->redirect('/seller/dashboard');
    }
}
