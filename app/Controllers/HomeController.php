<?php
// app/Controllers/HomeController.php

class HomeController extends Controller {
    
    public function index() {
        $productModel = new Product();
        $categoryModel = new Category();
        
        // Get categories
        $categories = $categoryModel->getActive();
        
        // Get featured products
        $featuredProducts = $productModel->getAll(['is_featured' => 1], 1, 8);
        
        // Get sponsored/rented products
        $db = Database::getInstance();
        $sponsoredIdsStr = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'sponsored_product_ids'")['value'] ?? '';
        $sponsoredProducts = [];
        
        if (!empty(trim($sponsoredIdsStr))) {
            $ids = array_map('trim', explode(',', $sponsoredIdsStr));
            $ids = array_filter($ids, function($v) { return is_numeric($v) && intval($v) > 0; });
            $ids = array_map('intval', $ids);
            
            if (!empty($ids)) {
                $placeholders = str_repeat('?,', count($ids) - 1) . '?';
                $sql = "SELECT p.*, u.name as seller_name 
                        FROM products p 
                        LEFT JOIN users u ON p.seller_id = u.id 
                        WHERE p.id IN ($placeholders) AND p.status IN ('active', 'approved')";
                $fetchedProducts = $db->fetchAll($sql, $ids);
                
                // Giữ nguyên thứ tự ID Admin đã nhập
                foreach ($ids as $id) {
                    foreach ($fetchedProducts as $p) {
                        if ((int)$p['id'] === (int)$id) {
                            $sponsoredProducts[] = $p;
                            break;
                        }
                    }
                }
            }
        }
        
        $this->view('home/index', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'sponsoredProducts' => $sponsoredProducts,
            'title' => 'Trang chủ - AI CỦA TÔI',
            'meta_description' => 'AI CỦA TÔI - Nền tảng mua bán sản phẩm số uy tín #1 Việt Nam. Chuyên cung cấp tài khoản, key phần mềm, khóa học với giá tốt nhất.'
        ]);
    }

    public function faqs() {
        $this->view('home/faqs', [
            'title' => 'Câu hỏi thường gặp - AI CỦA TÔI',
            'meta_description' => 'Giải đáp các thắc mắc thường gặp khi sử dụng nền tảng AI CỦA TÔI.'
        ]);
    }

    public function support() {
        $this->view('home/support', [
            'title' => 'Hỗ trợ khách hàng - AI CỦA TÔI',
            'meta_description' => 'Liên hệ với chúng tôi để được hỗ trợ giải đáp mọi thắc mắc.'
        ]);
    }

    public function twoFactor() {
        $this->view('home/2fa', [
            'title' => 'Công cụ 2FA - AI CỦA TÔI',
            'meta_description' => 'Công cụ tạo mã xác thực 2 bước (2FA) trực tuyến an toàn và nhanh chóng.'
        ]);
    }

    public function sellerRegistration() {
        $this->view('home/seller-registration', [
            'title' => 'Trở thành Nhà bán hàng - AI CỦA TÔI'
        ]);
    }
}
