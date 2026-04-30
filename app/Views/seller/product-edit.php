<?php ob_start(); ?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Chỉnh sửa sản phẩm</h2>
        <a href="<?= url('/seller/products') ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= url('/seller/products/update/' . $product['id']) ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?= e($product['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả ngắn gọn</label>
                        <textarea name="short_description" class="form-control" rows="3"><?= e($product['short_description']) ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control editor" rows="8"><?= e($product['description']) ?></textarea>
                        <small class="text-muted">Hỗ trợ định dạng HTML</small>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Ảnh đại diện</h6>
                            <?php if ($product['thumbnail']): ?>
                                <div class="mb-2 text-center">
                                    <img src="<?= asset($product['thumbnail']) ?>" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                            <?php endif; ?>
                            <input type="file" name="thumbnail" class="form-control" accept="image/*">
                            <small class="text-muted">Định dạng: JPG, PNG, WEBP. Để trống nếu không muốn đổi ảnh.</small>
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Giá bán</h6>
                            <div class="mb-3">
                                <label class="form-label">Giá gốc (VNĐ) <span class="text-danger">*</span></label>
                                <input type="number" name="price" class="form-control" value="<?= $product['price'] ?>" required min="0" step="1000">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Giá khuyến mãi (VNĐ)</label>
                                <input type="number" name="sale_price" class="form-control" value="<?= $product['sale_price'] ?>" min="0" step="1000">
                                <small class="text-muted">Để trống nếu không giảm giá</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Tùy chọn ghi chú</h6>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="require_note" id="require_note" value="1" <?= ($product['require_note'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="require_note">Bắt buộc nhập ghi chú</label>
                            </div>
                            <div class="form-text small">Nếu bật, người mua phải nhập nội dung (vd: email, link...) mới có thể thanh toán.</div>
                        </div>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Trạng thái</h6>
                            <div class="mb-3">
                                <select name="status" class="form-select">
                                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Đang bán</option>
                                    <option value="hidden" <?= $product['status'] === 'hidden' ? 'selected' : '' ?>>Ẩn (Ngừng bán)</option>
                                    <?php if ($product['status'] === 'pending'): ?>
                                    <option value="pending" selected>Chờ duyệt</option>
                                    <?php endif; ?>
                                    <?php if ($product['status'] === 'rejected'): ?>
                                    <option value="rejected" selected>Bị từ chối</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <hr>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Cập nhật sản phẩm</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Sửa sản phẩm';
require_once __DIR__ . '/../layouts/seller.php';
?>
