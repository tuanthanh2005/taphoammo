<?php
// app/Controllers/FavoriteController.php

class FavoriteController extends Controller {
    
    public function index() {
        if (!Auth::check()) {
            $this->redirect('/login');
            return;
        }

        $userId = Auth::id();
        $page = $_GET['page'] ?? 1;
        
        require_once __DIR__ . '/../Models/Favorite.php';
        $favoriteModel = new Favorite();
        $favorites = $favoriteModel->getUserFavorites($userId, $page);

        $this->view('user/favorites', [
            'title' => 'Sản phẩm yêu thích',
            'favorites' => $favorites,
            'currentPage' => $page
        ]);
    }

    public function toggle() {
        if (!Auth::check()) {
            return $this->json(['success' => false, 'message' => 'Vui lòng đăng nhập'], 401);
        }

        CSRF::check();

        $productId = $_POST['product_id'] ?? 0;
        if (!$productId) {
            return $this->json(['success' => false, 'message' => 'Sản phẩm không hợp lệ'], 400);
        }

        require_once __DIR__ . '/../Models/Favorite.php';
        $favoriteModel = new Favorite();
        $result = $favoriteModel->toggle(Auth::id(), $productId);

        return $this->json([
            'success' => true, 
            'status' => $result['status'],
            'message' => $result['status'] === 'added' ? 'Đã thêm vào mục yêu thích' : 'Đã xóa khỏi mục yêu thích'
        ]);
    }
}
