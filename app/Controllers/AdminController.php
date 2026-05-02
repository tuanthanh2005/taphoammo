<?php
// app/Controllers/AdminController.php

class AdminController extends Controller {
    
    public function dashboard() {
        $db = Database::getInstance();
        
        // Get statistics
        $stats = [
            'total_users' => $db->fetchOne("SELECT COUNT(*) as total FROM users")['total'],
            'total_sellers' => $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE role = 'seller'")['total'],
            'total_products' => $db->fetchOne("SELECT COUNT(*) as total FROM products")['total'],
            'total_orders' => $db->fetchOne("SELECT COUNT(*) as total FROM orders")['total'],
            'total_revenue' => $db->fetchOne("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'")['total'] ?? 0,
            'admin_revenue' => $db->fetchOne("SELECT SUM(admin_fee_amount) as total FROM order_items")['total'] ?? 0,
            'pending_products' => $db->fetchOne("SELECT COUNT(*) as total FROM products WHERE status = 'pending'")['total'],
            'pending_withdrawals' => $db->fetchOne("SELECT COUNT(*) as total FROM withdrawals WHERE status = 'pending'")['total'],
            'pending_deposits' => $db->fetchOne("SELECT COUNT(*) as total FROM deposit_requests WHERE status = 'pending'")['total'],
            'open_disputes' => $db->fetchOne("SELECT COUNT(*) as total FROM disputes WHERE status IN ('open', 'under_review')")['total'],
            'revenue_today' => $db->fetchOne("SELECT SUM(admin_fee_amount) as total FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.payment_status = 'paid' AND DATE(o.created_at) = CURDATE()")['total'] ?? 0,
            'deposit_approved_today' => $db->fetchOne("SELECT SUM(amount) as total FROM deposit_requests WHERE status = 'approved' AND DATE(processed_at) = CURDATE()")['total'] ?? 0
        ];
        
        // Get recent orders (Last 10)
        $recentOrders = $db->fetchAll(
            "SELECT o.*, u.name as user_name 
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT 10"
        );
        
        // Get pending withdrawals (First 5)
        $pendingWithdrawals = $db->fetchAll(
            "SELECT w.*, u.name as user_name, u.email
             FROM withdrawals w
             LEFT JOIN users u ON w.user_id = u.id
             WHERE w.status = 'pending'
             ORDER BY w.created_at ASC
             LIMIT 5"
        );

        // Get open disputes
        $recentDisputes = $db->fetchAll(
            "SELECT d.*, u.name as buyer_name, p.name as product_name
             FROM disputes d
             LEFT JOIN users u ON d.user_id = u.id
             LEFT JOIN order_items oi ON d.order_item_id = oi.id
             LEFT JOIN products p ON oi.product_id = p.id
             WHERE d.status IN ('open', 'under_review')
             ORDER BY d.created_at DESC
             LIMIT 5"
        );

        // Get top 5 sellers by revenue
        $topSellers = $db->fetchAll(
            "SELECT u.name, u.email, SUM(oi.seller_amount) as total_revenue
             FROM order_items oi
             JOIN users u ON oi.seller_id = u.id
             GROUP BY oi.seller_id
             ORDER BY total_revenue DESC
             LIMIT 5"
        );
        
        $this->view('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'pendingWithdrawals' => $pendingWithdrawals,
            'recentDisputes' => $recentDisputes,
            'topSellers' => $topSellers
        ]);
    }
    
    public function users() {
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($search)) {
            $where[] = '(u.name LIKE :search1 OR u.email LIKE :search2 OR u.username LIKE :search3)';
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }
        
        if (!empty($role)) {
            $where[] = 'u.role = :role';
            $params['role'] = $role;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $users = $db->fetchAll(
            "SELECT u.*, COALESCE(w.balance, 0) as wallet_balance
             FROM users u
             LEFT JOIN wallets w ON w.user_id = u.id
             WHERE {$whereClause}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->view('admin/users', [
            'users' => $users,
            'currentPage' => $page,
            'totalUsers' => $db->fetchOne("SELECT COUNT(*) as total FROM users u WHERE {$whereClause}", $params)['total'],
            'pendingSellerRequests' => $db->fetchOne("SELECT COUNT(*) as total FROM users WHERE is_seller_requested = 1")['total']
        ]);
    }

    public function spamUsers() {
        $db = Database::getInstance();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $search = trim($_GET['search'] ?? '');
        $where = "1=1";
        $params = [];
        
        if (!empty($search)) {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Users with 3 or more spam alerts (of any type)
        $users = $db->fetchAll(
            "SELECT u.*, 
                    COUNT(s.id) as total_alerts,
                    MAX(s.created_at) as last_alert_at,
                    GROUP_CONCAT(DISTINCT s.type) as alert_types
             FROM users u
             JOIN spam_alerts s ON u.id = s.user_id
             WHERE {$where}
             GROUP BY u.id
             HAVING total_alerts >= 3
             ORDER BY total_alerts DESC, last_alert_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $totalCount = $db->fetchOne("
            SELECT COUNT(*) as total FROM (
                SELECT u.id, COUNT(s.id) as total_alerts
                FROM users u
                JOIN spam_alerts s ON u.id = s.user_id
                WHERE {$where}
                GROUP BY u.id
                HAVING total_alerts >= 3
            ) as t
        ", $params)['total'] ?? 0;

        $this->view('admin/spam_users', [
            'users' => $users,
            'currentPage' => $page,
            'totalPages' => ceil($totalCount / $perPage),
            'search' => $search
        ]);
    }

    public function approveSeller() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        CSRF::check();
        
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            Session::setFlash('error', 'Thiếu ID người dùng');
            $this->redirect('/admin/users');
            return;
        }

        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        
        if (!$user) {
            Session::setFlash('error', 'Người dùng không tồn tại');
            $this->redirect('/admin/users');
            return;
        }

        $db->update('users', [
            'role' => 'seller',
            'is_seller_requested' => 0,
            'max_products' => 10 // Giới hạn mặc định khi mới lên Seller
        ], 'id = :id', ['id' => $userId]);

        // Gửi thông báo trong app
        require_once __DIR__ . '/../Models/Notification.php';
        $notifModel = new Notification();
        $notifMsg = 'Chúc mừng! Bạn đã trở thành Nhà bán hàng. Bây giờ bạn có thể đăng bán sản phẩm tại trang Quản lý shop.';
        $notifModel->send($userId, 'Chúc mừng! Bạn đã trở thành Nhà bán hàng', $notifMsg, 'success');
        Helper::sendSystemMessage($userId, $notifMsg);

        // Gửi email thông báo
        $subject = "[{$user['name']}] Chúc mừng! Bạn đã trở thành Nhà bán hàng tại AI CỦA TÔI";
        $emailMsg = "<h3>Chào {$user['name']},</h3>";
        $emailMsg .= "<p>Yêu cầu trở thành Nhà bán hàng của bạn đã được Admin phê duyệt thành công.</p>";
        $emailMsg .= "<p>Bây giờ bạn có thể truy cập vào <b>Quản lý shop</b> để đăng sản phẩm và bắt đầu kinh doanh.</p>";
        $emailMsg .= "<p>Chúc bạn buôn may bán đắt!</p>";
        Helper::sendEmail($user['email'], $subject, $emailMsg);

        Session::setFlash('success', "Đã duyệt người dùng {$user['name']} lên làm Nhà bán hàng.");
        $this->redirect('/admin/users');
    }

    public function rejectSeller() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        CSRF::check();
        
        $userId = $_POST['user_id'] ?? null;
        if (!$userId) {
            Session::setFlash('error', 'Thiếu ID người dùng');
            $this->redirect('/admin/users');
            return;
        }

        $db = Database::getInstance();
        $db->update('users', ['is_seller_requested' => 0], 'id = :id', ['id' => $userId]);

        // Gửi thông báo trong app
        require_once __DIR__ . '/../Models/Notification.php';
        $notifModel = new Notification();
        $notifMsg = 'Rất tiếc, yêu cầu trở thành Nhà bán hàng của bạn chưa đủ điều kiện. Vui lòng liên hệ Admin để biết thêm chi tiết.';
        $notifModel->send($userId, 'Yêu cầu làm Nhà bán hàng bị từ chối', $notifMsg, 'danger');
        Helper::sendSystemMessage($userId, $notifMsg);

        // Gửi email thông báo
        $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$userId]);
        $subject = "[{$user['name']}] Thông báo về yêu cầu làm Nhà bán hàng";
        $emailMsg = "<h3>Chào {$user['name']},</h3>";
        $emailMsg .= "<p>Chúng tôi rất tiếc phải thông báo rằng yêu cầu trở thành Nhà bán hàng của bạn đã bị từ chối.</p>";
        $emailMsg .= "<p>Lý do có thể do thông tin chưa đầy đủ hoặc không phù hợp với quy định của sàn.</p>";
        $emailMsg .= "<p>Bạn có thể liên hệ trực tiếp với Admin qua Telegram để được hỗ trợ cụ thể hơn.</p>";
        Helper::sendEmail($user['email'], $subject, $emailMsg);

        Session::setFlash('success', "Đã từ chối yêu cầu làm Nhà bán hàng.");
        $this->redirect('/admin/users');
    }

    public function updateUserRole($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        CSRF::check();
        $role = $_POST['role'] ?? '';
        if (!in_array($role, ['user', 'seller', 'affiliate', 'admin'])) {
            Session::setFlash('error', 'Vai trò không hợp lệ');
            $this->redirect('/admin/users');
            return;
        }
        Database::getInstance()->update('users', ['role' => $role], 'id = :id', ['id' => $id]);
        Session::setFlash('success', 'Cập nhật vai trò thành công');
        $this->redirect('/admin/users');
    }

    public function resetUserPassword($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }
        CSRF::check();
        $newPassword = $_POST['password'] ?? '';
        if (strlen($newPassword) < 6) {
            Session::setFlash('error', 'Mật khẩu phải từ 6 ký tự trở lên');
            $this->redirect('/admin/users');
            return;
        }
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::getInstance()->update('users', ['password' => $hashedPassword], 'id = :id', ['id' => $id]);
        Session::setFlash('success', 'Đã đặt lại mật khẩu thành công cho người dùng');
        $this->redirect('/admin/users');
    }

    public function sellers() {
        $db = Database::getInstance();
        $search = trim($_GET['search'] ?? '');
        $page = (int)($_GET['page'] ?? 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $where = "u.role = 'seller'";
        $params = [];
        if ($search !== '') {
            $where .= " AND (u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        $sellers = $db->fetchAll(
            "SELECT u.*, w.balance, w.total_earned, w.total_withdrawn,
                    (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as total_products,
                    (SELECT COUNT(DISTINCT order_id) FROM order_items WHERE seller_id = u.id) as total_orders,
                    (SELECT COUNT(*) FROM order_items WHERE seller_id = u.id AND item_status NOT IN ('delivered', 'refunded', 'released')) as open_order_items
             FROM users u
             LEFT JOIN wallets w ON u.id = w.user_id
             WHERE {$where}
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->view('admin/sellers', [
            'sellers' => $sellers,
            'currentPage' => $page,
            'search' => $search
        ]);
    }
    
    public function updateSellerLimit($id) {
        CSRF::check();
        $db = Database::getInstance();
        $maxProducts = (int)($_POST['max_products'] ?? 10);
        $db->update('users', ['max_products' => $maxProducts], 'id = :id AND role = "seller"', ['id' => $id]);
        Session::setFlash('success', 'Đã cập nhật giới hạn sản phẩm cho seller.');
        $this->redirect('/admin/sellers');
    }

    public function toggleSellerStatus($id) {
        CSRF::check();

        require_once __DIR__ . '/../Services/AdminSellerService.php';
        $sellerService = new AdminSellerService();
        $summary = $sellerService->getSellerSummary($id);

        if (!$summary) {
            Session::setFlash('error', 'Khong tim thay seller.');
            $this->redirect('/admin/sellers');
            return;
        }

        if ($summary['status'] === 'active') {
            $result = $sellerService->banSellerIfSettled(Auth::id(), $id);
        } else {
            $result = $sellerService->restoreSeller(Auth::id(), $id);
        }

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/admin/sellers');
    }

    public function refundSellerOrdersAndBan($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/sellers');
            return;
        }

        CSRF::check();

        require_once __DIR__ . '/../Services/AdminSellerService.php';
        $sellerService = new AdminSellerService();
        $note = trim($_POST['admin_note'] ?? '');
        $result = $sellerService->refundOpenOrdersAndBanSeller(Auth::id(), $id, $note);

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/admin/sellers');
    }

    public function disputes() {
        require_once __DIR__ . '/../Models/Dispute.php';
        $disputeModel = new Dispute();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = trim($_GET['search'] ?? '');
        
        $disputes = $disputeModel->getAllDisputes($page, 10, $status, $search);
        $counts = $disputeModel->countByStatus();
        
        // Fetch events for these disputes to show timeline
        $disputeIds = array_column($disputes, 'id');
        $events = [];
        if (!empty($disputeIds)) {
            $placeholders = str_repeat('?,', count($disputeIds) - 1) . '?';
            $eventsRaw = Database::getInstance()->fetchAll(
                "SELECT de.*, u.name as actor_name 
                 FROM dispute_events de
                 LEFT JOIN users u ON de.actor_id = u.id
                 WHERE de.dispute_id IN ($placeholders)
                 ORDER BY de.created_at ASC",
                $disputeIds
            );
            foreach ($eventsRaw as $e) {
                $events[$e['dispute_id']][] = $e;
            }
        }
        
        $this->view('admin/disputes', [
            'disputes' => $disputes,
            'counts' => $counts,
            'currentPage' => $page,
            'currentStatus' => $status,
            'search' => $search,
            'events' => $events
        ]);
    }

    public function resolveDispute($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/disputes');
            return;
        }

        CSRF::check();
        
        $decision = $_POST['decision'] ?? '';
        $refundAmount = (float)($_POST['refund_amount'] ?? 0);
        $penaltyAmount = (float)($_POST['penalty_amount'] ?? 0);
        $adminNote = trim($_POST['admin_note'] ?? '');

        require_once __DIR__ . '/../Services/DisputeService.php';
        $disputeService = new DisputeService();
        $result = $disputeService->resolveDispute(Auth::id(), $id, $decision, $refundAmount, $penaltyAmount, $adminNote);

        if ($result['success']) {
            Session::setFlash('success', 'Đã xử lý khiếu nại.');
        } else {
            Session::setFlash('error', $result['message']);
        }

        $this->redirect('/admin/disputes');
    }
    
    public function products() {
        $productModel = new Product();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? 'all';
        if ($status === '') $status = 'all';
        $search = trim($_GET['search'] ?? '');
        
        $filters = [
            'status' => $status,
            'search' => $search
        ];
        
        $perPage = 10;
        $products = $productModel->getAll($filters, $page, $perPage);
        
        $this->view('admin/products', [
            'products' => $products,
            'currentPage' => $page,
            'currentStatus' => $status,
            'currentSearch' => $search
        ]);
    }
    
    public function approveProduct($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }
        
        CSRF::check();
        
        $productModel = new Product();
        $productModel->update($id, ['status' => 'approved']);
        
        Session::setFlash('success', 'Đã duyệt sản phẩm');
        $this->redirect('/admin/products');
    }
    
    public function rejectProduct($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }
        
        CSRF::check();
        
        $productModel = new Product();
        $productModel->update($id, ['status' => 'rejected']);
        
        Session::setFlash('success', 'Đã từ chối sản phẩm');
        $this->redirect('/admin/products');
    }
    
    public function orders() {
        $db = Database::getInstance();
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $where = "1=1";
        $params = [];
        if ($search !== '') {
            $where .= " AND (o.order_code LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Thống kê tổng quan (giữ nguyên không đổi theo filter tìm kiếm để admin biết tổng sàn)
        $stats = $db->fetchOne("
            SELECT 
                COUNT(*) as total_orders,
                SUM(total_amount) as total_revenue,
                (SELECT SUM(admin_fee_amount) FROM order_items) as total_admin_fees
            FROM orders 
            WHERE payment_status = 'paid'
        ");

        // Đếm tổng số để phân trang
        $totalCount = $db->fetchOne("
            SELECT COUNT(*) as count 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE {$where}
        ", $params)['count'] ?? 0;
        $totalPages = ceil($totalCount / $perPage);

        // Danh sách đơn hàng kèm phí admin sum
        $orders = $db->fetchAll(
            "SELECT o.*, u.name as user_name, u.email as user_email,
                    (SELECT SUM(admin_fee_amount) FROM order_items WHERE order_id = o.id) as admin_fee
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE {$where}
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->view('admin/orders', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'stats' => $stats
        ]);
    }
    
    public function withdrawals() {
        $withdrawalModel = new Withdrawal();
        $page = $_GET['page'] ?? 1;
        $search = trim($_GET['search'] ?? '');
        
        $withdrawals = $withdrawalModel->getAllWithdrawals($page, 10, $search);
        
        $this->view('admin/withdrawals', [
            'withdrawals' => $withdrawals,
            'currentPage' => $page,
            'search' => $search
        ]);
    }

    public function deposits() {
        $db = Database::getInstance();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        
        // Thống kê nạp tiền
        $stats = $db->fetchOne("
            SELECT 
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
                SUM(CASE WHEN status = 'approved' AND DATE(processed_at) = CURDATE() THEN amount ELSE 0 END) as approved_today
            FROM deposit_requests
        ");

        $depositRequestModel = new DepositRequest();
        $depositRequests = $depositRequestModel->getAllRequests($page, $perPage);

        $this->view('admin/deposits', [
            'depositRequests' => $depositRequests,
            'currentPage' => $page,
            'stats' => $stats
        ]);
    }

    public function approveDeposit($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/deposits');
            return;
        }

        CSRF::check();

        $depositRequestModel = new DepositRequest();
        $walletService = new WalletService();
        $deposit = $depositRequestModel->find($id);

        if (!$deposit || $deposit['status'] !== 'pending') {
            Session::setFlash('error', 'Yêu cầu nạp tiền không tồn tại hoặc đã được xử lý');
            $this->redirect('/admin/deposits');
            return;
        }

        $walletService->addMoney(
            $deposit['user_id'],
            (float)$deposit['amount'],
            'deposit',
            'deposit_request',
            $deposit['id'],
            'Nạp tiền được admin duyệt'
        );

        $depositRequestModel->update($id, [
            'status' => 'approved',
            'admin_note' => trim($_POST['admin_note'] ?? 'Đã duyệt nạp tiền'),
            'processed_by' => Auth::id(),
            'processed_at' => date('Y-m-d H:i:s')
        ]);

        Session::setFlash('success', 'Đã duyệt yêu cầu nạp tiền');
        $this->redirect('/admin/deposits');
    }

    public function rejectDeposit($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/deposits');
            return;
        }

        CSRF::check();

        $depositRequestModel = new DepositRequest();
        $deposit = $depositRequestModel->find($id);

        if (!$deposit || $deposit['status'] !== 'pending') {
            Session::setFlash('error', 'Yêu cầu nạp tiền không tồn tại hoặc đã được xử lý');
            $this->redirect('/admin/deposits');
            return;
        }

        $depositRequestModel->update($id, [
            'status' => 'rejected',
            'admin_note' => trim($_POST['reason'] ?? 'Từ chối yêu cầu nạp tiền'),
            'processed_by' => Auth::id(),
            'processed_at' => date('Y-m-d H:i:s')
        ]);

        $db = Database::getInstance();
        $db->insert('spam_alerts', [
            'user_id' => $deposit['user_id'],
            'type' => 'deposit_rejected',
            'description' => 'Yêu cầu nạp tiền bị từ chối: ' . (trim($_POST['reason'] ?? 'Từ chối yêu cầu nạp tiền'))
        ]);

        Session::setFlash('success', 'Đã từ chối yêu cầu nạp tiền');
        $this->redirect('/admin/deposits');
    }
    
    public function approveWithdrawal($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/withdrawals');
            return;
        }
        
        CSRF::check();
        
        $withdrawalService = new WithdrawalService();
        $result = $withdrawalService->approveWithdrawal($id, Auth::id());
        
        if ($result['success']) {
            Session::setFlash('success', 'Đã duyệt yêu cầu rút tiền');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        $this->redirect('/admin/withdrawals');
    }
    
    public function rejectWithdrawal($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/withdrawals');
            return;
        }
        
        CSRF::check();
        
        $reason = $_POST['reason'] ?? 'Không đủ điều kiện';
        
        $withdrawalService = new WithdrawalService();
        $result = $withdrawalService->rejectWithdrawal($id, Auth::id(), $reason);
        
        if ($result['success']) {
            Session::setFlash('success', 'Đã từ chối yêu cầu rút tiền');
        } else {
            Session::setFlash('error', $result['message']);
        }
        
        $this->redirect('/admin/withdrawals');
    }
    
    public function categories() {
        $categoryModel = new Category();
        $categories = $categoryModel->all('display_order ASC, name ASC');
        
        $this->view('admin/categories', ['categories' => $categories]);
    }
    
    public function storeCategory() {
        CSRF::check();
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-folder');
        $display_order = (int)($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            Session::setFlash('error', 'Tên danh mục không được để trống');
            $this->redirect('/admin/categories');
            return;
        }

        if (empty($slug)) {
            $slug = Helper::slugify($name);
        }

        $categoryModel = new Category();
        $categoryModel->create([
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'display_order' => $display_order,
            'status' => $status
        ]);
        
        Session::setFlash('success', 'Đã thêm danh mục mới');
        $this->redirect('/admin/categories');
    }

    public function updateCategory($id) {
        CSRF::check();
        $name = trim($_POST['name'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fas fa-folder');
        $display_order = (int)($_POST['display_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (empty($name)) {
            Session::setFlash('error', 'Tên danh mục không được để trống');
            $this->redirect('/admin/categories');
            return;
        }

        $categoryModel = new Category();
        $categoryModel->update($id, [
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'display_order' => $display_order,
            'status' => $status
        ]);
        
        Session::setFlash('success', 'Đã cập nhật danh mục');
        $this->redirect('/admin/categories');
    }

    public function deleteCategory($id) {
        CSRF::check();
        $categoryModel = new Category();
        
        // Kiểm tra xem có sản phẩm thuộc danh mục này không
        $db = Database::getInstance();
        $hasProducts = $db->fetchOne("SELECT id FROM products WHERE category_id = ?", [$id]);
        if ($hasProducts) {
            Session::setFlash('error', 'Không thể xóa danh mục đang có sản phẩm');
            $this->redirect('/admin/categories');
            return;
        }
        
        $categoryModel->delete($id);
        Session::setFlash('success', 'Đã xóa danh mục');
        $this->redirect('/admin/categories');
    }

    public function toggleUserStatus($id) {
        CSRF::check();
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT status FROM users WHERE id = ?", [$id]);
        if (!$user) {
            $this->redirect('/admin/users');
            return;
        }
        
        $newStatus = $user['status'] === 'active' ? 'banned' : 'active';
        $db->update('users', ['status' => $newStatus], "id = :id", ['id' => $id]);
        
        Session::setFlash('success', 'Đã thay đổi trạng thái người dùng');
        // Trở về trang trước đó (ví dụ từ /admin/users hoặc /admin/sellers)
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    public function orderDetail($id) {
        $db = Database::getInstance();
        $order = $db->fetchOne(
            "SELECT o.*, u.name as user_name, u.email as user_email
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE o.id = ?",
            [$id]
        );
        
        if (!$order) {
            $this->redirect('/admin/orders');
            return;
        }
        
        $items = $db->fetchAll(
            "SELECT oi.*, p.name as product_name, s.name as seller_name
             FROM order_items oi
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users s ON oi.seller_id = s.id
             WHERE oi.order_id = ?",
            [$id]
        );
        
        $this->view('admin/order_detail', [
            'order' => $order,
            'items' => $items
        ]);
    }
    
    public function settings() {
        $db = Database::getInstance();
        $settings = $db->fetchAll("SELECT * FROM settings");
        
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['key_name']] = $setting['value'];
        }
        
        $this->view('admin/settings', ['settings' => $settingsArray]);
    }
    
    public function updateSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/settings');
            return;
        }
        
        CSRF::check();
        
        $db = Database::getInstance();
        
        foreach ($_POST as $key => $value) {
            if ($key !== 'csrf_token' && $key !== 'remove_home_hero_bg') {
                $existing = $db->fetchOne("SELECT * FROM settings WHERE key_name = ?", [$key]);
                
                if ($existing) {
                    $db->update('settings', ['value' => $value], 'key_name = :key_name', ['key_name' => $key]);
                } else {
                    $db->insert('settings', ['key_name' => $key, 'value' => $value]);
                }
            }
        }

        // Xử lý upload ảnh nền Hero
        if (isset($_POST['remove_home_hero_bg']) && $_POST['remove_home_hero_bg'] == '1') {
            $db->update('settings', ['value' => ''], 'key_name = :key_name', ['key_name' => 'home_hero_bg']);
        } elseif (isset($_FILES['home_hero_bg_file']) && $_FILES['home_hero_bg_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Helper::uploadFile($_FILES['home_hero_bg_file'], 'images');
            if (is_array($uploadResult) && $uploadResult['success'] && !empty($uploadResult['path'])) {
                $heroBgPath = $uploadResult['path'];
                $existingHero = $db->fetchOne("SELECT * FROM settings WHERE key_name = 'home_hero_bg'");
                if ($existingHero) {
                    $db->update('settings', ['value' => $heroBgPath], 'key_name = :key_name', ['key_name' => 'home_hero_bg']);
                } else {
                    $db->insert('settings', ['key_name' => 'home_hero_bg', 'value' => $heroBgPath]);
                }
            }
        }
        
        // Xử lý upload Banner Trái
        if (isset($_FILES['home_banner_left_file']) && $_FILES['home_banner_left_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Helper::uploadFile($_FILES['home_banner_left_file'], 'images');
            if ($uploadResult['success']) {
                $path = $uploadResult['path']; // Lưu đường dẫn tương đối
                $db->fetchOne("SELECT * FROM settings WHERE key_name = 'home_banner_left'") 
                    ? $db->update('settings', ['value' => $path], 'key_name = :key_name', ['key_name' => 'home_banner_left'])
                    : $db->insert('settings', ['key_name' => 'home_banner_left', 'value' => $path]);
            }
        }

        // Xử lý upload Banner Phải
        if (isset($_FILES['home_banner_right_file']) && $_FILES['home_banner_right_file']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = Helper::uploadFile($_FILES['home_banner_right_file'], 'images');
            if ($uploadResult['success']) {
                $path = $uploadResult['path']; // Lưu đường dẫn tương đối
                $db->fetchOne("SELECT * FROM settings WHERE key_name = 'home_banner_right'")
                    ? $db->update('settings', ['value' => $path], 'key_name = :key_name', ['key_name' => 'home_banner_right'])
                    : $db->insert('settings', ['key_name' => 'home_banner_right', 'value' => $path]);
            }
        }
        
        Session::setFlash('success', 'Cập nhật cài đặt thành công');
        $this->redirect('/admin/settings');
    }
    
    public function transactions() {
        $db = Database::getInstance();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $search = trim($_GET['search'] ?? '');
        $type = $_GET['type'] ?? 'all';
        
        $where = ['1=1'];
        $params = [];
        
        if ($type !== 'all') {
            $where[] = "t.type = :type";
            $params['type'] = $type;
        }
        
        if (!empty($search)) {
            $where[] = "(u.name LIKE :search OR u.email LIKE :search OR t.description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $transactions = $db->fetchAll(
            "SELECT t.*, u.name as user_name, u.email as user_email
             FROM transactions t
             LEFT JOIN users u ON t.user_id = u.id
             WHERE $whereClause
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $total = $db->fetchOne("SELECT COUNT(*) as total FROM transactions t LEFT JOIN users u ON t.user_id = u.id WHERE $whereClause", $params)['total'];
        $totalPages = ceil($total / $perPage);
        
        $this->view('admin/transactions', [
            'transactions' => $transactions,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentType' => $type,
            'currentSearch' => $search
        ]);
    }
    public function menus() {
        $menuModel = new Menu();
        $menus = $menuModel->getAllMenus();
        $parents = $menuModel->getParents();
        
        $this->view('admin/menus', [
            'menus' => $menus,
            'parents' => $parents
        ]);
    }
    
    public function storeMenu() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $menuModel = new Menu();
        $data = [
            'title' => $_POST['title'],
            'url' => $_POST['url'] ?: '#',
            'icon' => $_POST['icon'] ?: null,
            'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'display_order' => (int)$_POST['display_order'],
            'status' => $_POST['status'] ?? 'active'
        ];
        
        $menuModel->create($data);
        Session::setFlash('success', 'Thêm menu thành công');
        $this->redirect('/admin/menus');
    }
    
    public function updateMenu($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $menuModel = new Menu();
        $data = [
            'title' => $_POST['title'],
            'url' => $_POST['url'] ?: '#',
            'icon' => $_POST['icon'] ?: null,
            'parent_id' => !empty($_POST['parent_id']) ? $_POST['parent_id'] : null,
            'display_order' => (int)$_POST['display_order'],
            'status' => $_POST['status'] ?? 'active'
        ];
        
        $menuModel->update($id, $data);
        Session::setFlash('success', 'Cập nhật menu thành công');
        $this->redirect('/admin/menus');
    }
    
    public function deleteMenu($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $menuModel = new Menu();
        $menuModel->delete($id);
        Session::setFlash('success', 'Xóa menu thành công');
        $this->redirect('/admin/menus');
    }

    public function errorLogs() {
        $db = Database::getInstance();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $search = trim($_GET['search'] ?? '');
        $where = ['1=1'];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(e.error_message LIKE :search OR e.file LIKE :search OR e.url LIKE :search OR u.name LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $whereClause = implode(' AND ', $where);
        
        $logs = $db->fetchAll(
            "SELECT e.*, u.name as user_name 
             FROM error_logs e
             LEFT JOIN users u ON e.user_id = u.id
             WHERE $whereClause
             ORDER BY e.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $total = $db->fetchOne("SELECT COUNT(*) as total FROM error_logs e LEFT JOIN users u ON e.user_id = u.id WHERE $whereClause", $params)['total'];
        $totalPages = ceil($total / $perPage);
        
        $this->view('admin/error-logs', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'currentSearch' => $search
        ]);
    }
}
