<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$db = Database::getInstance();
$bannerLeft = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_left'")['value'] ?? '';
$bannerRight = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_right'")['value'] ?? '';
$bannerLeftLink = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_left_link'")['value'] ?? '#';
$bannerRightLink = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_right_link'")['value'] ?? '#';
$heroBg = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_hero_bg'")['value'] ?? '';
?>

<!-- Hero Section -->
<div class="hero-section text-center text-white mb-5 position-relative overflow-hidden">
    <div class="hero-bg position-absolute w-100 h-100" <?= $heroBg ? 'style="background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url(\'' . asset($heroBg) . '\') center/cover no-repeat;"' : '' ?>></div>
    <div class="container position-relative z-1 py-5">
        <h1 class="display-3 fw-bold mb-3 animate-fade-in-up">AI CỦA TÔI</h1>
        <p class="lead mb-4 animate-fade-in-up animation-delay-1">Sàn thương mại điện tử sản phẩm số #1 Việt Nam</p>
        <form action="<?= url('/search') ?>" method="GET" class="mt-4 animate-fade-in-up animation-delay-2">
            <div class="search-box mx-auto shadow-lg">
                <input type="text" name="q" class="form-control form-control-lg border-0"
                    placeholder="Bạn cần tìm mua gì hôm nay?..." style="border-radius: 30px 0 0 30px;">
                <button class="btn btn-warning px-4 fw-bold" type="submit" style="border-radius: 0 30px 30px 0;">
                    <i class="fas fa-search"></i> Tìm ngay
                </button>
            </div>
        </form>
    </div>
</div>

