<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-5 text-center">
    <div class="py-5">
        <h1 class="display-1 fw-bold text-success">404</h1>
        <h2 class="mb-4">Không tìm thấy trang</h2>
        <p class="text-muted mb-5"><?= $message ?? 'Trang bạn đang tìm kiếm không tồn tại hoặc đã bị di dời.' ?></p>
        <a href="<?= url('/') ?>" class="btn btn-success rounded-pill px-5">Quay về trang chủ</a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
