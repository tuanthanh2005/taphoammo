<?php
// app/Controllers/CheckoutController.php

class CheckoutController extends Controller {
    private function isAjaxRequest() {
        return (
            !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) || (
            !empty($_SERVER['HTTP_ACCEPT']) &&
            strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
        );
    }

    private function instantError($message, $redirect = '/') {
        if ($this->isAjaxRequest()) {
            $this->json([
                'success' => false,
                'message' => $message
            ], 422);
        }

        Session::setFlash('error', $message);
        $this->redirect($redirect);
    }

    public function index() {
        $cart = $_SESSION['cart'] ?? [];

        if (empty($cart)) {
            Session::setFlash('error', 'Giỏ hàng trống');
            $this->redirect('/cart');
            return;
        }

        $cartItems = [];
        $total = 0;
        $productModel = new Product();

        foreach ($cart as $productId => $quantity) {
            $product = $productModel->find($productId);
            if ($product) {
                $price = $product['sale_price'] ?? $product['price'];
                $cartItems[] = [
                    'product_id' => $product['id'],
                    'name' => $product['name'],
                    'thumbnail' => $product['thumbnail'],
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $price * $quantity,
                    'seller_id' => $product['seller_id']
                ];
                $total += $price * $quantity;
            }
        }

        $walletService = new WalletService();
        $wallet = $walletService->getWallet(Auth::id());

        $this->view('checkout/index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'wallet' => $wallet
        ]);
    }

    public function instant() {
        ob_start(); // Bắt mọi output để tránh lỗi JSON
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            ob_end_clean();
            $this->redirect('/');
            return;
        }

        CSRF::check();

        if (!Auth::check()) {
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => false,
                    'message' => 'Vui lòng đăng nhập để mua hàng',
                    'redirect' => '/login'
                ], 401);
            }

            Session::setFlash('error', 'Vui lòng đăng nhập để mua hàng');
            $this->redirect('/login');
            return;
        }

        $productId = $_POST['product_id'] ?? 0;
        $quantity = (int)($_POST['quantity'] ?? 1);
        $note = trim($_POST['note'] ?? '');

        if ($quantity < 1) {
            $this->instantError('Số lượng không hợp lệ', $_SERVER['HTTP_REFERER'] ?? '/');
            return;
        }

        $productModel = new Product();
        $product = $productModel->find($productId);

        if (!$product || !in_array($product['status'], ['active', 'approved'], true)) {
            $this->instantError('Sản phẩm không tồn tại hoặc đã ngừng kinh doanh', $_SERVER['HTTP_REFERER'] ?? '/');
            return;
        }

        // Kiểm tra trạng thái người bán
        $db = Database::getInstance();
        $seller = $db->fetchOne("SELECT status FROM users WHERE id = ?", [$product['seller_id']]);
        if (!$seller || $seller['status'] !== 'active') {
            $this->instantError('Người bán này hiện đang bị khóa tài khoản. Bạn không thể thực hiện mua hàng.', $_SERVER['HTTP_REFERER'] ?? '/');
            return;
        }

        if ($product['stock_quantity'] < $quantity) {
            $this->instantError('Sản phẩm không đủ số lượng trong kho', $_SERVER['HTTP_REFERER'] ?? '/');
            return;
        }

        if (!empty($product['require_note']) && $note === '') {
            $this->instantError('Sản phẩm này bắt buộc phải nhập ghi chú', $_SERVER['HTTP_REFERER'] ?? '/');
            return;
        }

        $price = $product['sale_price'] ?? $product['price'];
        $item = [
            'product_id' => $product['id'],
            'name' => $product['name'],
            'price' => $price,
            'quantity' => $quantity,
            'seller_id' => $product['seller_id'],
            'note' => $note
        ];

        $orderService = new OrderService();
        $result = $orderService->createOrder(Auth::id(), [$item]);

        if ($result['success']) {
            ob_end_clean();
            if ($this->isAjaxRequest()) {
                $this->json([
                    'success' => true,
                    'message' => 'Đặt hàng thành công! Mã đơn hàng: ' . $result['order_code'],
                    'order_id' => $result['order_id'],
                    'order_code' => $result['order_code']
                ]);
            }

            Session::setFlash('success', 'Đặt hàng thành công! Mã đơn hàng: ' . $result['order_code']);
            $this->redirect('/user/orders/' . $result['order_id']);
            return;
        }

        ob_end_clean();
        $this->instantError($result['message'], $_SERVER['HTTP_REFERER'] ?? '/');
    }
}

