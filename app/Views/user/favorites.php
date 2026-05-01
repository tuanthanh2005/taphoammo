<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h4 class="fw-bold mb-0">
                <i class="fas fa-heart text-danger me-2"></i> Sản phẩm đã lưu
            </h4>
            <p class="text-muted small mb-0">Lưu lại những sản phẩm bạn yêu thích để mua sau</p>
        </div>
    </div>

    <?php if (empty($favorites)): ?>
        <div class="card border-0 shadow-sm rounded-4 py-5 text-center">
            <div class="py-5">
                <div class="mb-4">
                    <i class="fas fa-heart-broken fa-4x text-muted opacity-25"></i>
                </div>
                <h5>Chưa có sản phẩm nào được lưu</h5>
                <p class="text-muted">Hãy dạo quanh cửa hàng và lưu lại những sản phẩm bạn ưng ý nhé!</p>
                <a href="<?= url('/products') ?>" class="btn btn-success rounded-pill px-4 mt-3">Tiếp tục mua sắm</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($favorites as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden product-card position-relative">
                        <button class="btn btn-sm btn-light rounded-circle shadow-sm position-absolute top-0 end-0 m-2 z-3 fav-toggle-btn active" 
                                data-id="<?= $product['id'] ?>" title="Bỏ lưu">
                            <i class="fas fa-heart text-danger"></i>
                        </button>
                        
                        <a href="<?= url('/product/' . $product['slug']) ?>" class="text-decoration-none text-dark">
                            <div class="ratio ratio-4x3 overflow-hidden">
                                <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" class="card-img-top object-fit-cover" alt="<?= e($product['name']) ?>">
                            </div>
                            <div class="card-body p-3">
                                <div class="small text-primary mb-1"><?= e($product['category_name']) ?></div>
                                <h6 class="card-title fw-bold mb-2 text-truncate"><?= e($product['name']) ?></h6>
                                <div class="d-flex align-items-center mb-2 small text-muted">
                                    <i class="fas fa-store me-1"></i> <?= e($product['seller_name']) ?>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-bold text-danger fs-5"><?= money($product['sale_price'] ?? $product['price']) ?></span>
                                    <?php if ($product['sale_price']): ?>
                                        <span class="text-muted text-decoration-line-through small"><?= money($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.fav-toggle-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const productId = this.dataset.id;
            const card = this.closest('.col-6');
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('csrf_token', '<?= csrf_token() ?>');

            try {
                const response = await fetch('<?= url('/api/favorites/toggle') ?>', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    if (result.status === 'removed') {
                        card.remove();
                        // Check if no more items
                        if (document.querySelectorAll('.product-card').length === 0) {
                            location.reload();
                        }
                    }
                }
            } catch (err) {
                console.error(err);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
