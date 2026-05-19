<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background: linear-gradient(135deg, #2e1065 0%, #1e1b4b 50%, #0f172a 100%); position: relative; overflow: hidden;">
    <div class="position-absolute top-0 start-0 w-100 h-100" style="background: radial-gradient(circle at 80% 20%, rgba(139, 92, 246, 0.15) 0%, transparent 50%); pointer-events: none;"></div>
    <div class="container text-white position-relative z-3">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);">
                    <i class="fas fa-book-open me-2 text-warning"></i>Bài viết & Hướng dẫn
                </span>
                <h1 class="display-5 fw-bold mb-3">Tin Tức & Kinh Nghiệm</h1>
                <p class="text-white-50 mb-0">Chia sẻ kiến thức, mẹo sử dụng AI và thủ thuật MMO mới nhất từ các chuyên gia.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="text-white-50 small mb-1">Tổng số bài viết</div>
                <div class="display-6 fw-bold text-gradient-vip"><?= count($articles) ?> bài viết</div>
            </div>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: #f8fafc;">
    <div class="container">
        <?php if (empty($articles)): ?>
            <div class="bg-white rounded-4 shadow-sm p-5 text-center">
                <h3 class="fw-bold mb-2">Chưa có bài viết nào</h3>
                <p class="text-muted">Chúng tôi đang cập nhật nội dung, vui lòng quay lại sau.</p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($articles as $article): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="card h-100 border-0 shadow-sm overflow-hidden article-card" style="border-radius: 20px;">
                            <a href="<?= url('/bai-viet/' . $article['slug']) ?>" class="text-decoration-none">
                                <div class="article-card-cover">
                                    <img src="<?= !empty($article['cover_image']) ? asset($article['cover_image']) : asset('images/no-image.png') ?>"
                                         alt="<?= e($article['title']) ?>">
                                </div>
                            </a>
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-3" style="font-size: 0.75rem;">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        <?= Helper::formatDate($article['published_at'] ?: $article['created_at'], 'd/m/Y') ?>
                                    </span>
                                </div>
                                <h3 class="h5 fw-bold mb-2">
                                    <a href="<?= url('/bai-viet/' . $article['slug']) ?>"
                                       class="text-dark text-decoration-none article-title-link"><?= e($article['title']) ?></a>
                                </h3>
                                <p class="text-secondary small mb-4 line-clamp-3">
                                    <?= e($article['excerpt'] ?: Helper::truncate(strip_tags($article['content']), 120)) ?>
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= url('/bai-viet/' . $article['slug']) ?>"
                                       class="btn btn-outline-primary rounded-pill px-4 btn-read-more">
                                        Đọc bài viết <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (($totalPages ?? 1) > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link" href="<?= url('/bai-viet?page=' . $i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<style>
    .text-gradient-vip {
        background: linear-gradient(135deg, #a855f7 0%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .article-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.03) !important;
    }
    .article-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(139, 92, 246, 0.08) !important;
        border-color: rgba(139, 92, 246, 0.15) !important;
    }
    .article-card-cover {
        aspect-ratio: 16 / 9;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        overflow: hidden;
    }
    .article-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .article-card:hover .article-card-cover img {
        transform: scale(1.04);
    }
    .article-title-link {
        transition: color 0.2s ease;
    }
    .article-card:hover .article-title-link {
        color: var(--brand-main) !important;
    }
    .btn-read-more {
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .btn-read-more:hover {
        background-color: var(--brand-main) !important;
        border-color: var(--brand-main) !important;
        color: white !important;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;  
        overflow: hidden;
    }
    
    /* Custom Pagination Styling */
    .pagination .page-item .page-link {
        color: var(--brand-main);
        border-radius: 8px;
        margin: 0 4px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
        font-weight: 500;
        padding: 8px 16px;
    }
    .pagination .page-item.active .page-link {
        background-color: var(--brand-main) !important;
        border-color: var(--brand-main) !important;
        color: white !important;
    }
    .pagination .page-item .page-link:hover {
        background-color: var(--brand-light);
        border-color: var(--brand-main);
        color: var(--brand-dark);
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>