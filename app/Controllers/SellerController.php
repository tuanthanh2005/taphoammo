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

    public function profile() {
        $userModel = new User();
        $user = $userModel->find(Auth::id());
        $this->view('seller/profile', ['user' => $user]);
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/profile');
            return;
        }

        CSRF::check();

        $userId = Auth::id();
        $user = Auth::user();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];

        if ($data['name'] === '') {
            Session::setFlash('error', 'Vui lòng nhập họ tên.');
            $this->redirect('/seller/profile');
            return;
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['avatar'], 'avatars');
            if ($upload['success']) {
                $data['avatar'] = $upload['path'];
            } else {
                Session::setFlash('error', $upload['message']);
                $this->redirect('/seller/profile');
                return;
            }
        }

        if (!empty($_POST['new_password'])) {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($user['password']) || !password_verify($currentPassword, $user['password'])) {
                Session::setFlash('error', 'Mật khẩu hiện tại không đúng.');
                $this->redirect('/seller/profile');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                Session::setFlash('error', 'Mật khẩu xác nhận không khớp.');
                $this->redirect('/seller/profile');
                return;
            }

            if (strlen($newPassword) < 6) {
                Session::setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự.');
                $this->redirect('/seller/profile');
                return;
            }

            $data['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $userModel = new User();
        $userModel->update($userId, $data);

        // Update session name if changed
        if (isset($data['name'])) {
            $_SESSION['user_name'] = $data['name'];
        }

        Session::setFlash('success', 'Cập nhật hồ sơ seller thành công.');
        $this->redirect('/seller/profile');
    }
    
    public function products() {
        $productModel = new Product();
        $page = (int)($_GET['page'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        
        $filters = ['seller_id' => Auth::id(), 'status' => 'all'];
        if ($search !== '') {
            $filters['search'] = $search;
        }

        $products = $productModel->getAll($filters, $page, 10);
        
        $this->view('seller/products', [
            'products' => $products,
            'currentPage' => $page,
            'search' => $search
        ]);
    }
    
    public function createProduct() {
        $db = Database::getInstance();
        $user = $db->fetchOne("SELECT max_products FROM users WHERE id = ?", [Auth::id()]);
        $maxProducts = $user['max_products'] ?? 10;
        $totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = ?", [Auth::id()])['count'] ?? 0;
        
        if ($totalProducts >= $maxProducts) {
            Session::setFlash('error', "Bạn đã đạt giới hạn {$maxProducts} sản phẩm. Vui lòng liên hệ Admin để nâng cấp.");
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
        $user = $db->fetchOne("SELECT max_products FROM users WHERE id = ?", [Auth::id()]);
        $maxProducts = $user['max_products'] ?? 10;
        $totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE seller_id = ?", [Auth::id()])['count'] ?? 0;
        
        if ($totalProducts >= $maxProducts) {
            Session::setFlash('error', "Bạn đã đạt giới hạn {$maxProducts} sản phẩm. Vui lòng liên hệ Admin để nâng cấp.");
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
        $warrantyDays = max(0, (int)($_POST['warranty_days'] ?? 0));
        $warrantyNote = trim($_POST['warranty_note'] ?? '');
        $categoryModel = new Category();
        $category = $categoryModel->find($categoryId);
        $profile = Helper::getCategoryProductProfile($category ?: []);
        if (empty($productType) || !in_array($productType, $profile['allowed_product_types'], true)) {
            $productType = $profile['suggested_product_type'];
        }
        
        // Validation
        $hasVariants = isset($_POST['has_variants']);
        if (empty($name) || empty($categoryId) || (!$hasVariants && empty($price))) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            $this->redirect('/seller/products/create');
            return;
        }
        
        if ($warrantyNote === '') {
            $warrantyNote = $warrantyDays > 0 ? "Bao hanh {$warrantyDays} ngay" : 'Khong bao hanh';
        }

        $data = [
            'seller_id' => Auth::id(),
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => Helper::generateSlug($name) . '-' . time(),
            'short_description' => $shortDescription,
            'description' => $description,
            'price' => !empty($price) ? $price : 0,
            'sale_price' => $salePrice ?: null,
            'display_price' => isset($_POST['display_price']) ? $_POST['display_price'] : null,
            'product_type' => $productType,
            'warranty_days' => $warrantyDays,
            'warranty_note' => $warrantyNote,
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
            // Handle Variants
            if (isset($_POST['has_variants']) && !empty($_POST['variants'])) {
                $db = Database::getInstance();
                foreach ($_POST['variants'] as $variant) {
                    if (empty($variant['name']) || empty($variant['price'])) continue;
                    
                    $vId = $db->insert('product_variants', [
                        'product_id'     => $productId,
                        'name'           => $variant['name'],
                        'price'          => $variant['price'],
                        'sale_price'     => !empty($variant['sale_price']) ? $variant['sale_price'] : null,
                        'require_note'   => isset($variant['require_note']) ? 1 : 0,
                        'stock_quantity' => 0,
                        'status'         => 'active'
                    ]);

                    // Handle Stock Addition for new variants
                    $stockAdd = !empty($variant['stock_add']) ? (int)$variant['stock_add'] : 0;
                    if ($vId && $stockAdd > 0) {
                        $stockContent = !empty($variant['stock_content']) ? $variant['stock_content'] : 'Bàn giao thủ công';
                        for ($i = 0; $i < $stockAdd; $i++) {
                            $db->insert('product_stocks', [
                                'product_id' => $productId,
                                'variant_id' => $vId,
                                'seller_id'  => Auth::id(),
                                'content'    => $stockContent,
                                'status'     => 'available'
                            ]);
                        }
                    }
                }
                
                if (!empty($_POST['variants'][0]['price'])) {
                    $firstPrice = $_POST['variants'][0]['price'];
                    $firstSale = !empty($_POST['variants'][0]['sale_price']) ? $_POST['variants'][0]['sale_price'] : null;
                    $productModel->update($productId, ['price' => $firstPrice, 'sale_price' => $firstSale]);
                }
            } else {
                // Handle Main Stock Addition (Quick Import)
                $mainStockAdd = !empty($_POST['main_stock_add']) ? (int)$_POST['main_stock_add'] : 0;
                if ($mainStockAdd > 0) {
                    $mainStockContent = !empty($_POST['main_stock_content']) ? $_POST['main_stock_content'] : 'Bàn giao thủ công';
                    for ($i = 0; $i < $mainStockAdd; $i++) {
                        $db->insert('product_stocks', [
                            'product_id' => $productId,
                            'variant_id' => null,
                            'seller_id'  => Auth::id(),
                            'content'    => $mainStockContent,
                            'status'     => 'available'
                        ]);
                    }
                }
            }
            
            // Sync stock counts
            $productModel->updateStock($productId);

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
        $selectedCategory = $categoryModel->find($product['category_id']);
        
        $db = Database::getInstance();
        $variants = $db->fetchAll("SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC", [$id]);
        
        $this->view('seller/product-edit', [
            'product' => $product,
            'categories' => $categories,
            'variants' => $variants,
            'categoryProfile' => Helper::getCategoryProductProfile($selectedCategory ?: [])
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
        $productType = $_POST['product_type'] ?? ($product['product_type'] ?? 'key');
        $warrantyDays = max(0, (int)($_POST['warranty_days'] ?? 0));
        $warrantyNote = trim($_POST['warranty_note'] ?? '');
        $categoryModel = new Category();
        $category = $categoryModel->find($categoryId);
        $profile = Helper::getCategoryProductProfile($category ?: []);
        if (empty($productType) || !in_array($productType, $profile['allowed_product_types'], true)) {
            $productType = $profile['suggested_product_type'];
        }
        
        $data = [
            'category_id' => $categoryId,
            'name' => $name,
            'short_description' => $shortDescription,
            'description' => $description,
            'price' => $price,
            'sale_price' => $salePrice ?: null,
            'display_price' => $_POST['display_price'] ?? null,
            'product_type' => $productType,
            'warranty_days' => $warrantyDays,
            'warranty_note' => $warrantyNote !== '' ? $warrantyNote : ($warrantyDays > 0 ? "Bao hanh {$warrantyDays} ngay" : 'Khong bao hanh'),
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
        
        // Handle Variants
        $db = Database::getInstance();
        if (isset($_POST['has_variants']) && !empty($_POST['variants'])) {
            $postedVariantIds = [];
            foreach ($_POST['variants'] as $v) {
                if (empty($v['name'])) continue;
                
                $variantData = [
                    'product_id'   => $id,
                    'name'         => $v['name'],
                    'price'        => $v['price'],
                    'sale_price'   => !empty($v['sale_price']) ? $v['sale_price'] : null,
                    'require_note' => isset($v['require_note']) ? 1 : 0
                ];
                
                $vId = null;
                if (!empty($v['id'])) {
                    $vId = (int)$v['id'];
                    $db->update('product_variants', $variantData, "id = :id", ['id' => $vId]);
                    $postedVariantIds[] = $vId;
                } else {
                    $vId = $db->insert('product_variants', array_merge($variantData, [
                        'stock_quantity' => 0,
                        'status'         => 'active'
                    ]));
                    if ($vId) $postedVariantIds[] = $vId;
                }

                // Handle Stock Addition
                $stockAdd = !empty($v['stock_add']) ? (int)$v['stock_add'] : 0;
                if ($vId && $stockAdd > 0) {
                    $stockContent = !empty($v['stock_content']) ? $v['stock_content'] : 'Bàn giao thủ công';
                    for ($i = 0; $i < $stockAdd; $i++) {
                        $db->insert('product_stocks', [
                            'product_id' => $id,
                            'variant_id' => $vId,
                            'seller_id'  => Auth::id(),
                            'content'    => $stockContent,
                            'status'     => 'available'
                        ]);
                    }
                }
            }
            
            // Delete removed variants
            if (!empty($postedVariantIds)) {
                $placeholders = implode(',', array_fill(0, count($postedVariantIds), '?'));
                $params = array_merge([$id], $postedVariantIds);
                $db->query("DELETE FROM product_variants WHERE product_id = ? AND id NOT IN ($placeholders)", $params);
            } else {
                $db->query("DELETE FROM product_variants WHERE product_id = ?", [$id]);
            }

            // Sync main price with first variant
            if (!empty($_POST['variants'][0]['price'])) {
                $firstPrice = $_POST['variants'][0]['price'];
                $firstSale = !empty($_POST['variants'][0]['sale_price']) ? $_POST['variants'][0]['sale_price'] : null;
                $productModel->update($id, ['price' => $firstPrice, 'sale_price' => $firstSale]);
            }
        } else {
            $db->query("DELETE FROM product_variants WHERE product_id = ?", [$id]);

            // Handle Main Stock Addition (Quick Import)
            $mainStockAdd = !empty($_POST['main_stock_add']) ? (int)$_POST['main_stock_add'] : 0;
            if ($mainStockAdd > 0) {
                $mainStockContent = !empty($_POST['main_stock_content']) ? $_POST['main_stock_content'] : 'Bàn giao thủ công';
                for ($i = 0; $i < $mainStockAdd; $i++) {
                    $db->insert('product_stocks', [
                        'product_id' => $id,
                        'variant_id' => null,
                        'seller_id'  => Auth::id(),
                        'content'    => $mainStockContent,
                        'status'     => 'available'
                    ]);
                }
            }
        }

        $productModel->updateStock($id);
        
        Session::setFlash('success', 'Cập nhật sản phẩm thành công');
        $this->redirect('/seller/products');
    }
    
    public function deleteProduct($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/products');
            return;
        }

        CSRF::check();
        
        $db = Database::getInstance();
        $sellerId = Auth::id();

        $product = $db->fetchOne("SELECT * FROM products WHERE id = ? AND seller_id = ?", [$id, $sellerId]);
        if (!$product) {
            Session::setFlash('error', 'Sản phẩm không tồn tại.');
            $this->redirect('/seller/products');
            return;
        }

        // 1. Kiểm tra xem có đơn hàng nào đang trong trạng thái xử lý hoặc khiếu nại không
        $pendingOrders = $db->fetchAll("
            SELECT o.order_code, u.name as buyer_name, oi.item_status
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN users u ON o.user_id = u.id
            LEFT JOIN disputes d ON oi.id = d.order_item_id
            WHERE oi.product_id = ? 
            AND (oi.item_status = 'processing' OR (d.id IS NOT NULL AND d.status IN ('open', 'under_review')))
            GROUP BY o.order_code, u.name, oi.item_status
        ", [$id]);

        if (!empty($pendingOrders)) {
            $orderList = [];
            foreach ($pendingOrders as $po) {
                $statusText = ($po['item_status'] === 'processing') ? 'Đang xử lý' : 'Đang khiếu nại';
                $orderList[] = "#" . $po['order_code'] . " (" . $po['buyer_name'] . " - " . $statusText . ")";
            }
            $msg = "Không thể xóa/ẩn sản phẩm này vì còn các đơn hàng chưa hoàn tất: " . implode(', ', $orderList) . ". Vui lòng xử lý xong trước khi xóa.";
            Session::setFlash('error', $msg);
            $this->redirect('/seller/products');
            return;
        }

        // 2. Kiểm tra lịch sử đơn hàng cũ (đã hoàn thành)
        $orderCount = $db->fetchOne("SELECT COUNT(*) as total FROM order_items WHERE product_id = ?", [$id])['total'];
        
        if ($orderCount > 0) {
            // Nếu đã có đơn hàng cũ, chỉ đổi trạng thái sang 'deleted' để ẩn hoàn toàn khỏi Seller
            $db->update('products', ['status' => 'deleted'], 'id = :id', ['id' => $id]);
            Session::setFlash('success', 'Sản phẩm đã có lịch sử đơn hàng nên hệ thống đã đánh dấu Xóa và ẩn khỏi danh sách của bạn.');
        } else {
            // Nếu chưa có đơn hàng nào, xóa sạch 100%
            $db->query("DELETE FROM product_stocks WHERE product_id = ?", [$id]);
            $db->query("DELETE FROM products WHERE id = ?", [$id]);
            Session::setFlash('success', 'Đã xóa sản phẩm thành công.');
        }

        $this->redirect('/seller/products');
    }

    public function manageStock($id) {
        $productModel = new Product();
        $product = $productModel->find($id);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            http_response_code(403);
            die('403 - Forbidden');
        }
        
        $categoryModel = new Category();
        $category = $categoryModel->find($product['category_id']);
        $db = Database::getInstance();
        $stocks = $db->fetchAll(
            "SELECT * FROM product_stocks WHERE product_id = ? ORDER BY created_at DESC",
            [$id]
        );
        $variants = $db->fetchAll("SELECT * FROM product_variants WHERE product_id = ?", [$id]);
        
        $this->view('seller/product-stock', [
            'product' => $product,
            'stocks' => $stocks,
            'variants' => $variants,
            'categoryProfile' => Helper::getCategoryProductProfile($category ?: [])
        ]);
    }
    
    public function importStock() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/products');
            return;
        }
        
        CSRF::check();
        
        $productId = $_POST['product_id'] ?? 0;
        $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $stockContent = trim($_POST['stock_content'] ?? '');
        
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product || $product['seller_id'] != Auth::id()) {
            http_response_code(403);
            die('403 - Forbidden');
        }

        $categoryModel = new Category();
        $category = $categoryModel->find($product['category_id']);
        $profile = Helper::getCategoryProductProfile($category ?: []);
        $stockEntries = [];

        if (($profile['stock_mode'] ?? 'lines') === 'file') {
            $uploadedFiles = $_FILES['stock_files'] ?? null;
            if (empty($uploadedFiles) || empty($uploadedFiles['name']) || !is_array($uploadedFiles['name'])) {
                Session::setFlash('error', 'Vui lòng chọn ít nhất 1 file tải lên');
                $this->redirect('/seller/products/stock/' . $productId);
                return;
            }

            foreach ($uploadedFiles['name'] as $index => $originalName) {
                if (($uploadedFiles['error'][$index] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }

                $singleFile = [
                    'name' => $uploadedFiles['name'][$index],
                    'type' => $uploadedFiles['type'][$index],
                    'tmp_name' => $uploadedFiles['tmp_name'][$index],
                    'error' => $uploadedFiles['error'][$index],
                    'size' => $uploadedFiles['size'][$index],
                ];

                $upload = Helper::uploadDocumentFile($singleFile, 'downloads');
                if (!$upload['success']) {
                    Session::setFlash('error', $upload['message']);
                    $this->redirect('/seller/products/stock/' . $productId);
                    return;
                }

                $stockEntries[] = Helper::encodeStockContent('file', [
                    'path' => $upload['path'],
                    'name' => $upload['original_name'] ?? $upload['filename']
                ]);
            }
        } else {
            $importType = $_POST['import_type'] ?? 'list';
            if ($importType === 'quantity') {
                $quantity = (int)($_POST['stock_quantity'] ?? 0);
                if ($quantity <= 0) {
                    Session::setFlash('error', 'Vui lòng nhập số lượng hợp lệ');
                    $this->redirect('/seller/products/stock/' . $productId);
                    return;
                }
                for ($i = 0; $i < $quantity; $i++) {
                    $stockEntries[] = Helper::encodeStockContent('manual_delivery', [
                        'message' => 'San pham ban giao thu cong. Vui long lien he Nguoi ban qua muc Chat.'
                    ]);
                }
            } else {
                $lines = preg_split('/\r\n|\r|\n/', $stockContent);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line !== '') {
                        $stockEntries[] = $line;
                    }
                }
            }
        }

        $stockCount = count($stockEntries);
        if ($stockCount === 0) {
            Session::setFlash('error', ($profile['stock_mode'] ?? 'lines') === 'file'
                ? 'Vui lòng tải lên ít nhất 1 file'
                : 'Vui lòng nhập ít nhất 1 mục kho hàng');
            $this->redirect('/seller/products/stock/' . $productId);
            return;
        }
        
        $escrowService = new EscrowService();
        $escrowEnabled = $escrowService->isEscrowEnabled();
        $depositRequired = 0;
        
        if ($escrowEnabled) {
            $productPrice = $product['sale_price'] ?? $product['price'];
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
        
        $imported = 0;
        $db = Database::getInstance();
        
        foreach ($stockEntries as $entry) {
            $db->insert('product_stocks', [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'seller_id' => Auth::id(),
                'content' => $entry,
                'status' => 'available'
            ]);
            $imported++;
        }
        
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
        $search = trim($_GET['search'] ?? '');
        
        // Đánh dấu tất cả đơn hàng là đã đọc sau khi đã lấy dữ liệu để hiển thị (để vẫn hiện badge "Mới" lần đầu)
        $db->query("UPDATE order_items SET is_read = 1 WHERE seller_id = ? AND is_read = 0", [Auth::id()]);

        $page = (int)($_GET['page'] ?? 1);
        if ($page < 1) $page = 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        
        $where = "oi.seller_id = ?";
        $params = [Auth::id()];
        
        if ($search !== '') {
            $where .= " AND (o.order_code LIKE ? OR p.name LIKE ? OR u.name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        // Đếm tổng số để phân trang
        $totalCount = $db->fetchOne(
            "SELECT COUNT(*) as count 
             FROM order_items oi
             LEFT JOIN orders o ON oi.order_id = o.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON o.user_id = u.id
             WHERE {$where}",
            $params
        )['count'] ?? 0;
        $totalPages = ceil($totalCount / $perPage);

        $orders = $db->fetchAll(
            "SELECT oi.*, o.order_code, o.created_at, o.payment_status, 
                    p.name as product_name, u.name as buyer_name, o.id as order_id
             FROM order_items oi
             LEFT JOIN orders o ON oi.order_id = o.id
             LEFT JOIN products p ON oi.product_id = p.id
             LEFT JOIN users u ON o.user_id = u.id
             WHERE {$where}
             ORDER BY o.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );
        
        $this->view('seller/orders', [
            'orders' => $orders,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }
    
    public function orderDetail($orderId) {
        $db = Database::getInstance();
        $sellerId = Auth::id();
        
        // Kiểm tra đơn hàng có thuộc seller không
        $orderItem = $db->fetchOne(
            "SELECT oi.*, o.order_code, o.created_at, o.payment_status, o.order_status, o.total_amount,
                    p.name as product_name, p.thumbnail, p.product_type,
                    u.name as buyer_name, u.email as buyer_email, u.username as buyer_username, u.id as buyer_id
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

        // Kiểm tra xem tất cả items trong đơn đã hoàn thành chưa để cập nhật orders.order_status
        $remainingItems = $db->fetchOne(
            "SELECT COUNT(*) as count FROM order_items WHERE order_id = ? AND item_status NOT IN ('delivered', 'refunded')",
            [$orderId]
        )['count'];

        if ($remainingItems == 0) {
            $db->update('orders', ['order_status' => 'completed'], 'id = :id', ['id' => $orderId]);
        }

        Session::setFlash('success', 'Đã cập nhật trạng thái đơn hàng');
        $this->redirect('/seller/orders/' . $orderId);
    }

    public function wallet() {
        $walletService = new WalletService();
        $wallet = $walletService->getWallet(Auth::id());
        $transactions = $walletService->getTransactions(Auth::id(), 10);
        $walletSettings = [
            'deposit_bank_code' => Helper::getSettingValue('deposit_bank_code', 'KienLongBank'),
            'deposit_bank_name' => Helper::getSettingValue('deposit_bank_name', 'KienLongBank'),
            'deposit_account_name' => Helper::getSettingValue('deposit_account_name', 'TRAN THANH TUAN'),
            'deposit_account_number' => Helper::getSettingValue('deposit_account_number', '101499100004608842'),
            'wallet_telegram_support_username' => Helper::getWalletTelegramSettings()['support_username'],
            'wallet_telegram_support_url' => Helper::getWalletTelegramSettings()['support_url'],
        ];
        
        $this->view('seller/wallet', [
            'wallet' => $wallet,
            'transactions' => $transactions,
            'walletSettings' => $walletSettings
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
        $transferCode = 'NAPSELLER' . str_pad((string)$userId, 4, '0', STR_PAD_LEFT);
        $remaining = 5 - ($recentCount['cnt'] + 1); // Số lần còn lại sau khi tạo
        $walletTelegram = Helper::getWalletTelegramSettings();
        $bankCode = Helper::getSettingValue('deposit_bank_code', 'KienLongBank');
        $bankName = Helper::getSettingValue('deposit_bank_name', 'KienLongBank');
        $accountName = Helper::getSettingValue('deposit_account_name', 'TRAN THANH TUAN');
        $accountNumber = Helper::getSettingValue('deposit_account_number', '101499100004608842');
        
        $depositId = $db->insert('deposit_requests', [
            'user_id'        => $userId,
            'deposit_code'   => $depositCode,
            'transfer_code'  => $transferCode,
            'amount'         => $amount,
            'note'           => $note,
            'bank_code'      => $bankCode,
            'bank_name'      => $bankName,
            'account_name'   => $accountName,
            'account_number' => $accountNumber,
            'status'         => 'pending'
        ]);

        if ($walletTelegram['chat_id'] !== '' && $walletTelegram['bot_token'] !== '') {
            $seller = Auth::user();
            $telegramMessage = implode("\n", [
                'YEU CAU NAP TIEN SELLER',
                'Request ID: ' . $depositId,
                'Ma don: ' . $depositCode,
                'Seller ID: ' . $userId,
                'Ten: ' . ($seller['name'] ?? ''),
                'Username: ' . ($seller['username'] ?? ''),
                'So tien: ' . number_format($amount, 0, ',', '.') . 'd',
                'Noi dung CK: ' . $transferCode,
                'Ghi chu: ' . ($note !== '' ? $note : '(trong)')
            ]);
            Helper::sendTelegramMessage($walletTelegram['chat_id'], $telegramMessage, $walletTelegram['bot_token']);
        }
        
        $msg = "✅ Đã gửi yêu cầu nạp " . money($amount) . ". Mã đơn: {$depositCode}. Vui lòng chuyển khoản {$bankName} STK {$accountNumber} - {$accountName} với nội dung: {$transferCode}";
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
        $minBalanceSetting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'seller_minimum_balance'");
        
        $minAmount = $minAmountSetting ? (int)$minAmountSetting['value'] : 50000;
        $feePercent = $feePercentSetting ? (float)$feePercentSetting['value'] : 5;
        $minBalance = $minBalanceSetting ? (int)$minBalanceSetting['setting_value'] : 500000;

        // Tính tổng tiền các đơn hàng đang có khiếu nại
        $disputeAmount = $db->fetchOne(
            "SELECT SUM(oi.seller_amount) as total 
             FROM disputes d 
             JOIN order_items oi ON d.order_item_id = oi.id 
             WHERE d.seller_id = ? AND d.status IN ('open', 'under_review')",
            [Auth::id()]
        )['total'] ?? 0;

        // Tính tổng tiền các đơn hàng đang trong thời gian bảo hành (chưa hết hạn khiếu nại)
        $minimumDisputeHours = Helper::getMinimumDisputeHours();
        $warrantyAmount = $db->fetchOne(
            "SELECT SUM(oi.seller_amount) as total 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.seller_id = ? 
             AND oi.item_status = 'delivered' 
             AND DATE_ADD(
                    COALESCE(oi.status_updated_at, oi.created_at),
                    INTERVAL GREATEST(?, COALESCE(p.warranty_days, 0) * 24) HOUR
                 ) > NOW()",
            [Auth::id(), $minimumDisputeHours]
        )['total'] ?? 0;
        
        $this->view('seller/withdrawals', [
            'withdrawals' => $withdrawals,
            'wallet' => $wallet,
            'minAmount' => $minAmount,
            'feePercent' => $feePercent,
            'minBalance' => $minBalance,
            'disputeAmount' => $disputeAmount,
            'warrantyAmount' => $warrantyAmount
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
        
        $db = Database::getInstance();
        $disputeAmount = $db->fetchOne(
            "SELECT SUM(oi.seller_amount) as total 
             FROM disputes d 
             JOIN order_items oi ON d.order_item_id = oi.id 
             WHERE d.seller_id = ? AND d.status IN ('open', 'under_review')",
            [Auth::id()]
        )['total'] ?? 0;

        $minimumDisputeHours = Helper::getMinimumDisputeHours();
        $warrantyAmount = $db->fetchOne(
            "SELECT SUM(oi.seller_amount) as total 
             FROM order_items oi 
             JOIN products p ON oi.product_id = p.id 
             WHERE oi.seller_id = ? 
             AND oi.item_status = 'delivered' 
             AND DATE_ADD(
                    COALESCE(oi.status_updated_at, oi.created_at),
                    INTERVAL GREATEST(?, COALESCE(p.warranty_days, 0) * 24) HOUR
                 ) > NOW()",
            [Auth::id(), $minimumDisputeHours]
        )['total'] ?? 0;
        
        $minBalanceSetting = $db->fetchOne("SELECT setting_value FROM system_settings WHERE setting_key = 'seller_minimum_balance'");
        $minBalance = $minBalanceSetting ? (int)$minBalanceSetting['setting_value'] : 500000;
        
        $walletService = new WalletService();
        $wallet = $walletService->getWallet(Auth::id());
        
        $riskAmount = $disputeAmount + $warrantyAmount;
        $heldAmount = max($minBalance, $riskAmount);
        $withdrawable = max(0, $wallet['balance'] - $heldAmount);
        
        if ($amount > $withdrawable) {
            $msg = '⚠️ Số tiền rút vượt quá hạn mức cho phép. ';
            if ($riskAmount > $minBalance) {
                $msg .= 'Hệ thống đang giữ lại ' . money($riskAmount) . ' (bao gồm ' . money($disputeAmount) . ' khiếu nại và ' . money($warrantyAmount) . ' tiền bảo hành đơn hàng) để đảm bảo an toàn.';
            } else {
                $msg .= 'Bạn cần giữ lại ít nhất ' . money($minBalance) . ' (Quỹ bảo chứng) để duy trì tài khoản.';
            }
            Session::setFlash('error', $msg);
            $this->redirect('/seller/withdrawals');
            return;
        }

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

    public function refundOrder($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/orders/' . $id);
            return;
        }

        CSRF::check();
        
        $orderItemId = $_POST['order_item_id'] ?? 0;
        $note = trim($_POST['seller_note'] ?? '');

        if (!$orderItemId) {
            Session::setFlash('error', 'Sản phẩm không hợp lệ.');
            $this->redirect('/seller/orders/' . $id);
            return;
        }

        require_once __DIR__ . '/../Services/DisputeService.php';
        $disputeService = new DisputeService();
        $result = $disputeService->sellerRefundBuyer(Auth::id(), $id, $orderItemId, $note);

        if ($result['success']) {
            Session::setFlash('success', 'Đã hoàn tiền cho khách hàng thành công.');
        } else {
            Session::setFlash('error', $result['message']);
        }

        $this->redirect('/seller/orders/' . $id);
    }

    public function disputes() {
        require_once __DIR__ . '/../Models/Dispute.php';
        $disputeModel = new Dispute();
        $disputes = $disputeModel->getBySeller(Auth::id());
        
        $this->view('seller/disputes', [
            'disputes' => $disputes
        ]);
    }

    public function respondDispute($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/seller/disputes');
            return;
        }

        CSRF::check();

        $response = trim($_POST['seller_response'] ?? '');
        $evidenceImages = [];

        if (!empty($_FILES['seller_evidence_images']['name']) && is_array($_FILES['seller_evidence_images']['name'])) {
            foreach ($_FILES['seller_evidence_images']['name'] as $index => $name) {
                if (($name ?? '') === '') {
                    continue;
                }

                $fileArray = [
                    'name' => $_FILES['seller_evidence_images']['name'][$index],
                    'type' => $_FILES['seller_evidence_images']['type'][$index],
                    'tmp_name' => $_FILES['seller_evidence_images']['tmp_name'][$index],
                    'error' => $_FILES['seller_evidence_images']['error'][$index],
                    'size' => $_FILES['seller_evidence_images']['size'][$index],
                ];

                if (($fileArray['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                    continue;
                }

                $upload = Helper::uploadFile($fileArray, 'disputes');
                if (!$upload['success']) {
                    Session::setFlash('error', $upload['message']);
                    $this->redirect('/seller/disputes');
                    return;
                }

                $evidenceImages[] = $upload['path'];
            }
        }

        require_once __DIR__ . '/../Services/DisputeService.php';
        $disputeService = new DisputeService();
        $result = $disputeService->sellerRespond(Auth::id(), (int)$id, $response, $evidenceImages);

        Session::setFlash($result['success'] ? 'success' : 'error', $result['message']);
        $this->redirect('/seller/disputes');
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
        $success = $userModel->update($userId, ['telegram_chat_id' => $telegramChatId]);
        
        if ($success) {
            if ($telegramChatId !== '') {
                // Gửi tin nhắn thử
                $testMsg = "🔔 <b>THÔNG BÁO HỆ THỐNG</b>\nChúc mừng! Bạn đã kết nối Telegram thành công với tài khoản seller <b>" . Helper::telegramEscape(Auth::user()['name']) . "</b>.\nBạn sẽ nhận được thông báo ngay khi có đơn hàng mới hoặc khiếu nại.";
                $sent = Helper::sendTelegramMessage($telegramChatId, $testMsg);
                
                if ($sent) {
                    Session::setFlash('success', 'Đã cập nhật Telegram Chat ID và gửi tin nhắn thử thành công! Vui lòng kiểm tra Telegram của bạn.');
                } else {
                    Session::setFlash('warning', 'Đã lưu Chat ID nhưng không thể gửi tin nhắn thử. Hãy chắc chắn bạn đã nhấn <b>/start</b> với bot hệ thống.');
                }
            } else {
                Session::setFlash('success', 'Đã xóa Telegram Chat ID thành công.');
            }
        } else {
            Session::setFlash('error', 'Có lỗi xảy ra khi cập nhật Telegram Chat ID.');
        }
        
        $this->redirect('/seller/dashboard');
    }
}
