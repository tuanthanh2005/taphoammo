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
        <div class="row align-items-center justify-content-center min-vh-90">
            <div class="col-xl-8 col-lg-9 col-md-11 text-center">
                <div class="hero-badge mb-4">
                    <span class="badge bg-gradient-vip px-4 py-2 fs-6 fw-bold">
                        <i class="fas fa-crown me-2"></i>#1 Sàn TMĐT Sản Phẩm Số VN
                    </span>
                </div>
                <h1 class="hero-title display-2 fw-bold mb-4" data-aos="fade-up" data-aos-delay="100">
                    AI CỦA TÔI
                </h1>
                <p class="hero-subtitle lead mb-5 opacity-90" data-aos="fade-up" data-aos-delay="200">
                    Nơi hội tụ <strong>triệu sản phẩm số</strong> chất lượng cao - Giá sốc - Giao ngay
                </p>
                
                <!-- 🔥 SEARCH BAR SIÊU XỊN 🔥 -->
                <div class="search-container position-relative" data-aos="fade-up" data-aos-delay="300">
                    <form action="<?= url('/search') ?>" method="GET" class="search-form-ultra">
                        <div class="search-input-wrapper">
                            <div class="search-icon-left">
                                <i class="fas fa-search fs-5"></i>
                            </div>
                            <input type="text" name="q" class="search-input-main" 
                                   placeholder="🔥 Tìm kiếm sản phẩm, shop, seller..." required>
                            <div class="search-filters-toggle">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                        </div>
                        <div class="search-filters-panel">
                            <select class="form-select filter-select" name="search_option">
                                <option value="all">Tất cả</option>
                                <option value="product">Sản phẩm</option>
                                <option value="shop">Shop</option>
                                <option value="seller">Seller</option>
                            </select>
                            <select class="form-select filter-select" name="category">
                                <option value="">Tất cả danh mục</option>
                                <option value="accounts">Tài khoản</option>
                                <option value="software">Phần mềm</option>
                                <option value="courses">Khóa học</option>
                            </select>
                            <select class="form-select filter-select" name="price_range">
                                <option value="">Tất cả giá</option>
                                <option value="0-100000">Dưới 100K</option>
                                <option value="100000-500000">100K - 500K</option>
                            </select>
                        </div>
                        <button type="submit" class="search-btn-ultra">
                            <i class="fas fa-rocket me-2"></i>
                            <span>TÌM KIẾM</span>
                            <div class="btn-glow"></div>
                        </button>
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
            <div class="col-xl-2 col-lg-2 d-none d-lg-block">
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
                            <a href="<?= url('/products?sort=hot') ?>" class="quick-link"><i class="fas fa-fire"></i>Hot Deals</a>
                            <a href="<?= url('/products?sort=top_seller') ?>" class="quick-link"><i class="fas fa-star"></i>Top Seller</a>
                            <a href="<?= url('/products?on_sale=1') ?>" class="quick-link"><i class="fas fa-gift"></i>Khuyến mãi</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 🔥 MAIN CONTENT 🔥 -->
            <div class="col-xl-8 col-lg-8">
                <!-- Categories Ultra -->
                <section class="section-vip mb-5" data-aos="fade-up">
                    <div class="section-header-vip">
                        <h2 class="section-title-vip">
                            <i class="fas fa-th-large text-vip me-3"></i>
                            <span>Khám Phá Danh Mục</span>
                        </h2>
                        <a href="<?= url('/categories') ?>" class="view-all-btn">
                            Xem tất cả <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                    <div class="categories-grid-ultra">
                        <?php foreach ($categories as $category): ?>
                            <a href="<?= url('/category/' . $category['slug']) ?>" class="category-card-ultra" data-aos="flip-left" data-aos-delay="100">
                                <div class="category-icon-ultra">
                                    <?php
                                    $iconClass = $category['icon'] ?? 'fa-folder';
                                    if (strpos($iconClass, 'fa-') === 0 && strpos($iconClass, ' ') === false) {
                                        $iconClass = ($iconClass === 'fa-bitcoin') ? 'fab ' . $iconClass : 'fas ' . $iconClass;
                                    }
                                    ?>
                                    <i class="<?= e($iconClass) ?>"></i>
                                </div>
                                <h6 class="category-name"><?= e($category['name']) ?></h6>
                                <div class="category-glow"></div>
                            </a>
                        <?php endforeach; ?>
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
                                <?php $isFav = in_array((int)$product['id'], $favoriteProductIds, true); ?>
                                <div class="product-media">
                                    <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" 
                                         alt="<?= e($product['name']) ?>" class="product-img-ultra">
                                    <div class="product-badges-ultra">
                                        <span class="badge-hot">🔥 HOT</span>
                                        <?php if ($product['sale_price']): ?>
                                            <span class="badge-sale">-<?= number_format((1 - $product['sale_price']/$product['price'])*100) ?>%</span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="fav-btn" data-id="<?= $product['id'] ?>" onclick="toggleFavorite(this, event)">
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
                                        <?php if ($product['sale_price']): ?>
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

                <!-- Sponsored VIP 🔥 -->
                <section class="section-vip mb-5" data-aos="fade-up">
                    <div class="section-header-vip">
                        <h2 class="section-title-vip">
                            <i class="fas fa-gem me-3 text-warning"></i>
                            <span>Sản Phẩm VIP</span>
                        </h2>
                        <div class="vip-label">
                            <i class="fas fa-crown"></i> GHIM TOP
                        </div>
                    </div>
                    <div class="products-grid-ultra">
                        <?php for ($i = 0; $i < 8; $i++): ?>
                            <div class="product-card-ultra <?= isset($sponsoredProducts[$i]) ? 'vip-sponsored' : 'vip-placeholder' ?>" data-aos="fade-up" data-aos-delay="50">
                                <?php if (isset($sponsoredProducts[$i])):
                                    $product = $sponsoredProducts[$i]; ?>
                                    <div class="product-media">
                                        <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" 
                                             alt="<?= e($product['name']) ?>" class="product-img-ultra">
                                        <div class="product-badges-ultra">
                                            <span class="badge-vip">⭐ VIP</span>
                                        </div>
                                        <div class="product-overlay">
                                            <a href="<?= url('/product/' . $product['slug']) ?>" class="btn-overlay">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="product-info">
                                        <h6><?= e(Helper::truncate($product['name'], 60)) ?></h6>
                                        <div class="seller-info">
                                            <span><?= e($product['seller_name']) ?></span>
                                        </div>
                                        <div class="product-price-section">
                                            <?php if ($product['sale_price']): ?>
                                                <div class="price-sale"><?= money($product['sale_price']) ?></div>
                                            <?php else: ?>
                                                <div class="price-normal"><?= money($product['price']) ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="placeholder-content">
                                        <div class="placeholder-icon">
                                            <i class="fas fa-crown"></i>
                                        </div>
                                        <h6>VIP SLOT</h6>
                                                <p class="small">Liên hệ admin ghim top</p>
                                                <div class="vip-contact">Telegram: @vip_support</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endfor; ?>
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
            <div class="col-xl-2 col-lg-2 d-none d-lg-block">
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
* { font-family: 'Inter', sans-serif; }
body { background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%); }