<div class="container-fluid px-lg-5 mb-5">
    <div class="row">
        <!-- Banner Trái -->
        <div class="col-xl-2 col-lg-2 d-none d-lg-block">
            <div class="sticky-top" style="top: 100px; z-index: 10;">
                <a href="<?= e($bannerLeftLink) ?>"
                    class="d-block text-decoration-none banner-side-container shadow-sm rounded overflow-hidden">
                    <?php if ($bannerLeft): ?>
                        <img src="<?= asset($bannerLeft) ?>" class="img-fluid w-100" alt="Banner Left">
                    <?php else: ?>
                        <div class="bg-white text-center p-4 border banner-placeholder">
                            <i class="fas fa-ad fa-3x mb-3 text-muted"></i>
                            <p class="small text-secondary fw-bold">Liên hệ Admin đặt quảng cáo</p>
                            <span class="badge bg-primary px-3 py-2">Vị trí Vàng</span>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Nội Dung Chính -->
        <div class="col-xl-8 col-lg-8">
            <!-- Danh mục nổi bật -->
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h3 class="fw-bold section-title"><i class="fas fa-compass text-primary"></i> Khám Phá Danh Mục</h3>
            </div>

            <div class="row g-4 mb-5">
                <?php foreach ($categories as $category): ?>
                    <div class="col-md-3 col-sm-6 col-6">
                        <a href="<?= url('/category/' . $category['slug']) ?>" class="text-decoration-none">
                            <div class="category-card card h-100 border-0 shadow-sm text-center p-3 p-md-4">
                                <div class="category-icon mx-auto mb-3 text-primary bg-light rounded-circle d-flex align-items-center justify-content-center"
                                    style="width: 70px; height: 70px;">
                                    <?php
                                    $iconClass = $category['icon'] ?? 'fa-folder';
                                    if (strpos($iconClass, 'fa-') === 0 && strpos($iconClass, ' ') === false) {
                                        $iconClass = ($iconClass === 'fa-bitcoin') ? 'fab ' . $iconClass : 'fas ' . $iconClass;
                                    }
                                    ?>
                                    <i class="<?= e($iconClass) ?> fa-2x"></i>
                                </div>
                                <h6 class="card-title fw-bold text-dark mb-0"><?= e($category['name']) ?></h6>
                                <div class="category-overlay rounded"></div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Sản phẩm nổi bật -->
            <?php if (!empty($featuredProducts)): ?>
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <h3 class="fw-bold section-title"><i class="fas fa-fire text-danger"></i> Sản Phẩm Hot Nhất</h3>
                </div>

                <div class="row g-4 mb-5">
                    <?php foreach ($featuredProducts as $product): ?>
                        <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                            <div class="product-card card h-100 border-0 shadow-sm">
                                <div class="position-relative overflow-hidden">
                                    <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>"
                                        class="card-img-top product-img" alt="<?= e($product['name']) ?>">
                                    <div class="product-badges">
                                        <span class="badge bg-danger">HOT</span>
                                    </div>
                                    <div class="product-action-overlay d-flex align-items-center justify-content-center">
                                        <a href="<?= url('/product/' . $product['slug']) ?>"
                                            class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-primary">
                                            <i class="fas fa-shopping-cart"></i> Mua
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="card-title product-title mb-2">
                                        <a href="<?= url('/product/' . $product['slug']) ?>"
                                            class="text-dark text-decoration-none stretched-link">
                                            <?= e(Helper::truncate($product['name'], 50)) ?>
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="seller-avatar bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                            style="width: 20px; height: 20px; font-size: 10px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <small class="text-muted seller-name"
                                            style="font-size: 12px;"><?= e($product['seller_name']) ?></small>
                                    </div>
                                    <div class="mt-auto border-top pt-2">
                                        <div class="product-price mb-1">
                                            <?php if ($product['sale_price']): ?>
                                                <span
                                                    class="text-danger fw-bold d-block"><?= money($product['sale_price']) ?></span>
                                                <small class="text-muted text-decoration-line-through"
                                                    style="font-size: 11px;"><?= money($product['price']) ?></small>
                                            <?php else: ?>
                                                <span class="text-primary fw-bold d-block"><?= money($product['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="stock-badge badge bg-light text-dark border w-100 text-start mt-1"
                                            style="font-size: 11px;"><i class="fas fa-box text-muted"></i> Kho:
                                            <?= $product['stock_quantity'] ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Sản phẩm Tài trợ (Cho thuê) -->
            <!-- Sản phẩm Tài trợ (Cho thuê) -->
            <div class="d-flex justify-content-between align-items-end mb-4">
                <h3 class="fw-bold section-title"><i class="fas fa-gem text-warning"></i> Sản Phẩm HOT</h3>
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill"><i class="fas fa-ad"></i> HOT</span>
            </div>

            <div class="row g-4 mb-4">
                <?php for ($i = 0; $i < 8; $i++): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                        <?php if (isset($sponsoredProducts[$i])):
                            $product = $sponsoredProducts[$i]; ?>
                            <!-- Thẻ sản phẩm thật -->
                            <div class="product-card card h-100 border border-warning shadow-sm"
                                style="border-width: 2px !important;">
                                <div class="position-relative overflow-hidden">
                                    <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>"
                                        class="card-img-top product-img" alt="<?= e($product['name']) ?>">
                                    <div class="product-badges">
                                        <span class="badge bg-warning text-dark"><i class="fas fa-star"></i> HOT</span>
                                    </div>
                                    <div class="product-action-overlay d-flex align-items-center justify-content-center">
                                        <a href="<?= url('/product/' . $product['slug']) ?>"
                                            class="btn btn-light btn-sm rounded-pill px-3 fw-bold text-success">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <h6 class="card-title product-title mb-2">
                                        <a href="<?= url('/product/' . $product['slug']) ?>"
                                            class="text-dark text-decoration-none stretched-link">
                                            <?= e(Helper::truncate($product['name'], 50)) ?>
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="seller-avatar bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                                            style="width: 20px; height: 20px; font-size: 10px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <small class="text-muted seller-name"
                                            style="font-size: 12px;"><?= e($product['seller_name']) ?></small>
                                    </div>
                                    <div class="mt-auto border-top pt-2">
                                        <div class="product-price mb-1">
                                            <?php if ($product['sale_price']): ?>
                                                <span
                                                    class="text-danger fw-bold d-block"><?= money($product['sale_price']) ?></span>
                                                <small class="text-muted text-decoration-line-through"
                                                    style="font-size: 11px;"><?= money($product['price']) ?></small>
                                            <?php else: ?>
                                                <span class="text-primary fw-bold d-block"><?= money($product['price']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="stock-badge badge bg-light text-dark border w-100 text-start mt-1"
                                            style="font-size: 11px;"><i class="fas fa-box text-muted"></i> Kho:
                                            <?= $product['stock_quantity'] ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Ô trống cho thuê -->
                            <div class="product-card card h-100 border border-dashed text-center"
                                style="border: 2px dashed #dee2e6 !important; background-color: #f8f9fa;">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center py-5">
                                    <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center mb-3"
                                        style="width: 50px; height: 50px;">
                                        <i class="fas fa-plus text-muted"></i>
                                    </div>
                                    <h6 class="text-muted mb-1">Vị Trí Vàng</h6>
                                    <p class="small text-muted mb-0">Liên hệ admin để ghim sản phẩm của bạn tại đây</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="text-center d-block d-sm-none mb-5">
                <a href="<?= url('/products') ?>" class="btn btn-outline-success rounded-pill px-4">Xem tất cả sản phẩm
                    <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>

        <!-- Banner Phải -->
        <div class="col-xl-2 col-lg-2 d-none d-lg-block">
            <div class="sticky-top" style="top: 100px; z-index: 10;">
                <a href="<?= e($bannerRightLink) ?>"
                    class="d-block text-decoration-none banner-side-container shadow-sm rounded overflow-hidden">
                    <?php if ($bannerRight): ?>
                        <img src="<?= asset($bannerRight) ?>" class="img-fluid w-100" alt="Banner Right">
                    <?php else: ?>
                        <div class="bg-white text-center p-4 border banner-placeholder">
                            <i class="fas fa-ad fa-3x mb-3 text-muted"></i>
                            <p class="small text-secondary fw-bold">Liên hệ Admin đặt quảng cáo</p>
                            <span class="badge bg-primary px-3 py-2">Vị trí Vàng</span>
                        </div>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    /* Hero Section */
    .hero-section {
        padding: 60px 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .hero-bg {
        top: 0;
        left: 0;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        z-index: 0;
    }

    .search-box {
        display: flex;
        max-width: 600px;
        background: white;
        border-radius: 30px;
        padding: 5px;
    }

    .search-box input:focus {
        box-shadow: none;
    }

    /* Typography & Titles */
    .section-title {
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 0;
        font-size: 1.5rem;
    }

    .section-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        width: 50px;
        background: #4f46e5;
        border-radius: 3px;
    }

    /* Category Cards */
    .category-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
    }

    .category-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
    }

    .category-icon {
        transition: all 0.3s ease;
    }

    .category-card:hover .category-icon {
        background: #4f46e5 !important;
        color: white !important;
        transform: scale(1.1);
    }

    .category-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 0%, rgba(79, 70, 229, 0.05) 100%);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .category-card:hover .category-overlay {
        opacity: 1;
    }

    /* Product Cards */
    .product-card {
        transition: all 0.3s ease;
        border-radius: 8px;
    }

    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
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
        color: #4f46e5 !important;
    }

    .seller-name {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        width: 100%;
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
        background: rgba(0, 0, 0, 0.3);
        opacity: 0;
        transition: all 0.3s ease;
        z-index: 3;
    }

    .product-card:hover .product-action-overlay {
        opacity: 1;
    }

    /* Banner Side Container */
    .banner-side-container {
        transition: transform 0.3s ease;
        display: block;
        min-height: 400px;
        background: #f8f9fa;
    }

    .banner-side-container:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    }

    .banner-placeholder {
        height: 100%;
        min-height: 400px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    /* Animations */
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    .animation-delay-1 {
        animation-delay: 0.2s;
    }

    .animation-delay-2 {
        animation-delay: 0.4s;
    }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>