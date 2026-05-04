<?php
ob_start();
$categoryProfiles = [];
foreach ($categories as $cat) {
    $categoryProfiles[$cat['id']] = Helper::getCategoryProductProfile($cat);
    $categoryProfiles[$cat['id']]['icon_class'] = $cat['icon'] ?? 'fa-folder';
    $categoryProfiles[$cat['id']]['cat_name'] = $cat['name'];
}
?>

<div class="seller-form-shell">
    <div class="row align-items-center mb-4 gy-3">
        <div class="col-12 col-md-auto me-auto text-center text-md-start">
            <h2 class="mb-1 fw-bold">Thêm sản phẩm mới</h2>
            <div class="text-muted small">Chọn đúng danh mục để hệ thống gợi ý phù hợp.</div>
        </div>
        <div class="col-12 col-md-auto text-center">
            <a href="<?= url('/seller/products') ?>" class="btn btn-outline-secondary btn-sm px-3">
                <i class="fas fa-arrow-left me-1"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- ========== STEP 1: CHỌN DANH MỤC ========== -->
    <div id="stepCategory" class="seller-form-card p-4 p-lg-5">
        <div class="text-center mb-4">
            <span class="step-badge">Bước 1</span>
            <h4 class="fw-bold mt-2 mb-1">Bạn muốn bán gì?</h4>
            <p class="text-muted mb-0">Chọn danh mục phù hợp nhất với sản phẩm của bạn</p>
        </div>

        <div class="category-picker-grid">
            <?php foreach ($categories as $cat):
                $profile = $categoryProfiles[$cat['id']];
            ?>
            <div class="category-pick-card" data-cat-id="<?= $cat['id'] ?>" tabindex="0">
                <div class="category-pick-icon">
                    <i class="fas <?= e($cat['icon'] ?? 'fa-folder') ?>"></i>
                </div>
                <div class="category-pick-name"><?= e($cat['name']) ?></div>
                <div class="category-pick-desc"><?= e($profile['description'] ?? '') ?></div>
                <div class="category-pick-check"><i class="fas fa-check"></i></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ========== STEP 2: FORM TẠO SẢN PHẨM (ẩn mặc định) ========== -->
    <form action="<?= url('/seller/products/store') ?>" method="POST" enctype="multipart/form-data"
          id="productForm" class="seller-form-card p-4 p-lg-5 mt-4" style="display:none;">
        <?= csrf_field() ?>
        <input type="hidden" name="category_id" id="selectedCategoryId" value="">

        <!-- Header danh mục đã chọn -->
        <div class="selected-category-banner mb-4" id="selectedCatBanner">
            <div class="d-flex align-items-center gap-3">
                <span class="selected-cat-icon"><i id="selCatIcon" class="fas fa-folder"></i></span>
                <div>
                    <div class="small text-white-50">Danh mục đã chọn</div>
                    <div class="h5 mb-0 fw-bold" id="selCatName"></div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-light" id="btnChangeCat">
                <i class="fas fa-exchange-alt me-1"></i> Đổi danh mục
            </button>
        </div>

        <div class="text-center mb-4">
            <span class="step-badge">Bước 2</span>
            <h4 class="fw-bold mt-2 mb-1" id="formTitle">Nhập thông tin sản phẩm</h4>
            <p class="text-muted mb-0" id="formSubtitle"></p>
        </div>

        <div class="row g-4">
            <!-- CỘT TRÁI: Thông tin chính -->
            <div class="col-lg-8">
                <div class="seller-form-section mb-4">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-box-open"></i></span>
                        <span>Thông tin cơ bản</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên sản phẩm *</label>
                        <input type="text" name="name" class="form-control" required
                               id="inputName" placeholder="">
                        <div class="form-text" id="nameHelp"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" id="lblShortDesc">Mô tả ngắn</label>
                        <textarea name="short_description" class="form-control" rows="3"
                                  id="inputShortDesc" placeholder=""></textarea>
                    </div>

                    <div>
                        <label class="form-label fw-semibold" id="lblDesc">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="8"
                                  id="inputDesc" placeholder=""></textarea>
                    </div>
                </div>

                <!-- Section cấu hình theo danh mục -->
                <div class="seller-form-section" id="sectionConfig">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-sliders"></i></span>
                        <span id="configSectionTitle">Cấu hình sản phẩm</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Loại sản phẩm *</label>
                            <select name="product_type" id="productTypeSelect" class="form-select" required>
                                <option value="key">Key/License</option>
                                <option value="account">Account</option>
                                <option value="file">File Download</option>
                                <option value="link">Link dịch vụ</option>
                                <option value="service">Dịch vụ thủ công</option>
                            </select>
                            <div class="form-text" id="productTypeHelp"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="dynamic-note-box h-100">
                                <label class="form-label fw-semibold mb-2" id="noteLabel">Ghi chú</label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="require_note"
                                           id="require_note" value="1">
                                    <label class="form-check-label fw-semibold" for="require_note">
                                        Bắt buộc người mua nhập ghi chú
                                    </label>
                                </div>
                                <div class="small text-muted" id="noteHelp"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Bảo hành (ngày) *</label>
                            <input type="number" name="warranty_days" class="form-control" min="0" value="0" required>
                            <div class="form-text">Nhập 0 nếu sản phẩm không bảo hành.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Điều kiện bảo hành *</label>
                            <textarea name="warranty_note" class="form-control" rows="2" required placeholder="VD: Bảo hành 30 ngày nếu account bị khóa. Nếu không bảo hành, ghi: Không bảo hành."></textarea>
                            <div class="form-text">Admin se doc noi dung nay truoc khi duyet san pham.</div>
                        </div>
                    </div>

                    <!-- Dynamic tips per category -->
                    <div class="category-tips-box mt-3" id="categoryTips"></div>
                </div>
            </div>

            <!-- CỘT PHẢI -->
            <div class="col-lg-4">
                <div class="seller-form-section mb-4">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-image"></i></span>
                        <span>Hình ảnh và giá</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ảnh sản phẩm</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" id="lblPrice">Giá bán *</label>
                        <input type="number" name="price" class="form-control" required min="0" step="1000"
                               id="inputPrice" placeholder="">
                        <div class="form-text" id="priceHelp"></div>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Giá khuyến mãi</label>
                        <input type="number" name="sale_price" class="form-control" min="0" step="1000">
                    </div>
                </div>

                <div class="seller-form-section">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-warehouse"></i></span>
                        <span>Nhập kho sau khi tạo</span>
                    </div>

                    <div class="stock-mode-card">
                        <div class="fw-semibold mb-2" id="stockModeTitle"></div>
                        <div class="small text-muted mb-3" id="stockModeHelp"></div>
                        <div class="stock-item-preview">
                            <div class="small text-uppercase text-muted mb-2">Preview cách nhập hàng</div>
                            <div class="fw-semibold" id="stockModeLabel"></div>
                            <div class="small text-muted mt-2" id="stockModePlaceholder"></div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-circle-info me-1"></i> Sản phẩm sẽ được tạo trước, sau đó bạn nhập kho và chờ admin duyệt.
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-6 col-md-auto ms-md-auto order-2 order-md-1">
                <button type="button" class="btn btn-outline-secondary w-100" id="btnBackStep1">
                    <i class="fas fa-arrow-left me-1"></i> Đổi danh mục
                </button>
            </div>
            <div class="col-12 col-md-auto order-1 order-md-2">
                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold px-md-5">
                    <i class="fas fa-save me-1"></i> Tạo sản phẩm ngay
                </button>
            </div>
        </div>
    </form>
