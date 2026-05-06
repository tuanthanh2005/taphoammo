<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid px-lg-5 my-4 bg-light pt-3 pb-5">
    <!-- Main layout -->
    <div class="row">
        
        <!-- Sidebar (Bộ lọc) -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Bộ lọc</h6>
                    <p class="text-success small mb-3">Chọn 1 hoặc nhiều sản phẩm</p>
                    
                    <?php 
                    $selectedCategories = $_GET['category'] ?? [$category['id']];
                    if (!is_array($selectedCategories)) {
                        $selectedCategories = [$selectedCategories];
                    }
                    ?>
                    <form action="" method="GET">
                        <?php foreach ($categories as $cat): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="category[]" value="<?= $cat['id'] ?>" id="cat_<?= $cat['id'] ?>" <?= in_array($cat['id'], $selectedCategories) ? 'checked' : '' ?>>
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
            <!-- 🔥 Sản phẩm HOT / Vị Trí Vàng 🔥 -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4 featured-widget">
                <div class="card-header bg-dark py-2 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-white" style="font-size: 13px;"><i class="fas fa-fire-alt me-1 text-danger"></i> Sản phẩm HOT</h6>
                        <span class="badge bg-secondary rounded-pill px-2 py-1 fw-bold" style="font-size: 8px; letter-spacing: 0.5px; opacity: 0.8;">TÀI TRỢ</span>
                    </div>
                </div>
                <div class="card-body p-2">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <?php if (isset($sponsoredProducts[$i])): $p = $sponsoredProducts[$i]; ?>
                            <!-- Thẻ sản phẩm thật -->
                            <div class="featured-item mb-2 p-2 rounded-2 transition-all <?= $i < 2 ? 'border-bottom' : '' ?>">
                                <div class="d-flex align-items-start">
                                    <div class="featured-img-wrap rounded-2 overflow-hidden flex-shrink-0 border mt-1">
                                        <img src="<?= asset($p['thumbnail'] ?? 'images/no-image.png') ?>" alt="<?= e($p['name']) ?>" class="w-100 h-100" style="object-fit: cover;">
                                    </div>
                                    <div class="ms-2 flex-grow-1">
                                        <a href="<?= url('/product/' . $p['slug']) ?>" class="text-dark text-decoration-none fw-bold small hover-primary d-block mb-1 featured-title" title="<?= e($p['name']) ?>" style="font-size: 12px; line-height: 1.3;">
                                            <?= e($p['name']) ?>
                                        </a>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($p['sale_price']): ?>
                                                <span class="text-danger fw-bold" style="font-size: 12px;"><?= money($p['sale_price']) ?></span>
                                                <span class="text-muted text-decoration-line-through" style="font-size: 9px;"><?= money($p['price']) ?></span>
                                            <?php else: ?>
                                                <span class="text-primary fw-bold" style="font-size: 12px;"><?= money($p['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1 px-1">
                                    <div class="small text-muted d-flex align-items-center" style="font-size: 10px;">
                                        <i class="fas fa-user me-1"></i>
                                        <span><?= e(Helper::truncate($p['seller_name'], 15)) ?></span>
                                    </div>
                                    <span class="text-success fw-bold" style="font-size: 10px;">
                                        Bán: <?= $p['total_sold'] ?? 0 ?>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Ô trống cho thuê -->
                            <div class="mb-2 <?= $i < 2 ? 'border-bottom pb-2' : '' ?>">
                                <div class="golden-slot p-3 text-center rounded-3 border-dashed position-relative">
                                    <div class="position-relative z-1">
                                        <div class="slot-icon mb-1">
                                            <i class="fas fa-crown text-muted opacity-50"></i>
                                        </div>
                                        <h6 class="text-muted small fw-bold mb-1" style="font-size: 11px;">Vị Trí Vàng</h6>
                                        <a href="https://t.me/admin_taphoammo" target="_blank" class="text-primary text-decoration-none fw-bold" style="font-size: 10px;">
                                            Thuê ngay <i class="fas fa-chevron-right ms-1" style="font-size: 8px;"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                </div>
            </div>
        </div>

        <!-- Main Content (Danh sách sản phẩm) -->
        <div class="col-lg-9 col-md-8">
            
            <!-- Search bar ngang -->
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; overflow: hidden;">
                <div class="card-body p-1">
                    <form action="" method="GET" class="d-flex w-100 align-items-stretch m-0">
                        <?php foreach($selectedCategories as $catId): ?>
                            <input type="hidden" name="category[]" value="<?= $catId ?>">
                        <?php endforeach; ?>
                        <input type="text" name="q" class="form-control border-0 bg-transparent flex-grow-1 px-4 py-2" value="<?= e($_GET['q'] ?? '') ?>" placeholder="Tìm kiếm sản phẩm..." style="box-shadow: none; font-size: 0.95rem;">
                        
                        <div class="border-start d-flex align-items-center" style="min-width: 140px; background-color: #f8fafc;">
                            <select class="form-select border-0 text-muted px-3 py-2 bg-transparent w-100" style="box-shadow: none; cursor: pointer; font-size: 0.9rem;">
                                <option>Ngẫu nhiên</option>
                                <option>Mới nhất</option>
                                <option>Bán chạy</option>
                                <option>Giá ↑</option>
                                <option>Giá ↓</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success px-4 rounded-end border-0 text-nowrap d-flex align-items-center justify-content-center m-1" style="min-width: 110px; border-radius: 8px;">
                            <i class="fas fa-search me-2"></i> Tìm
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Tiêu đề & Tab -->
            <div class="d-flex align-items-baseline mb-3">
                <h3 class="fw-bold me-2 mb-0">Mua bán <?= e($category['name']) ?> giá rẻ</h3>
                <span class="text-muted small"><?= count($products) ?> sản phẩm</span>
            </div>
            
            <ul class="nav nav-tabs border-0 mb-3 filter-tabs">
                <li class="nav-item">
                    <a class="nav-link active fw-bold text-success" href="#"><i class="fas fa-random"></i> Ngẫu nhiên</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="#">Mới nhất</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="#">Bán chạy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="#">Giá ↑</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-muted" href="#">Giá ↓</a>
                </li>
            </ul>
            
            <!-- Alert -->
            <div class="alert text-primary border-primary bg-transparent py-2 px-3 mb-4 d-flex align-items-center rounded-3">
                <i class="fas fa-info-circle me-2"></i>
                <span class="small fw-medium">Sản phẩm không trùng cam kết bán ra 1 lần duy nhất trên hệ thống.</span>
            </div>
            
            <!-- Product Grid -->
            <?php if (empty($products)): ?>
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <img src="<?= asset('images/empty.png') ?>" alt="Empty" style="width: 150px; opacity: 0.5; margin-bottom: 20px;">
                    <h5 class="text-muted">Chưa có sản phẩm nào trong danh mục này</h5>
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
                                
                                <!-- Phải: Thông tin chi tiết -->
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
                                        <?php $sSlug = !empty($product['seller_username']) ? $product['seller_username'] : (string)$product['seller_id']; ?>
                                        <a href="<?= url('/seller/' . rawurlencode($sSlug)) ?>" class="text-decoration-none fw-bold" style="position: relative; z-index: 2;"><?= e($product['seller_name']) ?></a>
                                        <span class="text-success ms-1"><i class="fas fa-check-circle" title="Đã xác thực"></i></span>
                                    </div>
                                    
                                    <div class="small mb-2" style="font-size: 12px;">
                                        <span class="text-muted">Sản phẩm:</span> <?= e($category['name']) ?>
                                    </div>
                                    
                                    <ul class="small text-muted ps-3 mb-0 mt-auto" style="font-size: 11px;">
                                        <li><?= e(Helper::truncate(strip_tags($product['short_description']), 60)) ?></li>
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
body {
    background-color: #f0f2f5;
}
.hover-primary:hover {
    color: var(--brand-main) !important;
}
.product-horizontal-card {
    border-radius: 8px;
    transition: all 0.2s ease;
}
.product-horizontal-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
}
.filter-tabs .nav-link {
    border: none;
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}
.filter-tabs .nav-link.active {
    background: transparent;
    border-bottom: 2px solid var(--brand-main);
    color: var(--brand-main) !important;
}
.filter-tabs .nav-link:hover {
    border-bottom: 2px solid #ddd;
}
.featured-widget { border: 1px solid #eee; }
.featured-item { transition: all 0.2s ease; }
.featured-item:hover { background: #f8f9fa; }
.featured-img-wrap { width: 45px; height: 45px; border-color: #eee !important; }
.featured-title {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.4;
    max-height: 2.8em;
}

.golden-slot {
    background: #fdfdfd;
    border: 1px dashed #ddd;
}
.golden-slot:hover {
    background: #f8f9fa;
    border-color: #ccc;
}
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
