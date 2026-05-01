<?php

class ArticleController extends Controller {
    public function index() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $articleModel = new Article();
        $articles = $articleModel->getPublished($page, 12);
        $total = $articleModel->countPublished();

        $this->view('articles/index', [
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($total / 12)),
            'title' => 'Bài viết - AI CỦA TÔI',
            'meta_description' => 'Tổng hợp bài viết, hướng dẫn và công cụ miễn phí từ AI CỦA TÔI.'
        ]);
    }

    public function show($slug) {
        $articleModel = new Article();
        $article = $articleModel->findPublishedBySlug($slug);

        if (!$article) {
            http_response_code(404);
            echo 'Bài viết không tồn tại.';
            return;
        }

        $db = Database::getInstance();
        $recentArticles = $db->fetchAll(
            "SELECT id, title, slug, published_at
             FROM articles
             WHERE status = 'published' AND id != ?
             ORDER BY published_at DESC, created_at DESC
             LIMIT 5",
            [$article['id']]
        );

        $this->view('articles/show', [
            'article' => $article,
            'recentArticles' => $recentArticles,
            'title' => ($article['seo_title'] ?: $article['title']) . ' - AI CỦA TÔI',
            'meta_description' => $article['seo_description'] ?: ($article['excerpt'] ?: Helper::truncate(strip_tags($article['content']), 150)),
            'og_image' => !empty($article['cover_image']) ? asset($article['cover_image']) : null
        ]);
    }

    public function adminIndex() {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        
        $articleModel = new Article();
        $articles = $articleModel->getAdminList($page, $perPage);
        $total = $articleModel->countAll();

        $this->view('admin/articles', [
            'articles' => $articles,
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($total / $perPage)),
            'pageTitle' => 'Quản lý bài viết'
        ]);
        
        // Clear old input after displaying the view (so it doesn't persist across navigation)
        clear_old_input();
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/bai-viet');
            return;
        }

        CSRF::check();

        $articleModel = new Article();
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDescription = trim($_POST['seo_description'] ?? '');

        if ($title === '' || $content === '') {
            save_old_input();
            Session::setFlash('error', 'Tiêu đề và nội dung là bắt buộc.');
            $this->redirect('/admin/bai-viet');
            return;
        }

        $data = [
            'title' => $title,
            'slug' => $articleModel->createSlug($title),
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => $status,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'published_at' => $status === 'published' ? date('Y-m-d H:i:s') : null
        ];

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['cover_image'], 'articles');
            if (!$upload['success']) {
                save_old_input();
                Session::setFlash('error', $upload['message']);
                $this->redirect('/admin/bai-viet');
                return;
            }
            $data['cover_image'] = $upload['path'];
        }

        $articleModel->create($data);
        clear_old_input();
        Session::setFlash('success', 'Đã thêm bài viết.');
        $this->redirect('/admin/bai-viet');
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/bai-viet');
            return;
        }

        CSRF::check();

        $articleModel = new Article();
        $article = $articleModel->find($id);
        if (!$article) {
            Session::setFlash('error', 'Bài viết không tồn tại.');
            $this->redirect('/admin/bai-viet');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        $seoTitle = trim($_POST['seo_title'] ?? '');
        $seoDescription = trim($_POST['seo_description'] ?? '');

        if ($title === '' || $content === '') {
            save_old_input();
            Session::setFlash('error', 'Tiêu đề và nội dung là bắt buộc.');
            $this->redirect('/admin/bai-viet');
            return;
        }

        $data = [
            'title' => $title,
            'slug' => $articleModel->createSlug($title, (int)$id),
            'excerpt' => $excerpt,
            'content' => $content,
            'status' => $status,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'published_at' => $status === 'published'
                ? ($article['published_at'] ?: date('Y-m-d H:i:s'))
                : null
        ];

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['cover_image'], 'articles');
            if (!$upload['success']) {
                save_old_input();
                Session::setFlash('error', $upload['message']);
                $this->redirect('/admin/bai-viet');
                return;
            }
            $data['cover_image'] = $upload['path'];
        }

        $articleModel->update($id, $data);
        clear_old_input();
        Session::setFlash('success', 'Đã cập nhật bài viết.');
        $this->redirect('/admin/bai-viet');
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/bai-viet');
            return;
        }

        CSRF::check();

        $articleModel = new Article();
        $article = $articleModel->find($id);
        if (!$article) {
            Session::setFlash('error', 'Bài viết không tồn tại.');
            $this->redirect('/admin/bai-viet');
            return;
        }

        $articleModel->delete($id);
        Session::setFlash('success', 'Đã xóa bài viết.');
        $this->redirect('/admin/bai-viet');
    }

    public function uploadImage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        CSRF::check();

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $upload = Helper::uploadFile($_FILES['file'], 'articles');
            if ($upload['success']) {
                $this->json([
                    'success' => true,
                    'url' => asset($upload['path'])
                ]);
            } else {
                $this->json(['success' => false, 'message' => $upload['message']]);
            }
        }

        $this->json(['success' => false, 'message' => 'No file uploaded']);
    }
}
