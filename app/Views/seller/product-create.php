<?php 
ob_start();
?>

<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fas fa-plus"></i> Thêm sản phẩm mới</h5>
    </div>
    <div class="card-body">
        <form action="<?= url('/seller/products/store') ?>" method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label">Tên sản phẩm *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Danh mục *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">-- Chọn danh mục --</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả ngắn</label>
                        <textarea name="short_description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mô tả chi tiết</label>
                        <textarea name="description" class="form-control" rows="8"></textarea>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label">Ảnh sản phẩm</label>
                        <input type="file" name="thumbnail" class="form-control" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Giá bán *</label>
                        <input type="number" name="price" class="form-control" required min="0" step="1000">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Giá khuyến mãi</label>
                        <input type="number" name="sale_price" class="form-control" min="0" step="1000">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Loại sản phẩm *</label>
                        <select name="product_type" class="form-select" required>
                            <option value="key">Key/License</option>
                            <option value="account">Account</option>
                            <option value="file">File Download</option>
                            <option value="link">Link dịch vụ</option>
                            <option value="service">Dịch vụ thủ công</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="require_note" id="require_note" value="1">
                            <label class="form-check-label fw-bold" for="require_note">Bắt buộc người mua nhập ghi chú</label>
                        </div>
                        <div class="form-text small">Nếu bật, người mua phải nhập nội dung (vd: email, link...) mới có thể thanh toán.</div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Sau khi tạo sản phẩm, bạn cần nhập kho hàng và chờ admin duyệt.
            </div>
            
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save"></i> Tạo sản phẩm
            </button>
            <a href="<?= url('/seller/products') ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Hủy
            </a>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$pageTitle = 'Thêm sản phẩm';
require_once __DIR__ . '/../layouts/seller.php';
?>