</div>

<style>
.step-badge {
    display: inline-block;
    padding: 0.3rem 1rem;
    border-radius: 999px;
    background: linear-gradient(135deg, var(--brand-main), var(--brand-dark));
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

/* Category Picker Grid */
.category-picker-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 16px;
    max-width: 900px;
    margin: 0 auto;
}

.category-pick-card {
    position: relative;
    border: 2px solid #e9ecf4;
    border-radius: 18px;
    padding: 1.5rem 1.2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(.4,0,.2,1);
    background: #fff;
    outline: none;
}

.category-pick-card:hover {
    border-color: var(--brand-light);
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(139,92,246,0.13);
}

.category-pick-card.active {
    border-color: var(--brand-main);
    background: linear-gradient(180deg, #f5f0ff 0%, #fff 100%);
    box-shadow: 0 8px 24px rgba(139,92,246,0.18);
}

.category-pick-card.active .category-pick-check {
    opacity: 1;
    transform: scale(1);
}

.category-pick-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    margin-bottom: 0.75rem;
    background: rgba(139,92,246,0.1);
    color: var(--brand-main);
    transition: all 0.3s ease;
}

.category-pick-card.active .category-pick-icon {
    background: var(--brand-main);
    color: #fff;
}

.category-pick-name {
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 0.35rem;
    color: #1f2937;
}

