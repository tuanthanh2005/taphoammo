<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<?php
$db = Database::getInstance();
$bannerLeft = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_left'")['value'] ?? '';
$bannerRight = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_right'")['value'] ?? '';
$bannerLeftLink = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_left_link'")['value'] ?? '#';
$bannerRightLink = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_banner_right_link'")['value'] ?? '#';
$heroBg = $db->fetchOne("SELECT value FROM settings WHERE key_name = 'home_hero_bg'")['value'] ?? '';
$favoriteProductIds = [];
if (Auth::check()) {
    $favoriteRows = $db->fetchAll("SELECT product_id FROM user_favorites WHERE user_id = ?", [Auth::id()]);
    $favoriteProductIds = array_map('intval', array_column($favoriteRows, 'product_id'));
}
?>

<!-- 🔥 HERO SECTION ULTRA MODERN 🔥 -->
<section class="hero-ultra position-relative overflow-hidden mb-0" data-aos="fade-in">
    <div class="hero-gradient-overlay"></div>
    <div class="hero-particles"></div>
    <?php if ($heroBg): ?>
        <div class="hero-bg-image" style="background-image: url('<?= asset($heroBg) ?>')"></div>
    <?php endif; ?>

    <div class="container position-relative z-3">
        <div class="row align-items-center justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 text-center">
                <h1 class="hero-title fw-bold mb-2" data-aos="fade-up" data-aos-delay="100">
                    <a href="<?= url('/') ?>" class="brand-logo">
                        <span class="brand-icon">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <span class="brand-text">
                            <span class="brand-name">aicuatoi</span><span class="brand-tld">.com</span>
                        </span>
                    </a>
                </h1>

                <!-- 🔥 SEARCH BAR SIÊU XỊN 🔥 -->
                <div class="search-container position-relative" data-aos="fade-up" data-aos-delay="200">
                    <form action="<?= url('/search') ?>" method="GET" class="search-form-ultra">
                        <div class="search-input-wrapper">
                            <div class="search-icon-left">
                                <i class="fas fa-search fs-5"></i>
                            </div>
                            <input type="text" name="q" class="search-input-main"
                                placeholder="Tìm tài khoản AI, phần mềm, khóa học..." required>
                            <button type="button" class="search-filters-toggle" id="filterToggleBtn" title="Mở bộ lọc">
                                <i class="fas fa-sliders-h"></i>
                            </button>
                            <button type="submit" class="search-btn-ultra">
                                <i class="fas fa-search"></i>
                                <span>Tìm</span>
                            </button>
                        </div>

                        <!-- Filter Panel (ẩn mặc định) -->
                        <div class="search-filters-panel" id="searchFilterPanel" style="display:none;">
                            <select class="form-select filter-select" name="category">
                                <option value="">Tất cả danh mục</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= (int) $category['id'] ?>"><?= e($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select class="form-select filter-select" name="sort">
                                <option value="random">Ngẫu nhiên</option>
                                <option value="newest">Mới nhất</option>
                                <option value="best_selling">Bán chạy</option>
                                <option value="price_asc">Giá thấp đến cao</option>
                                <option value="price_desc">Giá cao đến thấp</option>
                            </select>
                            <select class="form-select filter-select" name="price_range">
                                <option value="">Tất cả giá</option>
                                <option value="0-100000">Dưới 100K</option>
                                <option value="100000-500000">100K – 500K</option>
                                <option value="500000-2000000">500K – 2M</option>
                                <option value="2000000-">Trên 2M</option>
                            </select>
                        </div>

                    </form>
                </div>

                <!-- Stats VIP -->
                <div class="hero-stats mt-5" data-aos="fade-up" data-aos-delay="400">
                    <div class="stat-item">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Sản phẩm</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">20+</div>
                        <div class="stat-label">Người bán</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">99.9%</div>
                        <div class="stat-label">Đánh giá</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-6 d-none">
                <div class="hero-mockup" data-aos="zoom-in" data-aos-delay="500">
                    <div class="mockup-phone">
                        <div class="phone-screen">
                            <div class="screen-glow"></div>
                            <div class="search-animation">
                                <div class="search-bar-anim"></div>
                                <div class="results-anim"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 🔥 MAIN CONTENT LAYOUT VIP 🔥 -->
