<?php
// app/Controllers/CartController.php

class CartController extends Controller {
    
    public function index() {
        $cart = $_SESSION['cart'] ?? [];
        $cartItems = [];
        $total = 0;
        
        if (!empty($cart)) {
            $productModel = new Product();
            
            foreach ($cart as $productId => $quantity) {
                $product = $productModel->find($productId);
                if ($product) {
                    $price = $product['sale_price'] ?? $product['price'];
                    $cartItems[] = [
                        'product_id' => $product['id'],
                        'name' => $product['name'],
                        'slug' => $product['slug'],
                        'thumbnail' => $product['thumbnail'],
                        'price' => $price,
                        'quantity' => $quantity,
                        'subtotal' => $price * $quantity,
                        'stock_quantity' => $product['stock_quantity'],
                        'seller_id' => $product['seller_id']
                    ];
                    $total += $price * $quantity;
                }
            }
        }
        
        $this->view('cart/index', [
            'cartItems' => $cartItems,
            'total' => $total
        ]);
    }
    
    public function add() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }
        
        CSRF::check();
        
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        $productModel = new Product();
        $product = $productModel->find($productId);
        
        if (!$product) {
            Session::setFlash('error', 'Sản phẩm không tồn tại');
            $this->redirect('/cart');
            return;
        }
        
        if ($product['stock_quantity'] < $quantity) {
            Session::setFlash('error', 'Sản phẩm không đủ hàng');
            $this->redirect('/product/' . $product['slug']);
            return;
        }
        
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
        
        $action = $_POST['action'] ?? '';
        if ($action === 'buy_now') {
            $this->redirect('/checkout');
            return;
        }
        
        Session::setFlash('success', 'Đã thêm sản phẩm vào giỏ hàng');
        $this->redirect('/cart');
    }
    
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }
        
        CSRF::check();
        
        $productId = $_POST['product_id'] ?? 0;
        $quantity = $_POST['quantity'] ?? 1;
        
        if (isset($_SESSION['cart'][$productId])) {
            if ($quantity > 0) {
                $_SESSION['cart'][$productId] = $quantity;
            } else {
                unset($_SESSION['cart'][$productId]);
            }
        }
        
        $this->redirect('/cart');
    }
    
    public function remove() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }
        
        CSRF::check();
        
        $productId = $_POST['product_id'] ?? 0;
        
        if (isset($_SESSION['cart'][$productId])) {
            unset($_SESSION['cart'][$productId]);
        }
        
        Session::setFlash('success', 'Đã xóa sản phẩm khỏi giỏ hàng');
        $this->redirect('/cart');
    }
}