.category-pick-desc {
    font-size: 0.8rem;
    color: #6b7280;
    line-height: 1.4;
}

.category-pick-check {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--brand-main);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.3s cubic-bezier(.4,0,.2,1);
}

/* Selected Category Banner */
.selected-category-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1rem 1.25rem;
    border-radius: 16px;
    background: linear-gradient(135deg, #1d4ed8 0%, #7c3aed 55%, #a855f7 100%);
    color: #fff;
    box-shadow: 0 12px 32px rgba(124,58,237,0.22);
}

.selected-cat-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: rgba(255,255,255,0.16);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}

/* Category Tips */
.category-tips-box {
    border-radius: 14px;
    padding: 1rem 1.1rem;
    background: #f8f5ff;
    border: 1px solid #e9ddff;
}

.category-tips-box .tip-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 0.88rem;
    color: #4b3a75;
    padding: 0.3rem 0;
}

.category-tips-box .tip-item i {
    color: var(--brand-main);
    margin-top: 2px;
    flex-shrink: 0;
}

/* Animations */
#productForm {
    animation: slideUp 0.5s cubic-bezier(.4,0,.2,1);
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    .seller-form-card { padding: 1.5rem !important; }
    .category-picker-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .category-pick-card { padding: 1rem 0.75rem; border-radius: 14px; }
    .category-pick-icon { width: 44px; height: 44px; font-size: 1.1rem; margin-bottom: 0.5rem; }
    .category-pick-name { font-size: 0.9rem; }
    .category-pick-desc { display: none; }
    
    .selected-category-banner { 
        padding: 0.85rem; 
        justify-content: center; 
        text-align: center;
        gap: 0.75rem;
    }
    .selected-category-banner .d-flex { flex-direction: column; width: 100%; }
    .selected-cat-icon { width: 40px; height: 40px; font-size: 1rem; }
    .selected-category-banner #btnChangeCat { width: 100%; }
}

@media (max-width: 480px) {
    .category-picker-grid { gap: 8px; }
    .category-pick-card { padding: 0.85rem 0.5rem; }
}
</style>

