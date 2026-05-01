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

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Giá gốc *</label>
                        <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required min="0" step="1000">
                    </div>

                    <div>
                        <label class="form-label fw-semibold">Giá khuyến mãi</label>
                        <input type="number" name="sale_price" class="form-control" value="<?= $product['sale_price'] ?>" min="0" step="1000">
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

                    <a href="<?= url('/seller/products/stock/' . $product['id']) ?>" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fas fa-warehouse me-1"></i> Mở quản lý kho
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
    })();
</script>

<?php
$content = ob_get_clean();
$pageTitle = 'Sửa sản phẩm';
require_once __DIR__ . '/../layouts/seller.php';
?>
