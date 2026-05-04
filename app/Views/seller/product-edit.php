<?php
ob_start();
$categoryProfiles = [];
foreach ($categories as $cat) {
    $categoryProfiles[$cat['id']] = Helper::getCategoryProductProfile($cat);
}
?>

<div class="seller-form-shell">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold">Chỉnh sửa sản phẩm</h2>
            <div class="text-muted">Cập nhật danh mục, kiểu bán và cấu hình giao hàng cho phù hợp.</div>
        </div>
        <a href="<?= url('/seller/products') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>

    <form action="<?= url('/seller/products/update/' . $product['id']) ?>" method="POST" enctype="multipart/form-data" class="seller-form-card p-4 p-lg-5">
        <?= csrf_field() ?>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="seller-form-section mb-4">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-pen-ruler"></i></span>
                        <span>Thông tin sản phẩm</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên sản phẩm *</label>
                        <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Danh mục *</label>
                        <select name="category_id" id="categorySelect" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= e($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mô tả ngắn</label>
                        <textarea name="short_description" class="form-control" rows="3"><?= e($product['short_description']) ?></textarea>
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control editor" rows="8"><?= e($product['description']) ?></textarea>
                    </div>
                </div>

                <div class="seller-form-section">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-sliders"></i></span>
                        <span>Cấu hình theo danh mục</span>
                    </div>

                    <div class="category-profile-card" id="categoryProfileCard">
                        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                            <div class="d-flex align-items-center gap-3">
                                <span class="profile-icon"><i id="profileIcon" class="fas <?= e($categoryProfile['icon'] ?? 'fa-layer-group') ?>"></i></span>
                                <div>
                                    <div class="small text-white-50 mb-1">Mẫu quản lý hiện tại</div>
                                    <div class="h5 mb-1 fw-bold" id="profileTitle"><?= e($categoryProfile['label'] ?? 'Sản phẩm số') ?></div>
                                    <div class="mb-0 text-white-50" id="profileDescription"><?= e($categoryProfile['description'] ?? '') ?></div>
                                </div>
                            </div>
                            <span class="profile-chip" id="profileChip">
                                <i class="fas fa-shapes"></i>
                                <span><?= e($product['product_type'] ?? 'key') ?></span>
                            </span>
                        </div>

                        <div class="category-profile-points">
                            <div class="category-profile-point">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span id="profilePoint1"></span>
                            </div>
                            <div class="category-profile-point">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span id="profilePoint2"></span>
                            </div>
                            <div class="category-profile-point">
                                <i class="fas fa-check-circle mt-1"></i>
                                <span id="profilePoint3"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Loại sản phẩm *</label>
                            <select name="product_type" id="productTypeSelect" class="form-select" required>
                                <option value="key" <?= ($product['product_type'] ?? '') === 'key' ? 'selected' : '' ?>>Key/License</option>
                                <option value="account" <?= ($product['product_type'] ?? '') === 'account' ? 'selected' : '' ?>>Account</option>
                                <option value="file" <?= ($product['product_type'] ?? '') === 'file' ? 'selected' : '' ?>>File Download</option>
                                <option value="link" <?= ($product['product_type'] ?? '') === 'link' ? 'selected' : '' ?>>Link dịch vụ</option>
                                <option value="service" <?= ($product['product_type'] ?? '') === 'service' ? 'selected' : '' ?>>Dịch vụ thủ công</option>
                            </select>
                            <div class="form-text" id="productTypeHelp"></div>
                        </div>

                        <div class="col-md-6">
                            <div class="dynamic-note-box h-100">
                                <label class="form-label fw-semibold mb-2" id="noteLabel"></label>
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="require_note" id="require_note" value="1" <?= ($product['require_note'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="require_note">Bắt buộc người mua nhập ghi chú</label>
                                </div>
                                <div class="small text-muted" id="noteHelp"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Bảo hành (ngày) *</label>
                            <input type="number" name="warranty_days" class="form-control" min="0" required value="<?= (int)($product['warranty_days'] ?? 0) ?>">
                            <div class="form-text">Nhập 0 nếu sản phẩm không bảo hành.</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Điều kiện bảo hành *</label>
                            <textarea name="warranty_note" class="form-control" rows="2" required><?= e($product['warranty_note'] ?? 'Không bảo hành') ?></textarea>
                            <div class="form-text">Admin se doc noi dung nay truoc khi duyet san pham.</div>
                        </div>
                    </div>
                </div>

                <!-- 🔥 SECTION: GÓI SẢN PHẨM (VARIANTS) 🔥 -->
                <div class="seller-form-section mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="seller-section-title mb-0">
                            <span class="seller-section-icon"><i class="fas fa-layer-group"></i></span>
                            <span>Gói sản phẩm & Giá (Tùy chọn)</span>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="enableVariants" name="has_variants" value="1" <?= !empty($variants) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold text-primary" for="enableVariants">Sản phẩm có nhiều gói</label>
                        </div>
                    </div>
                    
                    <div id="variantsContainer" style="display: <?= !empty($variants) ? 'block' : 'none' ?>;">
                        <div class="alert alert-light border border-primary border-opacity-25 mb-3">
                            <small class="text-muted"><i class="fas fa-info-circle me-1"></i> Bạn có thể tạo các gói như: 1 Tháng, 3 Tháng, Vĩnh viễn... với giá khác nhau.</small>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle" id="variantsTable" style="min-width: 900px;">
                                <thead class="text-muted small text-uppercase">
                                    <tr>
                                        <th style="width: 25%;">Tên gói</th>
                                        <th style="width: 20%;">Giá / Sale</th>
                                        <th style="width: 15%;">Tồn / Nạp</th>
                                        <th style="width: 30%;">Nội dung bàn giao</th>
                                        <th style="width: 5%;" class="text-center">Ghi chú?</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Variants will be added here -->
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm w-100 mt-2 border-dashed" id="btnAddVariant">
                            <i class="fas fa-plus-circle me-1"></i> Thêm gói mới
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="seller-form-section mb-4">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-image"></i></span>
                        <span>Ảnh và giá bán</span>
                    </div>

                    <?php if ($product['thumbnail']): ?>
                        <div class="text-center mb-3">
                            <img src="<?= asset($product['thumbnail']) ?>" class="img-thumbnail" style="max-height: 220px; border-radius: 16px;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Đổi ảnh sản phẩm</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>

                    <div class="mb-3" id="mainPriceContainer" style="display: <?= !empty($variants) ? 'none' : 'block' ?>;">
                        <label class="form-label fw-semibold">Giá gốc *</label>
                        <input type="number" name="price" id="inputPrice" class="form-control" value="<?= $product['price'] ?>" required min="0" step="1000">
                    </div>

                    <div id="mainSalePriceContainer" style="display: <?= !empty($variants) ? 'none' : 'block' ?>;">
                        <label class="form-label fw-semibold">Giá khuyến mãi</label>
                        <input type="number" name="sale_price" id="inputSalePrice" class="form-control" value="<?= $product['sale_price'] ?>" min="0" step="1000">
                    </div>

                    <!-- Ô nhập giá hiển thị -->
                    <div class="mt-3" id="displayPriceContainer" style="display: <?= !empty($variants) ? 'block' : 'none' ?>;">
                        <label class="form-label fw-semibold text-primary">Giá hiển thị giao diện (Ví dụ: 1k - 20k)</label>
                        <input type="text" name="display_price" class="form-control" id="inputDisplayPrice" 
                               value="<?= e($product['display_price'] ?? '') ?>"
                               placeholder="VD: 1.000đ - 20.000đ hoặc Từ 5.000đ">
                        <div class="form-text">Dùng để hiển thị ngoài trang chủ khi sản phẩm có nhiều gói.</div>
                    </div>
                </div>

                <div class="seller-form-section mb-4">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-chart-line"></i></span>
                        <span>Trạng thái</span>
                    </div>

                    <select name="status" class="form-select">
                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Đang bán</option>
                        <option value="hidden" <?= $product['status'] === 'hidden' ? 'selected' : '' ?>>Ẩn / ngừng bán</option>
                        <?php if ($product['status'] === 'pending'): ?>
                            <option value="pending" selected>Chờ duyệt</option>
                        <?php endif; ?>
                        <?php if ($product['status'] === 'rejected'): ?>
                            <option value="rejected" selected>Bị từ chối</option>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="seller-form-section">
                    <div class="seller-section-title">
                        <span class="seller-section-icon"><i class="fas fa-warehouse"></i></span>
                        <span>Nhập kho</span>
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

                    <div id="quickStockContainer" style="display: <?= empty($variants) ? 'block' : 'none' ?>;">
                        <div class="mt-3 p-3 bg-light rounded-3 border">
                            <div class="fw-bold small text-primary mb-2"><i class="fas fa-bolt me-1"></i> Nạp hàng nhanh</div>
                            <div class="mb-2">
                                <label class="form-label small mb-1">Số lượng nạp thêm</label>
                                <input type="number" name="main_stock_add" class="form-control form-control-sm" placeholder="VD: 10" min="0">
                            </div>
                            <div>
                                <label class="form-label small mb-1">Nội dung bàn giao</label>
                                <textarea name="main_stock_content" class="form-control form-control-sm" rows="2" placeholder="Nội dung gửi khách khi nạp hàng"></textarea>
                            </div>
                        </div>
                    </div>

                    <a href="<?= url('/seller/products/stock/' . $product['id']) ?>" class="btn btn-outline-primary w-100 mt-3 btn-sm">
                        <i class="fas fa-warehouse me-1"></i> Mở quản lý kho chuyên sâu
                    </a>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Cập nhật sản phẩm
            </button>
        </div>
    </form>
</div>

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

        const categorySelect = document.getElementById('categorySelect');
        const productTypeSelect = document.getElementById('productTypeSelect');

        function renderProfile(profile) {
            document.getElementById('profileIcon').className = `fas ${profile.icon}`;
            document.getElementById('profileTitle').textContent = profile.label;
            document.getElementById('profileDescription').textContent = profile.description;
            document.getElementById('profileChip').innerHTML = `<i class="fas fa-shapes"></i><span>${productTypeLabels[profile.suggested_product_type] || profile.suggested_product_type}</span>`;
            document.getElementById('profilePoint1').textContent = `Loại gợi ý: ${productTypeLabels[profile.suggested_product_type] || profile.suggested_product_type}.`;
            document.getElementById('profilePoint2').textContent = profile.stock_help;
            document.getElementById('profilePoint3').textContent = profile.recommended_require_note
                ? 'Nên bật ghi chú để người mua gửi dữ liệu cần xử lý trước khi thanh toán.'
                : 'Có thể giao ngay, chỉ nên bật ghi chú nếu bạn cần thêm thông tin từ khách.';

            document.getElementById('productTypeHelp').textContent = `Danh mục này hỗ trợ: ${profile.allowed_product_types.map(type => productTypeLabels[type] || type).join(', ')}.`;
            document.getElementById('noteLabel').textContent = profile.note_label;
            document.getElementById('noteHelp').textContent = profile.note_help;
            document.getElementById('stockModeTitle').textContent = profile.label;
            document.getElementById('stockModeHelp').textContent = profile.stock_help;
            document.getElementById('stockModeLabel').textContent = profile.stock_label;
            document.getElementById('stockModePlaceholder').textContent = profile.stock_placeholder;

            const currentValue = productTypeSelect.value;
            Array.from(productTypeSelect.options).forEach(option => {
                option.hidden = !profile.allowed_product_types.includes(option.value);
            });
            if (!profile.allowed_product_types.includes(currentValue)) {
                productTypeSelect.value = profile.suggested_product_type;
            }
        }

        categorySelect.addEventListener('change', function() {
            const profile = profiles[this.value];
            if (profile) {
                renderProfile(profile);
            }
        });

        if (categorySelect.value && profiles[categorySelect.value]) {
            renderProfile(profiles[categorySelect.value]);
        }

        // --- LOGIC GÓI SẢN PHẨM (VARIANTS) ---
        const enableVariants = document.getElementById('enableVariants');
        const variantsContainer = document.getElementById('variantsContainer');
        const variantsTableBody = document.querySelector('#variantsTable tbody');
        const btnAddVariant = document.getElementById('btnAddVariant');
        const inputPrice = document.getElementById('inputPrice');
        const inputSalePrice = document.getElementById('inputSalePrice');
        const mainPriceWrapper = document.getElementById('mainPriceContainer');
        const mainSalePriceWrapper = document.getElementById('mainSalePriceContainer');
        const displayPriceWrapper = document.getElementById('displayPriceContainer');
        const quickStockWrapper = document.getElementById('quickStockContainer');

        const existingVariants = <?= json_encode($variants) ?>;

        enableVariants.addEventListener('change', function() {
            if (this.checked) {
                variantsContainer.style.display = 'block';
                mainPriceWrapper.style.display = 'none';
                mainSalePriceWrapper.style.display = 'none';
                displayPriceWrapper.style.display = 'block';
                quickStockWrapper.style.display = 'none';
                inputPrice.required = false;
                if (variantsTableBody.children.length === 0) addVariantRow();
            } else {
                variantsContainer.style.display = 'none';
                mainPriceWrapper.style.display = 'block';
                mainSalePriceWrapper.style.display = 'block';
                displayPriceWrapper.style.display = 'none';
                quickStockWrapper.style.display = 'block';
                inputPrice.required = true;
            }
        });

        // Sync global require_note with variants
        const requireNoteGlobal = document.getElementById('require_note');
        requireNoteGlobal.addEventListener('change', function() {
            const isChecked = this.checked;
            if (!isChecked) {
                // If global is turned off, uncheck all variant notes
                const variantNoteCheckboxes = variantsTableBody.querySelectorAll('input[type="checkbox"][name*="[require_note]"]');
                variantNoteCheckboxes.forEach(cb => cb.checked = false);
            }
        });

        function addVariantRow(data = null) {
            const index = variantsTableBody.children.length;
            const tr = document.createElement('tr');
            const isGlobalNoteOn = requireNoteGlobal.checked;
            
            // For new rows, default to global setting
            const shouldBeChecked = data ? (data.require_note == 1) : isGlobalNoteOn;

            tr.innerHTML = `
                ${data ? `<input type="hidden" name="variants[${index}][id]" value="${data.id}">` : ''}
                <td>
                    <input type="text" name="variants[${index}][name]" class="form-control form-control-sm" placeholder="VD: Gói 1 Tháng" required value="${data ? data.name : ''}">
                </td>
                <td>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text bg-light border-0" style="font-size: 0.7rem;">Gốc</span>
                        <input type="number" name="variants[${index}][price]" class="form-control" placeholder="Giá" required min="0" step="1000" value="${data ? data.price : ''}">
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0" style="font-size: 0.7rem;">Sale</span>
                        <input type="number" name="variants[${index}][sale_price]" class="form-control" placeholder="Trống nếu không sale" min="0" step="1000" value="${data ? (data.sale_price || '') : ''}">
                    </div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <span class="badge bg-light text-dark border fw-normal" style="font-size: 0.7rem;">Tồn: ${data ? (data.stock_quantity || 0) : 0}</span>
                    </div>
                    <input type="number" name="variants[${index}][stock_add]" class="form-control form-control-sm" placeholder="Nạp thêm..." min="0">
                </td>
                <td>
                    <textarea name="variants[${index}][stock_content]" class="form-control form-control-sm" rows="2" placeholder="Nội dung gửi khách khi nạp thêm"></textarea>
                </td>
                <td class="text-center">
                    <div class="form-check form-check-inline m-0">
                        <input class="form-check-input" type="checkbox" name="variants[${index}][require_note]" value="1" ${shouldBeChecked ? 'checked' : ''}>
                    </div>
                </td>
                <td class="text-end">
                    <button type="button" class="btn btn-link text-danger p-0 btn-remove-variant"><i class="fas fa-times-circle"></i></button>
                </td>
            `;
            variantsTableBody.appendChild(tr);

            tr.querySelector('.btn-remove-variant').addEventListener('click', () => {
                tr.remove();
                if (variantsTableBody.children.length === 0) {
                    enableVariants.checked = false;
                    enableVariants.dispatchEvent(new Event('change'));
                }
            });
        }

        if (existingVariants && existingVariants.length > 0) {
            existingVariants.forEach(v => addVariantRow(v));
        }

        btnAddVariant.addEventListener('click', () => addVariantRow());
    })();
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Sửa sản phẩm';
require_once __DIR__ . '/../layouts/seller.php';
?>
