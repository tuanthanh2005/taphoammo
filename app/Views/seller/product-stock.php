<?php
ob_start();
$isFileMode = ($categoryProfile['stock_mode'] ?? 'lines') === 'file';
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <h4 class="fw-bold mb-1"><i class="fas fa-warehouse text-primary me-2"></i> Quản lý Kho hàng</h4>
            <p class="text-muted small mb-0">Sản phẩm: <span class="fw-bold text-dark"><?= e($product['name']) ?></span></p>
        </div>
        <div class="col-md-5 text-md-end mt-3 mt-md-0">
            <div class="d-inline-flex gap-3">
                <div class="text-center px-3 border-end">
                    <div class="small text-muted">Tồn kho</div>
                    <div class="fw-bold text-success h5 mb-0"><?= $product['stock_quantity'] ?></div>
                </div>
                <div class="text-center px-3">
                    <div class="small text-muted">Đã bán</div>
                    <div class="fw-bold text-dark h5 mb-0"><?= $product['total_sold'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4">
        <!-- Import Form -->
        <div class="col-xl-5">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="stats-icon bg-primary-subtle text-primary rounded-circle p-3 me-3">
                            <i class="fas <?= $isFileMode ? 'fa-file-upload' : 'fa-list-ol' ?>"></i>
                        </div>
                        <h5 class="mb-0 fw-bold"><?= $isFileMode ? 'Tải File Sản Phẩm' : 'Nhập Dữ Liệu Hàng Loạt' ?></h5>
                    </div>

                    <form action="<?= url('/seller/products/stock/import') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                        <?php if ($isFileMode): ?>
                            <div class="mb-4">
                                <label class="form-label fw-bold small">Chọn file để bán</label>
                                <div class="upload-zone p-4 border border-2 border-dashed rounded-4 text-center bg-light">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <input type="file" name="stock_files[]" class="form-control" multiple required>
                                    <div class="form-text mt-2 small">Hỗ trợ ZIP, PDF, RAR... Mỗi file là 1 sản phẩm.</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <ul class="nav nav-pills mb-3 bg-light p-1 rounded-3" id="importTab" role="tablist">
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link active w-100 rounded-2 py-2 small fw-bold" id="list-tab" data-bs-toggle="pill" data-bs-target="#list-import" type="button" onclick="document.getElementById('import_type').value='list'">Nhập danh sách</button>
                                </li>
                                <li class="nav-item flex-fill" role="presentation">
                                    <button class="nav-link w-100 rounded-2 py-2 small fw-bold" id="quantity-tab" data-bs-toggle="pill" data-bs-target="#quantity-import" type="button" onclick="document.getElementById('import_type').value='quantity'">Nhập số lượng</button>
                                </li>
                            </ul>
                            <input type="hidden" name="import_type" id="import_type" value="list">

                            <div class="tab-content" id="importTabContent">
                                <div class="tab-pane fade show active" id="list-import">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small"><?= e($categoryProfile['stock_label'] ?? 'Nội dung bàn giao cho khách') ?></label>
                                        <textarea name="stock_content" class="form-control rounded-3" rows="8" 
                                                  placeholder="VD:&#10;Key-XXXX-XXXX-XXXX&#10;Key-YYYY-YYYY-YYYY"></textarea>
                                        <div class="form-text mt-2 small">
                                            <i class="fas fa-info-circle me-1"></i> <strong>Quy tắc:</strong> Mỗi dòng tương ứng với 1 sản phẩm.
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="quantity-import">
                                    <div class="mb-4">
                                        <label class="form-label fw-bold small">Số lượng cần thêm</label>
                                        <input type="number" name="stock_quantity" class="form-control rounded-3 py-2" min="1" max="1000" placeholder="Nhập số lượng, ví dụ: 7">
                                        <div class="form-text mt-2 small">
                                            <i class="fas fa-info-circle me-1"></i> Sử dụng khi sản phẩm cần bàn giao thủ công qua Chat/Email.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="alert bg-warning-subtle border-0 rounded-4 p-3 mb-4">
                            <div class="d-flex">
                                <i class="fas fa-shield-alt text-warning mt-1 me-3"></i>
                                <div class="small">
                                    <strong class="d-block mb-1 text-warning-emphasis">Cơ chế bảo vệ khách hàng:</strong>
                                    Khi nhập kho, hệ thống sẽ tạm giữ <strong>100% giá trị</strong> từ số dư ví của bạn làm bảo đảm. Tiền sẽ được hoàn lại ngay khi khách hàng mua sản phẩm này.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-bold shadow-sm">
                            <i class="fas fa-plus-circle me-1"></i> XÁC NHẬN NHẬP KHO
                        </button>
                    </form>
                </div>
            </div>

            <!-- Guidelines Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-dark text-white overflow-hidden">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i> Mẹo dành cho Seller</h6>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2 d-flex">
                            <i class="fas fa-check text-success me-2 mt-1"></i>
                            <span>Nếu bán tài khoản, hãy nhập định dạng: <code>user|pass</code> hoặc <code>user:pass</code>.</span>
                        </li>
                        <li class="mb-2 d-flex">
                            <i class="fas fa-check text-success me-2 mt-1"></i>
                            <span>Hệ thống tự động tách dòng, đừng lo lắng về khoảng trắng.</span>
                        </li>
                        <li class="d-flex">
                            <i class="fas fa-check text-success me-2 mt-1"></i>
                            <span>Đảm bảo nội dung bàn giao là duy nhất để tránh tranh chấp.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Stock List -->
        <div class="col-xl-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list-ul text-primary me-2"></i> Danh sách trong kho</h5>
                    <span class="badge bg-light text-dark border rounded-pill px-3"><?= count($stocks) ?> bản ghi</span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($stocks)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
                            <p>Chưa có dữ liệu trong kho sản phẩm này.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr class="text-uppercase small fw-bold text-muted">
                                        <th class="ps-4">Nội dung</th>
                                        <th>Trạng thái</th>
                                        <th class="pe-4 text-end">Thời gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stocks as $stock): ?>
                                        <?php $parsed = Helper::parseStockContent($stock['content']); ?>
                                        <tr>
                                            <td class="ps-4">
                                                <?php if ($stock['status'] !== 'available'): ?>
                                                    <span class="text-muted small italic text-decoration-line-through">Dữ liệu đã bàn giao cho khách</span>
                                                <?php elseif ($parsed['type'] === 'file'): ?>
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                        <i class="fas fa-file-download me-1"></i> <?= e($parsed['display_text']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <code class="bg-light p-1 rounded"><?= e(Helper::truncate($parsed['display_text'], 40)) ?></code>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($stock['status'] === 'available'): ?>
                                                    <span class="badge bg-success-subtle text-success rounded-pill px-3">Sẵn sàng</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">Đã bán</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="pe-4 text-end text-muted small">
                                                <?= date('H:i d/m/Y', strtotime($stock['created_at'])) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="<?= url('/seller/products') ?>" class="btn btn-light rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Quay lại danh sách
        </a>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }

.stats-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.table thead th {
    font-size: 11px;
    letter-spacing: 0.5px;
}

.upload-zone {
    transition: all 0.3s;
    cursor: pointer;
}
.upload-zone:hover {
    border-color: #0d6efd !important;
    background-color: #f0f7ff !important;
}

code { font-size: 13px; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý kho hàng';
require_once __DIR__ . '/../layouts/seller.php';
?>
