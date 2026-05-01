<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <article class="bg-white shadow-sm rounded-4 overflow-hidden">
                    <div class="article-hero">
                        <img src="<?= !empty($article['cover_image']) ? asset($article['cover_image']) : asset('images/no-image.png') ?>" alt="<?= e($article['title']) ?>">
                    </div>
                    <div class="p-4 p-lg-5">
                        <div class="small text-uppercase text-muted fw-semibold mb-3">Bài viết • <?= Helper::formatDate($article['published_at'] ?: $article['created_at'], 'd/m/Y H:i') ?></div>
                        <h1 class="display-6 fw-bold mb-3"><?= e($article['title']) ?></h1>
                        <?php if (!empty($article['excerpt'])): ?>
                            <p class="lead text-secondary mb-4"><?= e($article['excerpt']) ?></p>
                        <?php endif; ?>

                        <div class="article-content">
                            <?= Helper::renderArticleContent($article['content']) ?>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-lg-4">
                <aside class="bg-white shadow-sm rounded-4 p-4 sticky-top" style="top: 24px;">
                    <h3 class="h5 fw-bold mb-3">Bài viết mới</h3>
                    <?php if (empty($recentArticles)): ?>
                        <p class="text-muted small mb-0">Chưa có bài viết khác.</p>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach ($recentArticles as $item): ?>
                                <a href="<?= url('/bai-viet/' . $item['slug']) ?>" class="text-decoration-none border rounded-4 p-3 article-side-link">
                                    <div class="small text-muted mb-1"><?= Helper::formatDate($item['published_at'], 'd/m/Y') ?></div>
                                    <div class="fw-semibold text-dark"><?= e($item['title']) ?></div>
                                </a>
                            <?php endforeach; ?>
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
    background: linear-gradient(135deg, #e2e8f0, #f8fafc);
    overflow: hidden;
}
.article-hero img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.article-content {
    font-size: 1rem;
    line-height: 1.85;
    color: #334155;
}
.article-content h2,
.article-content h3 {
    color: #0f172a;
    font-weight: 700;
    margin-top: 2rem;
    margin-bottom: 1rem;
}
.article-content p,
.article-content ul {
    margin-bottom: 1.25rem;
}
.article-content ul {
    padding-left: 1.25rem;
}
.article-content a {
    color: #0f766e;
    word-break: break-word;
}
.article-media {
    margin: 1.5rem 0;
}
.article-media-frame {
    aspect-ratio: 16 / 9;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
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
    transition: 0.2s ease;
}
.article-side-link:hover {
    border-color: #0f766e !important;
    transform: translateY(-2px);
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
