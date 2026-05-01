<?php 
ob_start();
?>

<div class="container-fluid py-3">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold"><i class="fas fa-boxes text-primary me-2"></i> Quản lý sản phẩm</h4>
            <p class="text-muted small">Duyệt, tìm kiếm và quản lý sản phẩm của tất cả seller trên sàn.</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-bold">Tìm kiếm</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tên sản phẩm, ID hoặc tên seller..." value="<?= e($currentSearch ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="all" <?= ($currentStatus ?? 'all') === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                        <option value="pending" <?= ($currentStatus ?? '') === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="approved" <?= ($currentStatus ?? '') === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="rejected" <?= ($currentStatus ?? '') === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                        <option value="active" <?= ($currentStatus ?? '') === 'active' ? 'selected' : '' ?>>Đang bán</option>
                        <option value="hidden" <?= ($currentStatus ?? '') === 'hidden' ? 'selected' : '' ?>>Đang ẩn</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-3">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                </div>
                <?php if (!empty($currentSearch) || ($currentStatus ?? 'all') !== 'all'): ?>
                    <div class="col-md-2">
                        <a href="<?= url('/admin/products') ?>" class="btn btn-light w-100 rounded-3 text-muted">Xóa lọc</a>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="ps-4 py-3">ID</th>
                            <th>Sản phẩm</th>
                            <th>Người bán</th>
                            <th>Giá & Kho</th>
                            <th>Đã bán</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-box-open fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Không tìm thấy sản phẩm nào phù hợp.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-muted small cursor-pointer" onclick="copyToClipboard('<?= $product['id'] ?>')" title="Bấm để sao chép ID">
                                        #<?= $product['id'] ?> <i class="far fa-copy ms-1 opacity-50"></i>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-3 overflow-hidden border bg-light me-3" style="width: 50px; height: 50px; flex-shrink: 0;">
                                            <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" class="w-100 h-100" style="object-fit: cover;">
                                        </div>
                                        <div style="max-width: 300px;">
                                            <div class="fw-bold text-dark text-truncate" title="<?= e($product['name']) ?>"><?= e($product['name']) ?></div>
                                            <div class="small text-muted"><?= e($product['category_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 24px; height: 24px; font-size: 10px;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="small fw-semibold"><?= e($product['seller_name']) ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= money($product['price']) ?></div>
                                    <div class="small text-muted">Kho: <span class="badge bg-light text-dark border fw-normal"><?= $product['stock_quantity'] ?></span></div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= $product['total_sold'] ?? 0 ?></div>
                                    <div class="small text-muted">đơn hàng</div>
                                </td>
                                <td>
                                    <?php
                                    $statusConfig = [
                                        'pending' => ['class' => 'bg-warning-subtle text-warning border-warning', 'text' => 'Chờ duyệt'],
                                        'approved' => ['class' => 'bg-success-subtle text-success border-success', 'text' => 'Đã duyệt'],
                                        'rejected' => ['class' => 'bg-danger-subtle text-danger border-danger', 'text' => 'Từ chối'],
                                        'active' => ['class' => 'bg-success-subtle text-success border-success', 'text' => 'Đang bán'],
                                        'hidden' => ['class' => 'bg-secondary-subtle text-secondary border-secondary', 'text' => 'Đang ẩn']
                                    ];
                                    $config = $statusConfig[$product['status']] ?? ['class' => 'bg-light text-dark', 'text' => $product['status']];
                                    ?>
                                    <span class="badge rounded-pill border px-3 py-2 <?= $config['class'] ?>" style="font-weight: 500;">
                                        <?= $config['text'] ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($product['status'] !== 'approved' && $product['status'] !== 'active'): ?>
                                            <form action="<?= url('/admin/products/approve/' . $product['id']) ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-white border shadow-none" onclick="return confirm('Xác nhận duyệt sản phẩm này?')" title="Duyệt">
                                                    <i class="fas fa-check text-success"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['status'] !== 'rejected'): ?>
                                            <form action="<?= url('/admin/products/reject/' . $product['id']) ?>" method="POST" class="d-inline">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-white border shadow-none" onclick="return confirm('Từ chối sản phẩm này?')" title="Từ chối">
                                                    <i class="fas fa-times text-danger"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="<?= url('/product/' . $product['slug']) ?>" target="_blank" class="btn btn-white border shadow-none" title="Xem trên sàn">
                                            <i class="fas fa-eye text-dark"></i>
                                        </a>
                                        <button class="btn btn-white border shadow-none" onclick="copyToClipboard('<?= $product['id'] ?>')" title="Copy ID để Tài trợ">
                                            <i class="fas fa-star text-warning"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if (!empty($products) && count($products) >= 50): ?>
            <div class="card-footer bg-white py-3 border-top">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>&status=<?= $currentStatus ?>&search=<?= urlencode($currentSearch) ?>">Trước</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#"><?= $currentPage ?></a></li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $currentPage + 1 ?>&status=<?= $currentStatus ?>&search=<?= urlencode($currentSearch) ?>">Tiếp theo</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        // Show a small toast or just alert
        const toast = document.createElement('div');
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.background = '#0d6efd';
        toast.style.color = '#fff';
        toast.style.padding = '10px 20px';
        toast.style.borderRadius = '30px';
        toast.style.zIndex = '9999';
        toast.innerHTML = '<i class="fas fa-check-circle me-2"></i> Đã copy ID: ' + text;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>

<style>
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
.btn-white { background: #fff; color: #444; }
.btn-white:hover { background: #f8fafc; color: #000; }
.cursor-pointer { cursor: pointer; }
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý sản phẩm';
require_once __DIR__ . '/../layouts/admin.php';
?>