/* 🔥 HERO ULTRA 🔥 */
.hero-ultra {
    min-height: calc(100vh - 76px);
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    padding: 96px 0 72px;
}

.min-vh-90 { min-height: calc(100vh - 148px); }

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
        radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.8), transparent),
        radial-gradient(1px 1px at 90px 40px, #fff, transparent),
        radial-gradient(1px 1px at 130px 80px, rgba(255,255,255,0.6), transparent);
    background-repeat: repeat;
    background-size: 200px 100px;
    animation: particles-float 20s linear infinite;
    z-index: 1;
}

@keyframes particles-float {
    0% { transform: translateY(0px) rotate(0deg); }
    100% { transform: translateY(-100px) rotate(360deg); }
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
    background: linear-gradient(135deg, #fff 0%, #f0f9ff 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
    letter-spacing: 0;
    font-size: clamp(3rem, 8vw, 6.25rem);
}

.hero-subtitle {
    color: rgba(255,255,255,0.92);
    text-shadow: 0 2px 10px rgba(0,0,0,0.22);
}

.hero-badge .badge {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.search-container {
    max-width: 860px;
    margin: 0 auto;
    width: 100%;
}

.search-form-ultra {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    border-radius: 24px;
    border: 1px solid var(--glass-border);
    padding: 26px 30px 30px;
    box-shadow: var(--shadow-vip);
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    min-height: 58px;
    margin-bottom: 18px;
    border-radius: 16px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
}

.search-icon-left,
.search-filters-toggle {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,0.9);
    z-index: 2;
}

.search-icon-left { left: 18px; }
.search-filters-toggle { right: 18px; }

.search-filters-toggle i {
    font-size: 1rem;
}

.search-input-main {
    flex: 1;
    border: none;
    background: transparent;
    width: 100%;
    padding: 16px 52px 16px 54px;
    font-size: 1.1rem;
    color: #fff;
    outline: none;
}

.search-input-main::placeholder {
    color: rgba(255,255,255,0.7);
}

.search-filters-panel {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
    padding: 0;
    margin-bottom: 18px;
    scrollbar-width: none;
}

.search-filters-panel::-webkit-scrollbar { display: none; }

.filter-select {
    min-width: 0;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    border-radius: 12px;
    padding: 12px 16px;
}

.filter-select option {
    color: #1f2937;
}

.search-btn-ultra {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 240px;
    background: linear-gradient(45deg, #ff6b6b, #ffd93d, #6bcf7f, #4facfe);
    background-size: 300% 300%;
    border: none;
    padding: 18px 34px;
    color: #fff;
    font-weight: 700;
    font-size: 1.1rem;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    animation: gradient-shift 3s ease infinite;
}

@keyframes gradient-shift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.hero-stats {
    display: flex;
    gap: 40px;
    justify-content: center;
    flex-wrap: wrap;
}

.stat-item {
    text-align: center;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 900;
    background: linear-gradient(135deg, #fff 0%, #f0f9ff 100%);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    line-height: 1;
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
    background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
    animation: screen-glow 2s ease-in-out infinite;
}

@keyframes screen-glow {
    0%, 100% { opacity: 0; transform: translateX(-50%) translateY(-50%) rotate(0deg); }
    50% { opacity: 1; transform: translateX(0) translateY(0) rotate(180deg); }
}

/* 🔥 SECTIONS VIP 🔥 */
.section-vip {
    background: rgba(255,255,255,0.6);
    backdrop-filter: blur(16px);
    border-radius: 24px;
    padding: 40px;
    border: 1px solid rgba(255,255,255,0.2);
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

/* 🔥 CATEGORIES ULTRA 🔥 */
.categories-grid-ultra {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 20px;
}

.category-card-ultra {
    position: relative;
    aspect-ratio: 1;
    background: rgba(255,255,255,0.9);
    border-radius: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: inherit;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    overflow: hidden;
    border: 1px solid rgba(255,255,255,0.3);
}

.category-card-ultra::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    transition: left 0.6s;
}

.category-card-ultra:hover::before {
    left: 100%;
}

.category-card-ultra:hover {
    transform: translateY(-16px) scale(1.05);
    box-shadow: var(--shadow-glow), var(--shadow-vip);
    background: linear-gradient(135deg, rgba(139,92,246,0.1), rgba(168,85,247,0.1));
}

.category-icon-ultra {
    width: 80px;
    height: 80px;
    background: var(--vip-gradient);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 16px;
    transition: all 0.3s ease;
    box-shadow: 0 10px 30px rgba(139,92,246,0.4);
}

.category-card-ultra:hover .category-icon-ultra {
    transform: scale(1.1) rotate(10deg);
}

.category-icon-ultra i {
    font-size: 2rem;
    color: white;
}

.category-name {
    font-weight: 700;
    font-size: 1rem;
    margin: 0;
}

/* 🔥 PRODUCTS ULTRA 🔥 */
.products-grid-ultra {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    gap: 24px;
}

.product-card-ultra {
    background: rgba(255,255,255,0.95);
    border-radius: 24px;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border: 1px solid rgba(255,255,255,0.3);
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

.badge-hot, .badge-vip, .badge-sale {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 700;
    color: white;
}

.badge-hot { background: linear-gradient(45deg, #ff6b6b, #ff8e8e); }
.badge-vip { background: linear-gradient(45deg, var(--vip-gold), #ffed4a); color: #1a1a1a; }
.badge-sale { background: linear-gradient(45deg, #10b981, #34d399); }

.fav-btn {
    position: absolute;
    top: 16px;
    right: 16px;
    width: 44px;
    height: 44px;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    z-index: 3;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.fav-btn:hover {
    background: #fff;
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(139,92,246,0.95), rgba(168,85,247,0.95));
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

.btn-overlay, .btn-overlay-buy {
    padding: 12px 24px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: 2px solid rgba(255,255,255,0.3);
}

.btn-overlay { background: rgba(255,255,255,0.2); color: white; }
.btn-overlay-buy { 
    background: rgba(255,255,255,0.9); 
    color: var(--vip-purple); 
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.btn-overlay:hover, .btn-overlay-buy:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
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

.price-sale, .price-normal {
    font-size: 1.4rem;
    font-weight: 800;
}

.price-sale { color: #ef4444; }
.price-normal { color: var(--vip-purple); }

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
    box-shadow: 0 4px 12px rgba(255,215,0,0.4);
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
    background: rgba(255,255,255,0.9);
    padding: 24px;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.3);
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
    background: rgba(139,92,246,0.1);
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
    box-shadow: 0 4px 15px rgba(255,215,0,0.4);
}

/* 🔥 CTA BUTTON 🔥 */
.cta-btn-ultra {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: var(--vip-gradient);
    color: white;
    padding: 20px 48px;
    border-radius: 50px;
    font-size: 1.2rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: var(--shadow-vip);
    transition: all 0.4s ease;
    border: 2px solid transparent;
}

.cta-btn-ultra:hover {
    transform: translateY(-4px) scale(1.05);
    box-shadow: 0 30px 60px -12px rgba(139,92,246,0.5);
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

/* 🔥 RESPONSIVE PERFECTION 🔥 */
@media (max-width: 991.98px) {
    .hero-ultra { min-height: auto; padding: 78px 0 64px; }
    .hero-title { font-size: 3.5rem; }
    .search-filters-panel { 
        grid-template-columns: 1fr;
        display: grid;
    }
    .search-filters-toggle { display: block; }
    
    .products-grid-ultra {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }
    
    .section-vip { padding: 32px 24px; }
}

@media (max-width: 768px) {
    .hero-title { font-size: 2.8rem; }
    .search-form-ultra { padding: 20px; border-radius: 20px; }
    .search-btn-ultra { width: 100%; }
    .products-grid-ultra {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 16px;
    }
    .categories-grid-ultra {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 16px;
    }
}

@media (max-width: 576px) {
    .hero-title { font-size: 2.2rem; }
    .search-input-main { padding: 20px 20px 20px 50px; font-size: 1rem; }
    .search-btn-ultra { 
        padding: 18px 30px; 
        font-size: 1rem;
        border-radius: 12px;
    }
    .products-grid-ultra { grid-template-columns: 1fr; }
}

/* 🔥 SMOOTH SCROLL & INTERACTIONS 🔥 */
html { scroll-behavior: smooth; }
* { scrollbar-width: thin; scrollbar-color: var(--vip-purple) transparent; }
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { 
    background: linear-gradient(var(--vip-purple), #a855f7);
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover { background: var(--vip-purple); }

/* AOS Animations */
[data-aos] { transition-duration: 0.8s; }
[data-aos][data-aos][data-aos-loaded="true"] { transition-duration: 0.6s; }
</style>

<!-- AOS Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>AOS.init({ once: true, duration: 800, easing: 'ease-out-cubic' });</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
