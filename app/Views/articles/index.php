<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<section class="py-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #334155 100%);">
    <div class="container text-white">
        <div class="row align-items-end g-4">
            <div class="col-lg-8">
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background: rgba(255,255,255,0.12);">Bài viết &
                    công cụ miễn phí</span>
                <h1 class="display-5 fw-bold mb-3">Kho nội dung</h1>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="text-white-50 small">Tổng bài viết đang hiển thị</div>
                <div class="display-6 fw-bold"><?= count($articles) ?></div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <?php if (empty($articles)): ?>
            <div class="bg-white rounded-4 shadow-sm p-5 text-center">
                <h3 class="fw-bold mb-2">Chưa có bài viết nào</h3>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($articles as $article): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="card h-100 border-0 shadow-sm overflow-hidden" style="border-radius: 24px;">
                            <a href="<?= url('/bai-viet/' . $article['slug']) ?>" class="text-decoration-none">
                                <div class="article-card-cover">
                                    <img src="<?= !empty($article['cover_image']) ? asset($article['cover_image']) : asset('images/no-image.png') ?>"
                                        alt="<?= e($article['title']) ?>">
                                </div>
                            </a>
                            <div class="card-body p-4 d-flex flex-column">
                                <div class="small text-muted mb-2">
                                    <?= Helper::formatDate($article['published_at'] ?: $article['created_at'], 'd/m/Y') ?>
                                </div>
                                <h3 class="h5 fw-bold mb-2">
                                    <a href="<?= url('/bai-viet/' . $article['slug']) ?>"
                                        class="text-dark text-decoration-none"><?= e($article['title']) ?></a>
                                </h3>
                                <p class="text-secondary small mb-4">
                                    <?= e($article['excerpt'] ?: Helper::truncate(strip_tags($article['content']), 120)) ?>
                                </p>
                                <div class="mt-auto">
                                    <a href="<?= url('/bai-viet/' . $article['slug']) ?>"
                                        class="btn btn-outline-dark rounded-pill px-4">Đọc bài viết</a>
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
    .article-card-cover {
        aspect-ratio: 16 / 9;
        background: linear-gradient(135deg, #e2e8f0, #f8fafc);
        overflow: hidden;
    }

    .article-card-cover img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>