<div class="main-content-vip">
    <div class="container-fluid px-lg-5">
        <div class="row gx-5">
            <!-- Sidebar Left VIP -->
            <div class="d-none">
                <div class="sidebar-vip sticky-top pt-5" style="top: 120px;">
                    <div class="vip-banner-card mb-4">
                        <a href="<?= e($bannerLeftLink) ?>" class="banner-link-vip">
                            <?php if ($bannerLeft): ?>
                                <img src="<?= asset($bannerLeft) ?>" class="img-fluid rounded-3" alt="VIP Banner">
                            <?php else: ?>
                                <div class="banner-placeholder-vip">
                                    <div class="placeholder-icon">
                                        <i class="fas fa-gem fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold mb-2">VIP BANNER</h6>
                                    <p class="small mb-0">Liên hệ: 090xxxxxxx</p>
                                    <div class="vip-badge mt-2">Vị trí Vàng</div>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <!-- Quick Links -->
                    <div class="quick-links-card">
                        <h6 class="fw-bold mb-3"><i class="fas fa-bolt me-2 text-vip"></i>Nhanh</h6>
                        <div class="quick-links-list">
                            <a href="<?= url('/products?sort=hot') ?>" class="quick-link"><i class="fas fa-fire"></i>Hot
                                Deals</a>
                            <a href="<?= url('/products?sort=top_seller') ?>" class="quick-link"><i
                                    class="fas fa-star"></i>Top Seller</a>
                            <a href="<?= url('/products?on_sale=1') ?>" class="quick-link"><i
                                    class="fas fa-gift"></i>Khuyến mãi</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔥 MAIN CONTENT 🔥 -->
            <div class="col-12">
                <!-- Categories Compact Scroll -->
                <section class="section-compact mb-4" data-aos="fade-up">
                    <div class="section-header-compact">
                        <h2 class="section-title-compact">
                            <i class="fas fa-th-large me-2"></i>
                            Khám Phá Danh Mục
                        </h2>
                    </div>
                    <div class="categories-scroll-wrap">
                        <div class="categories-scroll-row">
                            <?php foreach ($categories as $category): ?>
                                <a href="<?= url('/category/' . $category['slug']) ?>" class="cat-pill">
                                    <div class="cat-pill-icon">
                                        <?php
                                        $iconClass = $category['icon'] ?? 'fa-folder';
                                        if (strpos($iconClass, 'fa-') === 0 && strpos($iconClass, ' ') === false) {
                                            $iconClass = ($iconClass === 'fa-bitcoin') ? 'fab ' . $iconClass : 'fas ' . $iconClass;
                                        }
                                        ?>
                                        <i class="<?= e($iconClass) ?>"></i>
                                    </div>
                                    <span class="cat-pill-name"><?= e($category['name']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>

                <!-- Featured Products 🔥 -->
                <?php if (!empty($featuredProducts)): ?>
                    <section class="section-vip mb-5" data-aos="fade-up">
                        <div class="section-header-vip">
                            <h2 class="section-title-vip">
                                <i class="fas fa-fire me-3 text-danger"></i>
                                <span>Sản Phẩm Hot 🔥</span>
                            </h2>
                        </div>
                        <div class="products-grid-ultra">
                            <?php foreach ($featuredProducts as $product): ?>
                                <div class="product-card-ultra" data-aos="fade-up" data-aos-delay="50">
                                    <?php $isFav = in_array((int) $product['id'], $favoriteProductIds, true); ?>
                                    <div class="product-media">
                                        <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>"
                                            alt="<?= e($product['name']) ?>" class="product-img-ultra">
                                        <div class="product-badges-ultra">
                                            <span class="badge-hot">🔥 HOT</span>
                                            <?php if ($product['sale_price']): ?>
                                                <span
                                                    class="badge-sale">-<?= number_format((1 - $product['sale_price'] / $product['price']) * 100) ?>%</span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="fav-btn" data-id="<?= $product['id'] ?>"
                                            onclick="toggleFavorite(this, event)">
                                            <i class="<?= $isFav ? 'fas text-danger' : 'far' ?> fa-heart"></i>
                                        </button>
                                        <div class="product-overlay">
                                            <a href="<?= url('/product/' . $product['slug']) ?>" class="btn-overlay">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                            <a href="<?= url('/product/' . $product['slug']) ?>#buy" class="btn-overlay-buy">
                                                <i class="fas fa-shopping-cart"></i> Mua ngay
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-name"><?= e(Helper::truncate($product['name'], 60)) ?></h6>
                                        <div class="seller-info">
                                            <div class="seller-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <span class="seller-name"><?= e($product['seller_name']) ?></span>
                                        </div>
                                        <div class="product-price-section">
                                            <?php if (!empty($product['display_price'])): ?>
                                                <div class="price-normal text-gradient-vip fw-bold">
                                                    <?= e($product['display_price']) ?>
                                                </div>
                                            <?php elseif ($product['sale_price']): ?>
                                                <div class="price-sale"><?= money($product['sale_price']) ?></div>
                                                <div class="price-old"><?= money($product['price']) ?></div>
                                            <?php else: ?>
                                                <div class="price-normal"><?= money($product['price']) ?></div>
                                            <?php endif; ?>
                                            <div class="stock-info">
                                                <i class="fas fa-box"></i>
                                                <?= $product['stock_quantity'] ?> còn
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <!-- 🔥 Mobile VIP Banners (Top) 🔥 -->
                <div class="d-none" data-aos="fade-up">
                    <div class="col-6">
                        <div class="vip-banner-card" style="aspect-ratio: auto;">
                            <a href="<?= e($bannerLeftLink) ?>" class="banner-link-vip" style="height: 220px;">
                                <?php if ($bannerLeft): ?>
                                    <img src="<?= asset($bannerLeft) ?>" class="img-fluid rounded-3 w-100 h-100"
                                        style="object-fit: cover;" alt="VIP Banner">
                                <?php else: ?>
                                    <div class="banner-placeholder-vip p-2">
                                        <div class="placeholder-icon"
                                            style="width: 50px; height: 50px; margin-bottom: 8px;">
                                            <i class="fas fa-gem text-white fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1" style="font-size: 0.8rem;">VIP BANNER</h6>
                                        <p class="small mb-0" style="font-size: 0.7rem;">Liên hệ: 090xxxxxxx</p>
                                        <div class="vip-badge mt-2" style="font-size: 0.7rem; padding: 4px 10px;">Vị trí
                                            Vàng</div>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="vip-banner-card" style="aspect-ratio: auto;">
                            <a href="<?= e($bannerRightLink) ?>" class="banner-link-vip" style="height: 220px;">
                                <?php if ($bannerRight): ?>
                                    <img src="<?= asset($bannerRight) ?>" class="img-fluid rounded-3 w-100 h-100"
                                        style="object-fit: cover;" alt="VIP Banner">
                                <?php else: ?>
                                    <div class="banner-placeholder-vip p-2">
                                        <div class="placeholder-icon"
                                            style="width: 50px; height: 50px; margin-bottom: 8px;">
                                            <i class="fas fa-gem text-white fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold mb-1" style="font-size: 0.8rem;">VIP BANNER</h6>
                                        <p class="small mb-0" style="font-size: 0.7rem;">Liên hệ: 090xxxxxxx</p>
                                        <div class="vip-badge mt-2" style="font-size: 0.7rem; padding: 4px 10px;">Vị trí
                                            Vàng</div>
                                    </div>
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Sponsored VIP Compact Scroll 🔥 -->
                <section class="section-compact mb-4" data-aos="fade-up">
                    <div class="section-header-compact">
                        <h2 class="section-title-compact">
                            <i class="fas fa-gem me-2 text-warning"></i>
                            Sản Phẩm VIP
                        </h2>
                        <div class="vip-label-sm">
                            <i class="fas fa-crown me-1"></i> GHIM TOP
                        </div>
                    </div>
                    <div class="vip-scroll-wrap">
                        <div class="vip-scroll-row">
                            <?php for ($i = 0; $i < 12; $i++): ?>
                                <?php if (isset($sponsoredProducts[$i])):
                                    $product = $sponsoredProducts[$i]; ?>
                                    <a href="<?= url('/product/' . $product['slug']) ?>" class="vip-card-sm">
                                        <div class="vip-card-thumb">
                                            <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>"
                                                alt="<?= e($product['name']) ?>">
                                            <span class="vip-badge-sm">⭐ VIP</span>
                                        </div>
                                        <div class="vip-card-body">
                                            <p class="vip-card-name"><?= e(Helper::truncate($product['name'], 50)) ?></p>
                                            <span class="vip-card-shop"><?= e($product['seller_name']) ?></span>
                                            <div class="vip-card-price">
                                                <?php if (!empty($product['display_price'])): ?>
                                                    <?= e($product['display_price']) ?>
                                                <?php elseif ($product['sale_price']): ?>
                                                    <?= money($product['sale_price']) ?>
                                                <?php else: ?>
                                                    <?= money($product['price']) ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php else: ?>
                                    <div class="vip-card-sm vip-card-empty">
                                        <div class="vip-card-thumb vip-empty-thumb">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                        <div class="vip-card-body">
                                            <p class="vip-card-name fw-bold">VIP SLOT</p>
                                            <span class="vip-card-shop">Liên hệ admin ghim top</span>
                                            <div class="vip-contact-sm">Telegram: @specademy</div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                </section>

                <!-- CTA Button -->
                <div class="text-center mb-5" data-aos="zoom-in">
                    <a href="<?= url('/products') ?>" class="cta-btn-ultra">
                        <i class="fas fa-store me-2"></i>
                        Xem tất cả 100+sản phẩm
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Sidebar Right VIP -->
            <div class="d-none">
                <div class="sidebar-vip sticky-top pt-5" style="top: 120px;">
                    <div class="vip-banner-card mb-4">
                        <a href="<?= e($bannerRightLink) ?>" class="banner-link-vip">
                            <?php if ($bannerRight): ?>
                                <img src="<?= asset($bannerRight) ?>" class="img-fluid rounded-3" alt="VIP Banner">
                            <?php else: ?>
                                <div class="banner-placeholder-vip">
                                    <div class="placeholder-icon">
                                        <i class="fas fa-gem fa-2x"></i>
                                    </div>
                                    <h6 class="fw-bold mb-2">VIP BANNER</h6>
                                    <p class="small mb-0">Liên hệ: 090xxxxxxx</p>
                                    <div class="vip-badge mt-2">Vị trí Vàng</div>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* 🔥 ULTRA VIP STYLES 🔥 */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

    :root {
        --vip-gold: #FFD700;
        --vip-purple: #8b5cf6;
        --vip-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        --glass-bg: rgba(255, 255, 255, 0.25);
        --glass-border: rgba(255, 255, 255, 0.18);
        --shadow-vip: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        --shadow-glow: 0 0 30px rgba(139, 92, 246, 0.4);
    }

    /* Typography */
    * {
        font-family: 'Inter', sans-serif;
    }

    body {
        background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%);
    }

    /* 🔥 HERO ULTRA 🔥 */
    .hero-ultra {
        min-height: auto;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        padding: 10px 0 10px;
    }

    .min-vh-90 {
        min-height: auto;
    }

    .hero-gradient-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: var(--vip-gradient);
        opacity: 0.95;
        z-index: 1;
    }

    .hero-particles {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-image:
            radial-gradient(2px 2px at 20px 30px, #fff, transparent),
            radial-gradient(2px 2px at 40px 70px, rgba(255, 255, 255, 0.8), transparent),
            radial-gradient(1px 1px at 90px 40px, #fff, transparent),
            radial-gradient(1px 1px at 130px 80px, rgba(255, 255, 255, 0.6), transparent);
        background-repeat: repeat;
        background-size: 200px 100px;
        animation: particles-float 20s linear infinite;
        z-index: 1;
    }

    @keyframes particles-float {
        0% {
            transform: translateY(0px) rotate(0deg);
        }

        100% {
            transform: translateY(-100px) rotate(360deg);
        }
    }

    .hero-bg-image {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-size: cover;
        background-position: center;
        opacity: 0.3;
        z-index: 1;
    }

    .hero-title {
        letter-spacing: -0.01em;
        font-size: clamp(0.95rem, 2vw, 1.15rem);
        line-height: 1.2;
        margin-bottom: 8px !important;
    }

    .brand-logo {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        padding: 4px 14px 4px 4px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.18);
        transition: all 0.25s ease;
    }

    .brand-logo:hover {
        background: rgba(255, 255, 255, 0.18);
        transform: translateY(-1px);
    }

    .brand-icon {
        width: 28px;
        height: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        border-radius: 50%;
        color: #5b21b6;
        font-size: 0.78rem;
        box-shadow: 0 4px 12px rgba(255, 165, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.5);
        flex-shrink: 0;
    }

    .brand-text {
        display: inline-flex;
        align-items: baseline;
        font-family: 'Poppins', system-ui, -apple-system, sans-serif;
        line-height: 1;
    }

    .brand-name {
        font-weight: 800;
        font-size: 1.15rem;
        color: #fff;
        letter-spacing: -0.02em;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .brand-tld {
        font-weight: 600;
        font-size: 0.78rem;
        color: #FFD700;
        margin-left: 1px;
        letter-spacing: 0;
    }

    .hero-subtitle {
        color: rgba(255, 255, 255, 0.92);
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.22);
        font-size: 0.85rem;
        margin-bottom: 10px !important;
    }

    .hero-badge {
        display: none;
    }

    .hero-badge .badge {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .search-container {
        max-width: 680px;
        margin: 0 auto;
        width: 100%;
    }

    .search-form-ultra {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(20px);
        border-radius: 16px;
        border: 1px solid var(--glass-border);
        padding: 6px;
        box-shadow: 0 10px 30px rgba(31, 41, 55, 0.18);
    }

    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        min-height: 48px;
        margin-bottom: 0;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.97);
        border: 1px solid rgba(255, 255, 255, 0.42);
        box-shadow: inset 0 0 0 1px rgba(139, 92, 246, 0.06);
    }

    .search-icon-left,
    .search-filters-toggle {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        color: var(--vip-purple);
        z-index: 2;
    }

    .search-icon-left {
        left: 14px;
    }

    .search-icon-left i {
        font-size: 0.9rem;
    }

    .search-filters-toggle {
        right: 92px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 9px;
        background: #f3f0ff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .search-filters-toggle:hover {
        background: #e8ddff;
        transform: translateY(-50%) scale(1.03);
    }

    .search-filters-toggle.is-active {
        color: #fff;
        background: var(--vip-purple);
    }

    .search-filters-toggle i {
        font-size: 0.85rem;
    }

    .search-input-main {
        flex: 1;
        border: none;
        background: transparent;
        width: 100%;
        padding: 12px 138px 12px 42px;
        font-size: 0.92rem;
        color: #1f2937;
        outline: none;
    }

    .search-input-main::placeholder {
        color: #9ca3af;
    }

    .search-filters-panel {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        padding: 0;
        margin-top: 8px;
        margin-bottom: 0;
        scrollbar-width: none;
    }

    .search-filters-panel::-webkit-scrollbar {
        display: none;
    }

    .filter-select {
        min-width: 0;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(139, 92, 246, 0.14);
        color: #374151;
        border-radius: 10px;
        padding: 8px 12px;
        font-size: 0.85rem;
    }

    .filter-select option {
        color: #1f2937;
    }

    .search-btn-ultra {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        position: absolute;
        top: 50%;
        right: 6px;
        transform: translateY(-50%);
        min-width: 80px;
        min-height: 36px;
        background: linear-gradient(135deg, var(--vip-purple), #a855f7);
        border: none;
        padding: 8px 14px;
        color: #fff;
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 9px;
        overflow: hidden;
        cursor: pointer;
        box-shadow: 0 6px 16px rgba(139, 92, 246, 0.3);
        transition: all 0.2s ease;
    }

    .search-btn-ultra:hover {
        transform: translateY(-50%) translateY(-1px);
        box-shadow: 0 10px 22px rgba(139, 92, 246, 0.38);
    }

    .search-btn-ultra i {
        font-size: 0.8rem;
    }

    @keyframes gradient-shift {

        0%,
        100% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }
    }

    .hero-stats {
        display: none;
    }

    .hero-mockup {
        display: flex;
        justify-content: center;
    }

    .mockup-phone {
        width: 280px;
        height: 580px;
        background: linear-gradient(145deg, #1a1a2e, #16213e);
        border-radius: 32px;
        position: relative;
        box-shadow: var(--shadow-vip);
    }

    .phone-screen {
        position: absolute;
        top: 24px;
        left: 12px;
        right: 12px;
        bottom: 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 24px;
        overflow: hidden;
    }

    .screen-glow {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        animation: screen-glow 2s ease-in-out infinite;
    }

    @keyframes screen-glow {

        0%,
        100% {
            opacity: 0;
            transform: translateX(-50%) translateY(-50%) rotate(0deg);
        }

        50% {
            opacity: 1;
            transform: translateX(0) translateY(0) rotate(180deg);
        }
    }

    /* 🔥 SECTIONS VIP 🔥 */
    .section-vip {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(16px);
        border-radius: 24px;
        padding: 40px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: var(--shadow-vip);
    }

    .section-header-vip {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 32px;
        flex-wrap: wrap;
        gap: 16px;
    }

    .section-title-vip {
        font-size: 2rem;
        font-weight: 800;
        background: linear-gradient(135deg, var(--vip-purple), #a855f7);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    .view-all-btn {
        color: var(--vip-purple);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .view-all-btn:hover {
        color: #7c3aed;
        transform: translateX(8px);
    }

    /* 🔥 COMPACT SECTION STYLES 🔥 */
    .section-compact {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(16px);
        border-radius: 20px;
        padding: 20px 24px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }

    .section-header-compact {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .section-title-compact {
        font-size: 1.05rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--vip-purple), #a855f7);
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    .view-all-link {
        font-size: 0.82rem;
        color: var(--vip-purple);
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
    }

    .view-all-link:hover {
        color: #7c3aed;
    }

    /* 🔥 CATEGORIES COMPACT GRID 🔥 */
    .categories-scroll-wrap {
        overflow-x: visible;
        margin: 0;
        padding: 4px 0 6px;
    }

    .categories-scroll-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
        gap: 10px;
        width: 100%;
    }

    .cat-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        width: 100%;
        padding: 16px 10px;
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid rgba(139, 92, 246, 0.08);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    }

    .cat-pill:hover {
        transform: translateY(-6px);
        background: linear-gradient(135deg, #ffffff 0%, #f5f3ff 100%);
        border-color: rgba(139, 92, 246, 0.3);
        box-shadow: 0 12px 25px rgba(139, 92, 246, 0.15);
    }

    .cat-pill-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 4px rgba(255, 255, 255, 0.5), 0 4px 10px rgba(139, 92, 246, 0.1);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        margin: 0 auto;
    }

    .cat-pill:hover .cat-pill-icon {
        transform: scale(1.08) rotate(5deg);
        background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%);
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.4);
    }

    .cat-pill-icon i {
        font-size: 1.3rem;
        color: #8b5cf6;
        transition: all 0.3s ease;
    }

    .cat-pill:hover .cat-pill-icon i {
        color: white;
    }

    .cat-pill-name {
        font-size: 0.8rem;
        font-weight: 700;
        text-align: center;
        color: #1f2937;
        line-height: 1.3;
        white-space: normal;
        word-break: break-word;
        max-width: 100%;
        transition: color 0.3s ease;
    }

    .cat-pill:hover .cat-pill-name {
        color: #7c3aed;
    }

    /* 🔥 PRODUCTS ULTRA 🔥 */
    .products-grid-ultra {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
        gap: 24px;
    }

    .product-card-ultra {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 24px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
        height: 100%;
    }

    .product-card-ultra:hover {
        transform: translateY(-20px);
        box-shadow: var(--shadow-glow), var(--shadow-vip);
    }

    .product-media {
        position: relative;
        height: 220px;
        overflow: hidden;
    }

    .product-img-ultra {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }

    .product-card-ultra:hover .product-img-ultra {
        transform: scale(1.1);
    }

    .product-badges-ultra {
        position: absolute;
        top: 16px;
        left: 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
        z-index: 3;
    }

    .badge-hot,
    .badge-vip,
    .badge-sale {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        color: white;
    }

    .badge-hot {
        background: linear-gradient(45deg, #ff6b6b, #ff8e8e);
    }

    .badge-vip {
        background: linear-gradient(45deg, var(--vip-gold), #ffed4a);
        color: #1a1a1a;
    }

    .badge-sale {
        background: linear-gradient(45deg, #10b981, #34d399);
    }

    .fav-btn {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 44px;
        height: 44px;
        background: rgba(255, 255, 255, 0.9);
        border: none;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: all 0.3s ease;
        z-index: 3;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .fav-btn:hover {
        background: #fff;
        transform: scale(1.1);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    .product-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.95), rgba(168, 85, 247, 0.95));
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        opacity: 0;
        transition: all 0.4s ease;
    }

    .product-card-ultra:hover .product-overlay {
        opacity: 1;
    }

    .btn-overlay,
    .btn-overlay-buy {
        padding: 12px 24px;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-overlay {
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .btn-overlay-buy {
        background: rgba(255, 255, 255, 0.9);
        color: var(--vip-purple);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .btn-overlay:hover,
    .btn-overlay-buy:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    }

    .product-info {
        padding: 24px;
    }

    .product-name {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 12px;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        color: var(--dark-color);
    }

    .seller-info {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }

    .seller-avatar {
        width: 28px;
        height: 28px;
        background: var(--vip-purple);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
    }

    .seller-name {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 500;
    }

    .product-price-section {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .price-sale,
    .price-normal {
        font-size: 1.4rem;
        font-weight: 800;
    }

    .price-sale {
        color: #ef4444;
    }

    .price-normal {
        color: var(--vip-purple);
    }

    .price-old {
        font-size: 0.85rem;
        color: #9ca3af;
        text-decoration: line-through;
    }

    .stock-info {
        font-size: 0.8rem;
        color: #10b981;
        font-weight: 600;
    }

    /* 🔥 VIP SPONSORED 🔥 */
    .vip-sponsored {
        border: 2px solid var(--vip-gold) !important;
        position: relative;
    }

    .vip-sponsored::before {
        content: '⭐ VIP';
        position: absolute;
        top: -12px;
        left: 20px;
        background: var(--vip-gold);
        color: #1a1a1a;
        padding: 4px 16px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        box-shadow: 0 4px 12px rgba(255, 215, 0, 0.4);
    }

    /* 🔥 VIP COMPACT GRID 🔥 */
    .vip-scroll-wrap {
        overflow-x: visible;
        margin: 0;
        padding: 4px 0 8px;
    }

    .vip-scroll-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 12px;
        width: 100%;
    }

    .vip-card-sm {
        width: 100%;
        flex-shrink: unset;
        background: rgba(255, 255, 255, 0.95);
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.3);
        text-decoration: none;
        color: inherit;
        display: flex;
        flex-direction: column;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.07);
    }

    .vip-card-sm:hover {
        transform: translateY(-6px);
        box-shadow: 0 12px 28px rgba(139, 92, 246, 0.25);
        color: inherit;
    }

    .vip-card-thumb {
        position: relative;
        height: 110px;
        overflow: hidden;
        background: #f1f5f9;
    }

    .vip-card-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.4s ease;
    }

    .vip-card-sm:hover .vip-card-thumb img {
        transform: scale(1.08);
    }

    .vip-badge-sm {
        position: absolute;
        top: 6px;
        left: 6px;
        background: linear-gradient(45deg, var(--vip-gold), #ffed4a);
        color: #1a1a1a;
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 0.65rem;
        font-weight: 700;
    }

    .vip-card-body {
        padding: 10px;
        display: flex;
        flex-direction: column;
        gap: 3px;
        flex: 1;
    }

    .vip-card-name {
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .vip-card-shop {
        font-size: 0.65rem;
        color: #94a3b8;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .vip-card-price {
        font-size: 0.82rem;
        font-weight: 800;
        color: var(--vip-purple);
        margin-top: 2px;
    }

    .vip-card-empty {
        border: 2px dashed #cbd5e1 !important;
        background: linear-gradient(135deg, #f8fafc, #f1f5f9) !important;
        cursor: default;
    }

    .vip-card-empty:hover {
        transform: none;
        box-shadow: none;
    }

    .vip-empty-thumb {
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
    }

    .vip-empty-thumb i {
        font-size: 2rem;
        color: #cbd5e1;
    }

    .vip-contact-sm {
        background: var(--vip-gold);
        color: #1a1a1a;
        padding: 3px 8px;
        border-radius: 20px;
        font-size: 0.6rem;
        font-weight: 700;
        margin-top: 4px;
        text-align: center;
    }

    /* 🔥 SIDEBAR VIP 🔥 */
    .sidebar-vip {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .vip-banner-card {
        aspect-ratio: 1;
    }

    .banner-link-vip {
        display: block;
        border-radius: 20px;
        overflow: hidden;
        height: 300px;
        transition: all 0.3s ease;
    }

    .banner-link-vip:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-glow);
    }

    .banner-placeholder-vip {
        height: 100%;
        background: linear-gradient(135deg, #f8fafc, #e2e8f0);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px;
        border-radius: 20px;
        border: 2px dashed #cbd5e1;
    }

    .placeholder-icon {
        width: 64px;
        height: 64px;
        background: var(--vip-gradient);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        color: white;
    }

    .vip-badge {
        background: var(--vip-gold);
        color: #1a1a1a;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-top: 12px;
    }

    .quick-links-card {
        background: rgba(255, 255, 255, 0.9);
        padding: 24px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .quick-links-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .quick-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        color: var(--dark-color);
        text-decoration: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .quick-link:hover {
        background: rgba(139, 92, 246, 0.1);
        color: var(--vip-purple);
        transform: translateX(8px);
    }

    .vip-label {
        background: linear-gradient(45deg, var(--vip-gold), #ffed4a);
        color: #1a1a1a;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 0.9rem;
        box-shadow: 0 4px 15px rgba(255, 215, 0, 0.4);
    }

    /* 🔥 CTA BUTTON 🔥 */
    .cta-btn-ultra {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--vip-gradient);
        color: white;
        padding: 12px 32px;
        border-radius: 50px;
        font-size: 1rem;
        font-weight: 700;
        text-decoration: none;
        box-shadow: var(--shadow-vip);
        transition: all 0.4s ease;
        border: 2px solid transparent;
    }

    .cta-btn-ultra:hover {
        transform: translateY(-4px) scale(1.05);
        box-shadow: 0 30px 60px -12px rgba(139, 92, 246, 0.5);
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        text-decoration: none;
        color: white;
    }

    /* 🔥 PLACEHOLDER VIP 🔥 */
    .vip-placeholder {
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border: 2px dashed #cbd5e1;
        border-radius: 24px;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #64748b;
        text-align: center;
        padding: 40px 20px;
    }

    .placeholder-content .placeholder-icon {
        font-size: 3rem;
        margin-bottom: 16px;
        opacity: 0.5;
    }

    .vip-contact {
        background: var(--vip-gold);
        color: #1a1a1a;
        padding: 6px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 700;
        margin-top: 12px;
    }

    /* 🔥 RESPONSIVE 🔥 */
    @media (max-width: 991.98px) {
        .hero-ultra {
            padding: 10px 0 8px;
        }

        .hero-title {
            font-size: 1.05rem !important;
        }

        .brand-name {
            font-size: 1.05rem;
        }

        .brand-tld {
            font-size: 0.72rem;
        }

        .brand-icon {
            width: 26px;
            height: 26px;
            font-size: 0.72rem;
        }

        .hero-subtitle {
            font-size: 0.78rem;
        }

        .search-container {
            max-width: 100%;
        }

        .search-filters-panel {
            grid-template-columns: 1fr;
        }

        .products-grid-ultra {
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
        }

        .section-compact {
            padding: 14px 16px;
        }

        .section-vip {
            padding: 24px 16px;
        }
    }

    @media (max-width: 768px) {
        .hero-ultra {
            padding: 8px 0 8px;
        }

        .hero-title {
            font-size: 0.98rem !important;
            margin-bottom: 6px !important;
        }

        .brand-logo {
            padding: 3px 12px 3px 3px;
            gap: 6px;
        }

        .brand-icon {
            width: 24px;
            height: 24px;
            font-size: 0.68rem;
        }

        .brand-name {
            font-size: 0.98rem;
        }

        .brand-tld {
            font-size: 0.68rem;
        }

        .hero-subtitle {
            font-size: 0.74rem;
        }

        .search-form-ultra {
            padding: 5px;
            border-radius: 12px;
            display: block;
        }

        .search-input-wrapper {
            min-height: 42px;
            margin-bottom: 0;
            border-radius: 10px;
        }

        .search-input-main {
            font-size: 0.85rem;
            padding: 8px 92px 8px 36px;
        }

        .search-icon-left {
            left: 12px;
        }

        .search-icon-left i {
            font-size: 0.82rem;
        }

        .search-filters-toggle {
            right: 50px;
            width: 30px;
            height: 30px;
            border-radius: 8px;
        }

        .search-filters-toggle i {
            font-size: 0.8rem;
        }

        .search-btn-ultra {
            width: 38px;
            min-width: unset;
            min-height: 30px;
            padding: 0;
            font-size: 0.82rem;
            border-radius: 8px;
            right: 5px;
        }

        .search-btn-ultra span {
            display: none;
        }

        .search-filters-panel {
            grid-template-columns: 1fr;
            margin-top: 8px;
            margin-bottom: 0;
        }

        .products-grid-ultra {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .section-compact {
            padding: 12px 14px;
            border-radius: 14px;
        }

        .section-title-compact {
            font-size: 0.95rem;
        }

        /* VIP -> grid 2 cột mobile */
        .vip-scroll-wrap {
            overflow-x: visible !important;
        }

        .vip-scroll-row {
            display: grid !important;
            grid-template-columns: repeat(2, 1fr) !important;
            gap: 10px !important;
            width: 100% !important;
            flex-wrap: unset;
        }

        .vip-card-sm {
            width: 100% !important;
            flex-shrink: unset;
        }

        .vip-card-thumb {
            height: 100px;
        }

        /* Danh mục -> grid 5 cột mobile */
        .categories-scroll-wrap {
            overflow-x: visible !important;
        }

        .categories-scroll-row {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 10px !important;
            width: 100% !important;
        }

        .cat-pill {
            width: 100% !important;
            flex-shrink: unset;
            padding: 12px 4px !important;
            gap: 6px !important;
        }

        .cat-pill-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
        }

        .cat-pill-icon i {
            font-size: 0.95rem;
        }

        .cat-pill-name {
            font-size: 0.58rem;
            max-width: 100%;
            white-space: normal;
            word-break: break-word;
            text-align: center;
        }

        .cta-btn-ultra {
            padding: 10px 24px;
            font-size: 0.9rem;
        }
    }

    @media (max-width: 576px) {
        .hero-ultra {
            padding: 6px 0 6px;
        }

        .hero-title {
            font-size: 0.92rem !important;
        }

        .hero-subtitle {
            display: none;
        }

        .search-input-main {
            padding: 8px 86px 8px 34px;
            font-size: 0.82rem;
        }

        .search-btn-ultra {
            width: 36px;
            min-width: unset;
            min-height: 30px;
            padding: 0;
            right: 5px;
            font-size: 0.8rem;
            border-radius: 8px;
        }

        .search-filters-toggle {
            right: 47px;
            width: 30px;
            height: 30px;
            border-radius: 8px;
        }

        .products-grid-ultra {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .vip-card-sm {
            width: 120px;
        }

        .vip-card-thumb {
            height: 80px;
        }

        .vip-card-body {
            padding: 8px;
        }

        .vip-card-name {
            font-size: 0.7rem;
        }

        .vip-card-price {
            font-size: 0.75rem;
        }

        .cat-pill {
            width: 100%;
        }

        .cat-pill-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
        }

        .cat-pill-name {
            font-size: 0.65rem;
            max-width: 100%;
        }

        .cta-btn-ultra {
            padding: 9px 20px;
            font-size: 0.85rem;
            width: auto;
        }

        .section-compact {
            padding: 10px 12px;
        }
    }

    /* 🔥 SMOOTH SCROLL & INTERACTIONS 🔥 */
    html {
        scroll-behavior: smooth;
    }

    * {
        scrollbar-width: thin;
        scrollbar-color: var(--vip-purple) transparent;
    }

    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: transparent;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(var(--vip-purple), #a855f7);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--vip-purple);
    }

    /* AOS Animations */
    [data-aos] {
        transition-duration: 0.8s;
    }

    [data-aos][data-aos][data-aos-loaded="true"] {
        transition-duration: 0.6s;
    }
</style>

<!-- AOS Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ once: true, duration: 800, easing: 'ease-out-cubic' });</script>

<script>
    // Toggle filter panel
    const filterBtn = document.getElementById('filterToggleBtn');
    const filterPanel = document.getElementById('searchFilterPanel');
    if (filterBtn && filterPanel) {
        filterBtn.style.cursor = 'pointer';
        filterBtn.addEventListener('click', function () {
            const isVisible = filterPanel.style.display !== 'none';
            if (isVisible) {
                filterPanel.style.maxHeight = '0';
                filterPanel.style.opacity = '0';
                setTimeout(() => { filterPanel.style.display = 'none'; }, 250);
                filterBtn.classList.remove('is-active');
            } else {
                filterPanel.style.display = 'grid';
                filterPanel.style.maxHeight = '0';
                filterPanel.style.opacity = '0';
                filterPanel.style.transition = 'max-height 0.3s ease, opacity 0.25s ease';
                requestAnimationFrame(() => {
                    filterPanel.style.maxHeight = '200px';
                    filterPanel.style.opacity = '1';
                });
                filterBtn.classList.add('is-active');
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
