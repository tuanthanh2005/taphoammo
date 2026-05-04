<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="seller-shop-header py-5 text-white mb-5" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
    <div class="container py-4 text-center">
        <div class="seller-avatar-large mx-auto mb-3 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 100px; height: 100px; font-size: 40px;">
            <?php if (!empty($seller['avatar'])): ?>
                <img src="<?= asset($seller['avatar']) ?>" alt="<?= e($seller['name']) ?>" class="w-100 h-100 rounded-circle" style="object-fit: cover;">
            <?php else: ?>
                <?= strtoupper(substr($seller['name'], 0, 1)) ?>
            <?php endif; ?>
        </div>
        <h2 class="display-5 fw-bold mb-1">Cửa hàng của <?= e($seller['name']) ?></h2>
        <p class="mb-0 opacity-75">
            <?php if ($seller['status'] === 'active'): ?>
                <i class="fas fa-check-circle text-success bg-white rounded-circle p-1"></i> Người bán chuyên nghiệp
            <?php elseif ($seller['status'] === 'banned'): ?>
                <span class="badge bg-danger p-2"><i class="fas fa-ban me-1"></i> CỬA HÀNG ĐANG BỊ KHÓA</span>
            <?php elseif ($seller['status'] === 'suspended'): ?>
                <span class="badge bg-warning text-dark p-2"><i class="fas fa-exclamation-triangle me-1"></i> CỬA HÀNG ĐANG TẠM NGƯNG</span>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="container mb-5 pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <h3 class="fw-bold section-title mb-0"><i class="fas fa-box-open text-primary me-2"></i> Sản phẩm đang bán</h3>
        <span class="text-muted"><?= count($products) ?> sản phẩm</span>
    </div>

    <?php if (empty($products)): ?>
        <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
            <div class="mb-3 text-muted">
                <i class="fas fa-box-open fa-4x opacity-50"></i>
            </div>
            <h4 class="fw-bold text-dark">Chưa có sản phẩm nào</h4>
            <p class="text-secondary mb-0">Người bán này hiện tại chưa đăng bán sản phẩm nào.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                    <div class="product-card card h-100 border-0 shadow-sm">
                        <div class="position-relative overflow-hidden">
                            <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" class="card-img-top product-img" alt="<?= e($product['name']) ?>">
                            
                            <?php if ($product['is_featured']): ?>
                                <div class="product-badges">
                                    <span class="badge bg-danger">HOT</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-action-overlay d-flex align-items-center justify-content-center">
                                <a href="<?= url('/product/' . $product['slug']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary">
                                    <i class="fas fa-shopping-cart"></i> Mua
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <h6 class="card-title product-title mb-2">
                                <a href="<?= url('/product/' . $product['slug']) ?>" class="text-dark text-decoration-none stretched-link">
                                    <?= e(Helper::truncate($product['name'], 50)) ?>
                                </a>
                            </h6>
                            <div class="mt-auto border-top pt-2">
                                <div class="product-price mb-1">
                                    <?php if (!empty($product['display_price'])): ?>
                                        <span class="text-primary fw-bold d-block"><?= e($product['display_price']) ?></span>
                                    <?php elseif ($product['sale_price']): ?>
                                        <span class="text-danger fw-bold d-block"><?= money($product['sale_price']) ?></span>
                                        <small class="text-muted text-decoration-line-through" style="font-size: 11px;"><?= money($product['price']) ?></small>
                                    <?php else: ?>
                                        <span class="text-primary fw-bold d-block"><?= money($product['price']) ?></span>
                                    <?php endif; ?>
                                </div>
                                <span class="stock-badge badge bg-light text-dark border w-100 text-start mt-1" style="font-size: 11px;">
                                    <i class="fas fa-box text-muted"></i> Kho: <?= $product['stock_quantity'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($products) == 20): // Simplified pagination check ?>
            <div class="text-center mt-5">
                <a href="?page=<?= $currentPage + 1 ?>" class="btn btn-outline-primary rounded-pill px-4">
                    Tải thêm sản phẩm <i class="fas fa-chevron-down ms-1"></i>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
    /* Reuse Product Cards Style from Home */
    .section-title {
        position: relative;
        font-size: 1.5rem;
    }
    .product-card {
        transition: all 0.3s ease;
        border-radius: 8px;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .product-img {
        height: 180px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .product-card:hover .product-img {
        transform: scale(1.05);
    }
    .product-title {
        line-height: 1.4;
        height: 2.8em;
        overflow: hidden;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        font-size: 0.95rem;
    }
    .product-title a:hover {
        color: #8b5cf6 !important;
    }
    .product-badges {
        position: absolute;
        top: 8px;
        left: 8px;
        z-index: 2;
    }
    .product-action-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 3;
    }
    .product-card:hover .product-action-overlay {
        opacity: 1;
    }

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
