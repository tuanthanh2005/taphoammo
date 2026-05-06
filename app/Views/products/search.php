<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-lg-5 my-4 bg-light pt-3 pb-5">
    <!-- Main layout -->
    <div class="row">
        
        <!-- Sidebar (Bộ lọc) -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Bộ lọc danh mục</h6>
                    <p class="text-success small mb-3">Lọc thêm theo danh mục</p>
                    
                    <form action="<?= url('/search') ?>" method="GET">
                        <input type="hidden" name="q" value="<?= e($keyword) ?>">
                        <?php foreach ($categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="category[]" value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>">
                                <label class="form-check-label text-secondary small" for="cat_<?= $cat['id'] ?>">
                                    <?= e($cat['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn btn-success w-100 mt-3 fw-bold">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </form>
                </div>
            </div>
            <!-- 🔥 Sản phẩm HOT / Vị Trí Vàng VIP 🔥 -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 featured-widget">
                <div class="card-header bg-gradient-warning py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-white"><i class="fas fa-fire-alt me-2 animate-pulse"></i>Sản phẩm HOT</h6>
                        <span class="badge bg-white text-danger rounded-pill px-2 py-1 fw-bold shadow-sm" style="font-size: 9px; letter-spacing: 0.5px;">TÀI TRỢ</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php if (isset($sponsoredProducts[$i])): $p = $sponsoredProducts[$i]; ?>
                            <!-- Thẻ sản phẩm thật VIP -->
                            <div class="featured-item mb-3 p-2 rounded-3 transition-all <?= $i < 2 ? 'border-bottom' : '' ?>">
                                <div class="d-flex align-items-start mb-2">
                                    <div class="featured-img-wrap rounded-3 overflow-hidden flex-shrink-0 shadow-sm mt-1">
                                        <img src="<?= asset($p['thumbnail'] ?? 'images/no-image.png') ?>" alt="<?= e($p['name']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <a href="<?= url('/product/' . $p['slug']) ?>" class="text-dark text-decoration-none fw-bold small hover-primary d-block mb-1 featured-title" title="<?= e($p['name']) ?>">
                                            <?= e($p['name']) ?>
                                        </a>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($p['sale_price']): ?>
                                                <span class="text-danger fw-black small"><?= money($p['sale_price']) ?></span>
                                                <span class="text-muted text-decoration-line-through" style="font-size: 10px;"><?= money($p['price']) ?></span>
                                            <?php else: ?>
                                                <span class="text-primary fw-black small"><?= money($p['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                    <div class="small text-muted d-flex align-items-center">
                                        <div class="avatar-mini bg-light rounded-circle me-1" style="width: 18px; height: 18px; font-size: 9px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span style="font-size: 10.5px;"><?= e(Helper::truncate($p['seller_name'], 15)) ?></span>
                                    </div>
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-10 rounded-pill" style="font-size: 9.5px;">
                                        Đã bán: <strong><?= $p['total_sold'] ?? 0 ?></strong>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Ô trống cho thuê VIP -->
                            <div class="mb-3 <?= $i < 2 ? 'border-bottom pb-3' : '' ?>">
                                <div class="golden-slot p-3 text-center rounded-4 border-dashed position-relative overflow-hidden">
                                    <div class="slot-glow"></div>
                                    <div class="position-relative z-1">
                                        <div class="slot-icon mb-2">
                                            <i class="fas fa-crown text-warning fa-lg animate-bounce"></i>
                                        </div>
                                        <h6 class="text-dark small fw-black mb-1 text-uppercase letter-spacing-1">Vị Trí Vàng</h6>
                                        <p class="text-muted mb-2" style="font-size: 9px;">Tiếp cận hàng ngàn khách hàng tiềm năng ngay hôm nay!</p>
                                        <a href="https://t.me/admin_taphoammo" target="_blank" class="btn btn-warning btn-xs rounded-pill fw-bold py-1 px-3 shadow-sm border-0 bg-gradient-warning text-white" style="font-size: 9px;">
                                            LIÊN HỆ THUÊ <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Search bar ngang -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="card-body p-1">
                    <form action="<?= url('/search') ?>" method="GET" class="d-flex w-100 align-items-stretch m-0">
                        <input type="text" name="q" class="form-control border-0 bg-transparent flex-grow-1 px-4 py-2" value="<?= e($keyword) ?>" placeholder="Tìm kiếm sản phẩm..." style="box-shadow: none; font-size: 0.95rem;">
                        <button type="submit" class="btn btn-success px-4 rounded-end border-0 text-nowrap d-flex align-items-center justify-content-center m-1" style="min-width: 110px; border-radius: 8px;">
                            <i class="fas fa-search me-2"></i> Tìm
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Tiêu đề -->
            <div class="d-flex align-items-baseline mb-3">
                <h3 class="fw-bold me-2 mb-0">Kết quả: "<span class="text-success"><?= e($keyword) ?></span>"</h3>
                <span class="text-muted small"><?= count($products) ?> sản phẩm</span>
            </div>
            
            <!-- Alert -->
            <div class="alert text-primary border-primary bg-transparent py-2 px-3 mb-4 d-flex align-items-center rounded-3">
                <i class="fas fa-info-circle me-2"></i>
                <span class="small fw-medium">Sản phẩm không trùng cam kết bán ra 1 lần duy nhất trên hệ thống.</span>
            </div>
            
            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <img src="<?= asset('images/empty.png') ?>" alt="Empty" style="width: 150px; opacity: 0.5; margin-bottom: 20px;">
                    <h5 class="text-muted">Không tìm thấy sản phẩm nào khớp với "<strong><?= e($keyword) ?></strong>"</h5>
                    <a href="<?= url('/products') ?>" class="btn btn-outline-success mt-3">Xem tất cả sản phẩm</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($products as $index => $product): ?>
                    
                    <!-- Horizontal Product Card -->
                    <div class="col-xl-6 col-lg-12">
                        <div class="card h-100 border-0 shadow-sm product-horizontal-card position-relative">
                            <div class="row g-0 p-3 h-100">
                                <!-- Trái: Hình ảnh & Giá -->
                                <div class="col-4 text-center d-flex flex-column justify-content-center align-items-center border-end pe-3">
                                    <div class="product-img-wrap w-100 mb-2 p-2 bg-light rounded d-flex align-items-center justify-content-center" style="height: 100px;">
                                        <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" class="img-fluid" alt="<?= e($product['name']) ?>" style="max-height: 100%; object-fit: contain;">
                                    </div>
                                    <div class="text-success small fw-bold mt-auto">Tồn kho: <?= $product['stock_quantity'] ?></div>
                                    <?php if (!empty($product['display_price'])): ?>
                                        <div class="fw-bold text-primary fs-6 mt-1"><?= e($product['display_price']) ?></div>
                                    <?php elseif ($product['sale_price']): ?>
                                        <div class="fw-bold text-danger fs-6 mt-1"><?= money($product['sale_price']) ?></div>
                                        <div class="text-muted text-decoration-line-through" style="font-size: 11px;"><?= money($product['price']) ?></div>
                                    <?php else: ?>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?= money($product['price']) ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Phải: Thông tin -->
                                <div class="col-8 ps-3 d-flex flex-column">
                                    <h6 class="card-title mb-1" style="line-height: 1.4;">
                                        <span class="badge bg-success me-1 rounded-pill fw-normal" style="font-size: 10px;">Sản phẩm</span>
                                        <a href="<?= url('/product/' . $product['slug']) ?>" class="text-dark text-decoration-none hover-primary fw-bold text-uppercase stretched-link">
                                            <?= e($product['name']) ?>
                                        </a>
                                        <?php if (!empty($product['show_crown'])): ?>
                                            <i class="fas fa-crown text-warning ms-1" title="Sản phẩm nổi bật"></i>
                                        <?php endif; ?>
                                    </h6>
                                    
                                    <div class="text-muted small mb-2 d-flex align-items-center flex-wrap" style="font-size: 11px;">
                                        <?php if (!empty($product['rating_count']) && $product['rating_count'] > 0): ?>
                                            <span class="text-warning">
                                                <?php
                                                $rating = round($product['rating_avg'] ?? 0);
                                                for ($j = 1; $j <= 5; $j++) {
                                                    echo $j <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                }
                                                ?>
                                            </span>
                                            <span class="ms-1 text-dark"><?= $product['rating_count'] ?> Đánh giá</span>
                                        <?php else: ?>
                                            <span class="text-muted"><i class="far fa-star"></i> Chưa đánh giá</span>
                                        <?php endif; ?>
                                        <span class="mx-1">|</span> 
                                        <span>Đã bán: <span class="text-success fw-bold"><?= $product['total_sold'] ?? 0 ?></span></span> 
                                        <span class="mx-1">|</span> 
                                        <span>Khiếu nại: 0%</span>
                                    </div>
                                    
                                    <div class="small mb-1" style="font-size: 12px;">
                                        <span class="text-muted">Người bán:</span> 
                                        <?php
                                        $sellerProfileSlug = !empty($product['seller_username'])
                                            ? $product['seller_username']
                                            : (string)($product['seller_id'] ?? '');
                                        ?>
                                        <a href="<?= url('/seller/' . rawurlencode($sellerProfileSlug)) ?>" class="text-decoration-none fw-bold" style="position: relative; z-index: 2;"><?= e($product['seller_name']) ?></a>
                                        <span class="text-success ms-1"><i class="fas fa-check-circle" title="Đã xác thực"></i></span>
                                    </div>
                                    
                                    <div class="small mb-2" style="font-size: 12px;">
                                        <span class="text-muted">Danh mục:</span> <?= e($product['category_name'] ?? '') ?>
                                    </div>
                                    
                                    <ul class="small text-muted ps-3 mb-0 mt-auto" style="font-size: 11px;">
                                        <li><?= e(Helper::truncate(strip_tags($product['short_description'] ?? ''), 60)) ?></li>
                                        <li>Bảo hành đăng nhập 1-1</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if (count($products) == 20 || $currentPage > 1): ?>
                <?php 
                    $queryParams = $_GET;
                    unset($queryParams['page']);
                    $queryString = http_build_query($queryParams);
                    $queryString = $queryString ? '&' . $queryString : '';
                ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-end">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $queryString ?>"><i class="fas fa-chevron-left"></i> Trước</a>
                            </li>
                        <?php endif; ?>
                        <li class="page-item active"><a class="page-link" href="#"><?= $currentPage ?></a></li>
                        <?php if (count($products) == 20): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $queryString ?>">Sau <i class="fas fa-chevron-right"></i></a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<style>
body { background-color: #f0f2f5; }
.hover-primary:hover { color: var(--brand-main) !important; }
.product-horizontal-card {
    border-radius: 8px;
    transition: all 0.2s ease;
}
.product-horizontal-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
}
.alert.border-primary {
    border-color: #cce5ff !important;
    background-color: #e8f4fd !important;
    color: #0056b3 !important;
}
.bg-gradient-warning { background: linear-gradient(135deg, #f6d365 0%, #fda085 100%); }
.btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.fw-black { font-weight: 900; }
.letter-spacing-1 { letter-spacing: 1px; }

.featured-widget { border: 1px solid rgba(0,0,0,0.05); }
.featured-item { transition: all 0.2s ease; }
.featured-item:hover { background: #fff8f0; transform: scale(1.02); }
.featured-img-wrap { width: 55px; height: 55px; border: 1px solid #f1f5f9; }
.featured-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
    max-height: 2.8em;
}

.golden-slot {
    background: #fff;
    border: 2px dashed #f6d365;
    transition: all 0.3s ease;
}
.golden-slot:hover {
    border-style: solid;
    background: #fffdf5;
    box-shadow: 0 10px 20px rgba(246, 211, 101, 0.15);
}
.slot-glow {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle at 50% 50%, rgba(246, 211, 101, 0.1), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.golden-slot:hover .slot-glow { opacity: 1; }

@keyframes pulse-custom {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
    100% { transform: scale(1); opacity: 1; }
}
.animate-pulse { animation: pulse-custom 2s infinite; }

@keyframes bounce-custom {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
.animate-bounce { animation: bounce-custom 2s infinite; }

.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
