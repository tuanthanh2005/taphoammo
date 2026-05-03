<?php 
ob_start();
?>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4 gy-3 align-items-center">
        <div class="col-md-6 d-none d-md-block">
            <h4 class="fw-bold mb-1"><i class="fas fa-boxes text-primary me-2"></i> Quản lý Sản phẩm</h4>
            <p class="text-muted small mb-0">Quản lý kho hàng, trạng thái và cập nhật thông tin sản phẩm của bạn.</p>
        </div>
        <div class="col-md-6">
            <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end">
                <form action="<?= url('/seller/products') ?>" method="GET" class="d-flex gap-2">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Tìm sản phẩm..." value="<?= e($search ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-primary px-3">Tìm</button>
                </form>
                <a href="<?= url('/seller/products/create') ?>" class="btn btn-success rounded-3 px-4 shadow-sm">
                    <i class="fas fa-plus me-2"></i> Thêm sản phẩm
                </a>
            </div>
        </div>
    </div>

    <!-- Product Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-uppercase small fw-bold text-muted">
                            <th class="ps-4 py-3">Sản phẩm</th>
                            <th>Thông tin</th>
                            <th>Giá bán</th>
                            <th>Kho hàng</th>
                            <th>Trạng thái</th>
                            <th class="pe-4 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="fas fa-box-open fa-3x mb-3 text-muted opacity-25"></i>
                                        <p class="text-muted">Bạn chưa có sản phẩm nào được đăng tải.</p>
                                        <a href="<?= url('/seller/products/create') ?>" class="btn btn-sm btn-primary rounded-pill px-4">Đăng sản phẩm ngay</a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="product-thumb me-3">
                                            <img src="<?= asset($product['thumbnail'] ?? 'images/no-image.png') ?>" 
                                                 alt="<?= e($product['name']) ?>" 
                                                 class="rounded-3 shadow-sm"
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark"><?= e(Helper::truncate($product['name'], 40)) ?></div>
                                            <div class="text-muted small"><?= e($product['category_name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="mb-1"><i class="fas fa-shopping-cart text-muted me-1"></i> <?= $product['total_sold'] ?> đã bán</div>
                                        <div><i class="fas fa-tag text-muted me-1"></i> <?= e($product['product_type']) ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary"><?= money($product['price']) ?></div>
                                    <?php if ($product['sale_price']): ?>
                                        <del class="text-muted small" style="font-size: 11px;"><?= money($product['sale_price']) ?></del>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <span class="badge bg-success-subtle text-success rounded-pill px-3"><?= $product['stock_quantity'] ?> còn lại</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3">Hết hàng</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusConfigs = [
                                        'pending' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning', 'label' => 'Đang chờ duyệt'],
                                        'active' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'label' => 'Đang bán'],
                                        'rejected' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'label' => 'Bị từ chối'],
                                        'hidden' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'label' => 'Đã ẩn (Có đơn hàng)'],
                                        'approved' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'label' => 'Đã duyệt']
                                    ];
                                    $cfg = $statusConfigs[$product['status']] ?? ['bg' => 'bg-light', 'text' => 'text-dark', 'label' => $product['status']];
                                    ?>
                                    <span class="badge rounded-pill <?= $cfg['bg'] ?> <?= $cfg['text'] ?> px-3">
                                        <?= $cfg['label'] ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group shadow-sm rounded-pill overflow-hidden bg-white border">
                                        <a href="<?= url('/seller/products/stock/' . $product['id']) ?>" 
                                           class="btn btn-white btn-sm px-3" title="Quản lý kho">
                                            <i class="fas fa-warehouse text-primary"></i>
                                        </a>
                                        <a href="<?= url('/seller/products/edit/' . $product['id']) ?>" 
                                           class="btn btn-white btn-sm px-3 border-start" title="Chỉnh sửa">
                                            <i class="fas fa-edit text-warning"></i>
                                        </a>
                                        <button type="button" class="btn btn-white btn-sm px-3 border-start" 
                                                onclick="confirmDelete(<?= $product['id'] ?>, '<?= e($product['name']) ?>')" title="Xóa">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </div>
                                    <form id="delete-form-<?= $product['id'] ?>" action="<?= url('/seller/products/delete/' . $product['id']) ?>" method="POST" style="display: none;">
                                        <?= csrf_field() ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (isset($totalPages) && $totalPages > 1): ?>
        <div class="card-footer bg-white border-top py-3">
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link rounded-pill px-3" href="<?= Helper::buildQuery(['page' => $currentPage - 1]) ?>">Trước</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php 
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                    for ($i = $start; $i <= $end; $i++): 
                    ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link rounded-circle mx-1" href="<?= Helper::buildQuery(['page' => $i]) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link rounded-pill px-3" href="<?= Helper::buildQuery(['page' => $currentPage + 1]) ?>">Sau</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id, name) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Lưu ý: Nếu sản phẩm này đã có lịch sử đơn hàng, hệ thống sẽ tự động ẨN sản phẩm thay vì xóa hoàn toàn để giữ dữ liệu đối soát.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Vâng, tôi hiểu!',
        cancelButtonText: 'Hủy',
        customClass: {
            confirmButton: 'rounded-pill px-4',
            cancelButton: 'rounded-pill px-4'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
</script>

<style>
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1); }

.product-thumb img {
    border: 1px solid #f0f0f0;
}

.btn-group .btn-white:hover {
    background-color: #f8f9fa;
}

.table thead th {
    font-size: 11px;
    letter-spacing: 0.5px;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Quản lý Sản phẩm';
require_once __DIR__ . '/../layouts/seller.php';
?>