<script>
(function() {
    const profiles = <?= json_encode($categoryProfiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const productTypeLabels = {
        key: 'Key/License',
        account: 'Account',
        file: 'File Download',
        link: 'Link dịch vụ',
        service: 'Dịch vụ thủ công'
    };

    // Category-specific placeholder configs
    const categoryFormConfig = {
        account: {
            namePlaceholder: 'VD: Tài khoản Netflix Premium 1 tháng',
            nameHelp: 'Đặt tên rõ ràng: loại tài khoản + thời hạn (nếu có)',
            shortDescPlaceholder: 'Tài khoản chính chủ, bảo hành 30 ngày, hỗ trợ đổi pass...',
            descPlaceholder: 'Mô tả chi tiết: loại tài khoản, cách dùng, điều kiện bảo hành, cách nhận hàng...',
            priceHelp: 'Giá cho 1 tài khoản/combo',
            configTitle: 'Cấu hình tài khoản',
            formTitle: 'Nhập thông tin tài khoản bán',
            formSubtitle: 'Hệ thống sẽ tối ưu form nhập kho cho dạng account/email'
        },
        software: {
            namePlaceholder: 'VD: Key bản quyền Windows 11 Pro Lifetime',
            nameHelp: 'Ghi rõ tên phần mềm + phiên bản + thời hạn',
            shortDescPlaceholder: 'Key chính hãng, kích hoạt vĩnh viễn, hỗ trợ cài đặt...',
            descPlaceholder: 'Mô tả: phiên bản hỗ trợ, cách kích hoạt, hướng dẫn cài đặt, yêu cầu hệ thống...',
            priceHelp: 'Giá cho 1 key/license',
            configTitle: 'Cấu hình phần mềm',
            formTitle: 'Nhập thông tin phần mềm / công cụ số',
            formSubtitle: 'Kho hàng sẽ ưu tiên nhập key, link tải hoặc mã kích hoạt'
        },
        service: {
            namePlaceholder: 'VD: Gói tăng 1000 follow Instagram thật',
            nameHelp: 'Đặt tên rõ: loại dịch vụ + số lượng/quy mô',
            shortDescPlaceholder: 'Dịch vụ uy tín, xử lý trong 24h, bảo hành 30 ngày...',
            descPlaceholder: 'Mô tả dịch vụ: cách thức thực hiện, thời gian hoàn thành, yêu cầu từ khách (link profile, tài khoản)...',
            priceHelp: 'Giá cho 1 gói/lần dịch vụ',
            configTitle: 'Cấu hình dịch vụ',
            formTitle: 'Nhập thông tin dịch vụ',
            formSubtitle: 'Hệ thống sẽ yêu cầu người mua gửi thông tin trước khi thanh toán'
        },
        document: {
            namePlaceholder: 'VD: Ebook "100 Bí Quyết SEO" - PDF',
            nameHelp: 'Ghi tên tài liệu + định dạng file',
            shortDescPlaceholder: 'Tài liệu chất lượng cao, format PDF, giao hàng tự động...',
            descPlaceholder: 'Mô tả nội dung tài liệu, số trang, ngôn ngữ, có update không, preview...',
            priceHelp: 'Giá cho 1 file/bộ tài liệu',
            configTitle: 'Cấu hình tài liệu',
            formTitle: 'Nhập thông tin tài liệu / ebook',
            formSubtitle: 'Kho hàng sẽ cho phép upload file để giao tự động cho khách'
        },
        generic: {
            namePlaceholder: 'VD: Tên sản phẩm số của bạn',
            nameHelp: 'Đặt tên ngắn gọn, dễ hiểu',
            shortDescPlaceholder: 'Điểm mạnh, quyền lợi hoặc kết quả người mua nhận được',
            descPlaceholder: 'Mô tả rõ cách dùng, điều kiện sử dụng, nội dung giao hàng...',
            priceHelp: 'Giá cho 1 đơn vị sản phẩm',
            configTitle: 'Cấu hình sản phẩm',
            formTitle: 'Nhập thông tin sản phẩm',
            formSubtitle: 'Cấu hình linh hoạt cho key, account, file hoặc link'
        }
    };

    const stepCategory = document.getElementById('stepCategory');
    const productForm = document.getElementById('productForm');
    const cards = document.querySelectorAll('.category-pick-card');
    const hiddenCatId = document.getElementById('selectedCategoryId');
    const productTypeSelect = document.getElementById('productTypeSelect');
    const requireNote = document.getElementById('require_note');

    let selectedCatId = null;

    // Click category card
    cards.forEach(card => {
        card.addEventListener('click', () => selectCategory(card.dataset.catId));
        card.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                selectCategory(card.dataset.catId);
            }
        });
    });

    // Change category buttons
    document.getElementById('btnChangeCat').addEventListener('click', showStep1);
    document.getElementById('btnBackStep1').addEventListener('click', showStep1);

    function selectCategory(catId) {
        selectedCatId = catId;
        hiddenCatId.value = catId;

        // Highlight card
        cards.forEach(c => c.classList.remove('active'));
        document.querySelector(`[data-cat-id="${catId}"]`).classList.add('active');

        const profile = profiles[catId];
        if (!profile) return;

        // Small delay for visual feedback
        setTimeout(() => {
            applyProfile(profile);
            showStep2();
        }, 300);
    }

    function showStep1() {
        productForm.style.display = 'none';
        stepCategory.style.display = '';
        stepCategory.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function showStep2() {
        stepCategory.style.display = 'none';
        productForm.style.display = '';
        productForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function applyProfile(profile) {
        const key = profile.key || 'generic';
        const cfg = categoryFormConfig[key] || categoryFormConfig.generic;

        // Banner
        document.getElementById('selCatIcon').className = 'fas ' + (profile.icon_class || profile.icon || 'fa-folder');
        document.getElementById('selCatName').textContent = profile.cat_name || profile.label;

        // Form titles
        document.getElementById('formTitle').textContent = cfg.formTitle;
        document.getElementById('formSubtitle').textContent = cfg.formSubtitle;

        // Input placeholders & helps
        document.getElementById('inputName').placeholder = cfg.namePlaceholder;
        document.getElementById('nameHelp').textContent = cfg.nameHelp;
        document.getElementById('inputShortDesc').placeholder = cfg.shortDescPlaceholder;
        document.getElementById('inputDesc').placeholder = cfg.descPlaceholder;
        document.getElementById('priceHelp').textContent = cfg.priceHelp;

        // Config section
        document.getElementById('configSectionTitle').textContent = cfg.configTitle;

        // Product type filter
        const allowed = profile.allowed_product_types || [];
        Array.from(productTypeSelect.options).forEach(opt => {
            opt.hidden = !allowed.includes(opt.value);
        });
        if (!allowed.includes(productTypeSelect.value)) {
            productTypeSelect.value = profile.suggested_product_type;
        }
        document.getElementById('productTypeHelp').textContent =
            'Hỗ trợ: ' + allowed.map(t => productTypeLabels[t] || t).join(', ');

        // Note config
        document.getElementById('noteLabel').textContent = profile.note_label;
        document.getElementById('noteHelp').textContent = profile.note_help;
        if (!requireNote.dataset.userTouched) {
            requireNote.checked = !!profile.recommended_require_note;
        }

        // Stock preview
        document.getElementById('stockModeTitle').textContent = profile.label;
        document.getElementById('stockModeHelp').textContent = profile.stock_help;
        document.getElementById('stockModeLabel').textContent = profile.stock_label;
        document.getElementById('stockModePlaceholder').textContent = profile.stock_placeholder;

        // Category tips
        buildTips(profile);
    }

    function buildTips(profile) {
        const tips = [];
        tips.push(`Loại gợi ý: <strong>${productTypeLabels[profile.suggested_product_type] || profile.suggested_product_type}</strong>`);
        tips.push(profile.stock_help);
        if (profile.recommended_require_note) {
            tips.push('Nên bật ghi chú để người mua gửi thông tin xử lý đơn.');
        } else {
            tips.push('Chỉ bật ghi chú nếu bạn cần thêm dữ liệu từ khách.');
        }

        const box = document.getElementById('categoryTips');
        box.innerHTML = '<div class="fw-semibold mb-2" style="font-size:0.9rem"><i class="fas fa-lightbulb text-warning me-1"></i> Gợi ý cho danh mục này</div>' +
            tips.map(t => `<div class="tip-item"><i class="fas fa-check-circle"></i><span>${t}</span></div>`).join('');
    }

    // Track user touch on require_note
    requireNote.addEventListener('change', function() {
        this.dataset.userTouched = 'true';
    });
})();
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Thêm sản phẩm';
require_once __DIR__ . '/../layouts/seller.php';
?>
