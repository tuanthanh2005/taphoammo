<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background-color: #f8fafc;">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>" class="text-decoration-none text-secondary"><i class="fas fa-home me-1"></i> Trang chủ</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/bai-viet') ?>" class="text-decoration-none text-secondary">Bài viết</a></li>
                <li class="breadcrumb-item active text-dark fw-medium" aria-current="page"><?= e(Helper::truncate($article['title'], 40)) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-lg-8">
                <article class="bg-white shadow-sm border border-light-subtle overflow-hidden" style="border-radius: 24px;">
                    <div class="article-hero position-relative">
                        <img src="<?= !empty($article['cover_image']) ? asset($article['cover_image']) : asset('images/no-image.png') ?>" alt="<?= e($article['title']) ?>">
                        <div class="position-absolute bottom-0 start-0 w-100 p-4" style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);">
                            <span class="badge bg-primary text-white rounded-pill px-3 py-2 mb-2" style="font-size: 0.8rem;">
                                <i class="far fa-calendar-alt me-1.5"></i> <?= Helper::formatDate($article['published_at'] ?: $article['created_at'], 'd/m/Y H:i') ?>
                            </span>
                        </div>
                    </div>
                    <div class="p-4 p-lg-5">
                        <h1 class="display-6 fw-bold text-dark mb-4" style="line-height: 1.35; font-size: 2.2rem; letter-spacing: -0.02em;">
                            <?= e($article['title']) ?>
                        </h1>
                        
                        <?php if (!empty($article['excerpt'])): ?>
                            <div class="p-3 bg-light rounded-4 border-start border-primary border-4 mb-4 text-secondary italic" style="font-style: italic; line-height: 1.6;">
                                <?= e($article['excerpt']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="article-content">
                            <?= Helper::renderArticleContent($article['content']) ?>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-lg-4">
                <aside class="sticky-top" style="top: 24px;">
                    <div class="bg-white shadow-sm border border-light-subtle p-4 mb-4" style="border-radius: 20px;">
                        <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                            <span style="width: 4px; height: 18px; background: var(--brand-main); display: inline-block; border-radius: 2px;"></span>
                            Bài viết mới nhất
                        </h5>
                        <?php if (empty($recentArticles)): ?>
                            <p class="text-muted small mb-0">Chưa có bài viết khác.</p>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($recentArticles as $item): ?>
                                    <a href="<?= url('/bai-viet/' . $item['slug']) ?>" class="text-decoration-none border border-light-subtle rounded-4 p-3 article-side-link d-block">
                                        <div class="small text-muted mb-1 d-flex align-items-center gap-1.5">
                                            <i class="far fa-calendar-alt" style="font-size: 0.75rem;"></i>
                                            <?= Helper::formatDate($item['published_at'], 'd/m/Y') ?>
                                        </div>
                                        <div class="fw-semibold text-dark text-hover-brand" style="font-size: 0.95rem; line-height: 1.4; transition: color 0.2s;">
                                            <?= e($item['title']) ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                    $showDb = Database::getInstance();
                    $isSellerRegEnabledShow = $showDb->fetchOne("SELECT value FROM settings WHERE key_name = 'enable_seller_registration'")['value'] ?? 1;
                    if ((int)$isSellerRegEnabledShow === 1): 
                    ?>
                    <div class="bg-gradient-vip p-4 text-white text-center shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);">
                        <i class="fas fa-rocket fs-2 mb-3 text-warning"></i>
                        <h5 class="fw-bold mb-2">Trở thành nhà bán hàng</h5>
                        <p class="small text-white-50 mb-3">Mở gian hàng và bắt đầu đăng bán các tài khoản AI, key, code ngay hôm nay.</p>
                        <a href="<?= url('/nha-ban-hang') ?>" class="btn btn-light text-primary fw-bold rounded-pill px-4 btn-sm">Tìm hiểu ngay</a>
                    </div>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </div>
</section>

<style>
.article-hero {
    aspect-ratio: 16 / 9;
    background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    overflow: hidden;
}
.article-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.article-content {
    font-size: 1.05rem;
    line-height: 1.85;
    color: #334155;
}
.article-content h2,
.article-content h3 {
    color: #0f172a;
    font-weight: 700;
    margin-top: 2.25rem;
    margin-bottom: 1.15rem;
}
.article-content h2 {
    font-size: 1.5rem;
}
.article-content h3 {
    font-size: 1.25rem;
}
.article-content p,
.article-content ul {
    margin-bottom: 1.4rem;
}
.article-content ul {
    padding-left: 1.25rem;
}
.article-content a {
    color: var(--brand-main);
    text-decoration: underline;
    word-break: break-word;
    font-weight: 500;
}
.article-content a:hover {
    color: var(--brand-hover);
}
.article-media {
    margin: 1.75rem 0;
}
.article-media-frame {
    aspect-ratio: 16 / 9;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.article-media-frame img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
}
.article-side-link {
    background: #fafafa;
    transition: all 0.25s ease;
}
.article-side-link:hover {
    border-color: var(--brand-main) !important;
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.05);
}
.article-side-link:hover .text-hover-brand {
    color: var(--brand-main) !important;
}
.breadcrumb-item a:hover {
    color: var(--brand-main) !important;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
