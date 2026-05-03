<?php
// app/Controllers/ProductController.php

class ProductController extends Controller {
    
    public function index() {
        $productModel = new Product();
        $categoryModel = new Category();
        
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        $filters = [];
        
        if (!empty($_GET['category'])) {
            $filters['category_id'] = $_GET['category'];
        }
        
        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        $products = $productModel->getAll($filters, $page, $perPage);
        $categories = $categoryModel->getActive();
        
        // Get sponsored products (featured or random)
        $sponsoredProducts = $productModel->getSponsored(3);
        
        $this->view('products/index', [
            'products' => $products,
            'categories' => $categories,
            'sponsoredProducts' => $sponsoredProducts,
            'currentPage' => $page,
            'title' => 'Tất cả sản phẩm - AI CỦA TÔI'
        ]);
    }
    
    public function category($slug) {
        $categoryModel = new Category();
        $productModel = new Product();
        
        $category = $categoryModel->findBySlug($slug);
        
        if (!$category) {
            http_response_code(404);
            echo "404 - Category Not Found";
            return;
        }
        
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        $filters = [];
        if (isset($_GET['category']) && is_array($_GET['category'])) {
            $filters['category_id'] = $_GET['category'];
        } else {
            $filters['category_id'] = $category['id'];
        }
        
        if (isset($_GET['q'])) {
            $filters['search'] = $_GET['q'];
        }

        $products = $productModel->getAll($filters, $page, $perPage);
        
        $categories = $categoryModel->getActive();
        
        // Get sponsored/rented products
        $sponsoredProducts = $productModel->getSponsored(3);
        
        $this->view('products/category', [
            'category' => $category,
            'products' => $products,
            'categories' => $categories,
            'sponsoredProducts' => $sponsoredProducts,
            'currentPage' => $page,
            'title' => $category['name'] . ' - AI CỦA TÔI'
        ]);
    }
    
    public function show($slug) {
        $productModel = new Product();
        $db = Database::getInstance();
        
        $product = $productModel->findBySlug($slug);
        
        if (!$product) {
            http_response_code(404);
            echo "404 - Product Not Found";
            return;
        }
        
        // Get reviews
        $reviews = $db->fetchAll(
            "SELECT r.*, u.name as user_name, u.avatar 
             FROM reviews r
             LEFT JOIN users u ON r.user_id = u.id
             WHERE r.product_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC
             LIMIT 20",
            [$product['id']]
        );
        
        // Get related products
        $relatedProducts = $productModel->getAll([
            'category_id' => $product['category_id'],
            'status' => 'active'
        ], 1, 6);
        
        $this->view('products/show', [
            'product' => $product,
            'reviews' => $reviews,
            'relatedProducts' => $relatedProducts,
            'title' => $product['name'] . ' - AI CỦA TÔI',
            'meta_description' => Helper::truncate(strip_tags($product['description'] ?? ''), 160),
            'og_image' => asset($product['thumbnail'] ?? 'images/default-og.jpg')
        ]);
    }
    
    public function search() {
        $productModel = new Product();
        $categoryModel = new Category();
        $keyword = $_GET['q'] ?? '';
        
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        $products = $productModel->getAll([
            'search' => $keyword
        ], $page, $perPage);

        $categories = $categoryModel->getActive();
        $sponsoredProducts = $productModel->getSponsored(3);
        
        $this->view('products/search', [
            'products'          => $products,
            'keyword'           => $keyword,
            'categories'        => $categories,
            'sponsoredProducts' => $sponsoredProducts,
            'currentPage'       => $page,
            'title'             => 'Tìm kiếm: ' . $keyword . ' - AI CỦA TÔI'
        ]);
    }
    
    public function sellerShop($username) {
        $db = Database::getInstance();
        $productModel = new Product();

        $rawInput = trim((string)$username);
        $decodedInput = rawurldecode($rawInput); // Use rawurldecode as Router uses rawurldecode
        $decodedInput = trim($decodedInput, " \t\n\r\0\x0B/");

        $normalize = function ($value) {
            $value = trim((string)$value);
            $value = mb_strtolower($value, 'UTF-8');
            $map = [
                'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a',
                'â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a',
                'ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
                'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e',
                'ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
                'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
                'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o',
                'ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o',
                'ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
                'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u',
                'ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
                'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
                'đ'=>'d'
            ];
            $value = strtr($value, $map);
            $value = preg_replace('/[\s\-_]+/u', '', $value);
            return $value;
        };

        $slugNoSpace = $normalize($decodedInput);

        $seller = $db->fetchOne(
            "SELECT id, name, username, avatar, created_at, role, status
             FROM users
             WHERE status IN ('active', 'banned', 'suspended')
               AND (
                    username = ?
                    OR LOWER(REPLACE(REPLACE(REPLACE(username, ' ', ''), '-', ''), '_', '')) = ?
                    OR LOWER(REPLACE(REPLACE(REPLACE(name, ' ', ''), '-', ''), '_', '')) = ?
               )
             LIMIT 1",
            [$decodedInput, $slugNoSpace, $slugNoSpace]
        );

        if (!$seller && ctype_digit($decodedInput)) {
            $seller = $db->fetchOne(
                "SELECT id, name, username, avatar, created_at, role, status
                 FROM users
                 WHERE id = ? AND status IN ('active', 'banned', 'suspended')
                 LIMIT 1",
                [(int)$decodedInput]
            );
        }

        if (!$seller) {
            $like = '%' . $decodedInput . '%';
            $seller = $db->fetchOne(
                "SELECT id, name, username, avatar, created_at, role, status
                  FROM users
                  WHERE status IN ('active', 'banned', 'suspended')
                   AND (username LIKE ? OR name LIKE ?)
                 ORDER BY id ASC
                 LIMIT 1",
                [$like, $like]
            );
        }

        if (!$seller) {
            http_response_code(404);
            $this->view('errors/404', ['message' => 'Người bán không tồn tại.']);
            return;
        }
        
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        
        $products = $productModel->getAll(['seller_id' => $seller['id']], $page, $perPage);
        
        $this->view('products/seller_shop', [
            'seller' => $seller,
            'products' => $products,
            'currentPage' => $page,
            'title' => 'Cửa hàng của ' . $seller['name'] . ' - AI CỦA TÔI'
        ]);
    }
}
