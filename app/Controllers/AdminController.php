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
            'pending_withdrawals' => $db->fetchOne("SELECT COUNT(*) as total FROM withdrawals WHERE status = 'pending'")['total']
        ];
        
        // Get recent orders
        $recentOrders = $db->fetchAll(
            "SELECT o.*, u.name as user_name 
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT 10"
        );
        
        // Get pending withdrawals
        $pendingWithdrawals = $db->fetchAll(
            "SELECT w.*, u.name as user_name, u.email
             FROM withdrawals w
             LEFT JOIN users u ON w.user_id = u.id
             WHERE w.status = 'pending'
             ORDER BY w.created_at ASC
             LIMIT 10"
        );
        
        $this->view('admin/dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'pendingWithdrawals' => $pendingWithdrawals
        ]);
    }
    
    public function users() {
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        
        $where = ['1=1'];
        $params = [];
        
        if (!empty($search)) {
            $where[] = '(name LIKE :search1 OR email LIKE :search2 OR username LIKE :search3)';
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
            $params['search3'] = '%' . $search . '%';
        }
        
        if (!empty($role)) {
            $where[] = 'role = :role';
            $params['role'] = $role;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $users = $db->fetchAll(
            "SELECT * FROM users WHERE {$whereClause} ORDER BY created_at DESC LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->view('admin/users', [
            'users' => $users,
            'currentPage' => $page
        ]);
    }
    
    public function sellers() {
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $sellers = $db->fetchAll(
            "SELECT u.*, w.balance, w.total_earned, w.total_withdrawn,
                    (SELECT COUNT(*) FROM products WHERE seller_id = u.id) as total_products,
                    (SELECT COUNT(DISTINCT order_id) FROM order_items WHERE seller_id = u.id) as total_orders
             FROM users u
             LEFT JOIN wallets w ON u.id = w.user_id
             WHERE u.role = 'seller'
             ORDER BY u.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        
        $this->view('admin/sellers', [
            'sellers' => $sellers,
            'currentPage' => $page
        ]);
    }
    
    public function products() {
        $productModel = new Product();
        $page = $_GET['page'] ?? 1;
        $status = $_GET['status'] ?? '';
        
        $filters = [];
        if (!empty($status)) {
            $filters['status'] = $status;
        } else {
            $filters['status'] = 'all'; // By default admin should see everything
        }
        
        $products = $productModel->getAll($filters, $page, 50);
        
        $this->view('admin/products', [
            'products' => $products,
            'currentPage' => $page
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
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $orders = $db->fetchAll(
            "SELECT o.*, u.name as user_name, u.email as user_email
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        
        $this->view('admin/orders', [
            'orders' => $orders,
            'currentPage' => $page
        ]);
    }
    
    public function withdrawals() {
        $withdrawalModel = new Withdrawal();
        $page = $_GET['page'] ?? 1;
        
        $withdrawals = $withdrawalModel->getAllWithdrawals($page, 50);
        
        $this->view('admin/withdrawals', [
            'withdrawals' => $withdrawals,
            'currentPage' => $page
        ]);
    }

    public function deposits() {
        $depositRequestModel = new DepositRequest();
        $page = $_GET['page'] ?? 1;

        $depositRequests = $depositRequestModel->getAllRequests($page, 50);

        $this->view('admin/deposits', [
            'depositRequests' => $depositRequests,
            'currentPage' => $page
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
        
        Session::setFlash('success', 'Cập nhật cài đặt thành công');
        $this->redirect('/admin/settings');
    }
    
    public function transactions() {
        $db = Database::getInstance();
        $page = $_GET['page'] ?? 1;
        $perPage = 100;
        $offset = ($page - 1) * $perPage;
        
        $transactions = $db->fetchAll(
            "SELECT t.*, u.name as user_name, u.email as user_email
             FROM transactions t
             LEFT JOIN users u ON t.user_id = u.id
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}"
        );
        
        $this->view('admin/transactions', [
            'transactions' => $transactions,
            'currentPage' => $page
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
}
