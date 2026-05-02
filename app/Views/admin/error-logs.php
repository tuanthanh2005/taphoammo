<?php ob_start(); ?>

<div class="container-fluid py-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h3 class="fw-bold mb-0">Nhật ký lỗi hệ thống</h3>
            <p class="text-muted small">Xem và theo dõi tất cả các lỗi xảy ra trên hệ thống.</p>
        </div>
        <div class="col-md-6 text-md-end">
            <form action="<?= url('/admin/error-logs') ?>" method="GET" class="d-flex justify-content-md-end gap-2">
                <input type="text" name="search" class="form-control form-control-sm rounded-pill px-3" style="width: 250px;" placeholder="Tìm theo lỗi, file, url..." value="<?= e($currentSearch) ?>">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="small fw-bold text-muted">
                            <th class="ps-4">Thời gian</th>
                            <th>Người dùng</th>
                            <th>Lỗi</th>
                            <th>File:Line</th>
                            <th>URL</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-3x mb-3 text-success opacity-25"></i>
                                    <p>Tuyệt vời! Không tìm thấy lỗi nào.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="ps-4 small text-muted">
                                        <?= date('d/m H:i:s', strtotime($log['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($log['user_id']): ?>
                                            <span class="fw-bold small"><?= e($log['user_name']) ?></span>
                                            <div class="text-muted" style="font-size: 10px;">ID: <?= $log['user_id'] ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">Khách</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="text-danger fw-bold small text-truncate" style="max-width: 300px;" title="<?= e($log['error_message']) ?>">
                                            <?= e($log['error_message']) ?>
                                        </div>
                                        <?php if ($log['error_code']): ?>
                                            <span class="badge bg-light text-dark border" style="font-size: 9px;">Code: <?= e($log['error_code']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <div class="text-muted text-truncate" style="max-width: 200px;" title="<?= e($log['file']) ?>">
                                            <?= basename($log['file']) ?>:<?= $log['line'] ?>
                                        </div>
                                    </td>
                                    <td class="small">
                                        <span class="badge bg-light text-primary border"><?= $log['method'] ?></span>
                                        <span class="text-muted text-truncate" style="max-width: 150px;" title="<?= e($log['url']) ?>"><?= e($log['url']) ?></span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#logModal<?= $log['id'] ?>">
                                            Chi tiết
                                        </button>

                                        <!-- Modal Detail -->
                                        <div class="modal fade" id="logModal<?= $log['id'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content border-0 shadow rounded-4">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title fw-bold">Chi tiết lỗi #<?= $log['id'] ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start">
                                                        <div class="alert alert-danger border-0 rounded-4 mb-4">
                                                            <div class="fw-bold mb-1">Message:</div>
                                                            <div><?= e($log['error_message']) ?></div>
                                                        </div>

                                                        <div class="row g-3 mb-4">
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded-4 border h-100">
                                                                    <div class="small text-muted mb-1">File & Line</div>
                                                                    <div class="small fw-bold text-break"><?= e($log['file']) ?>:<?= $log['line'] ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded-4 border h-100">
                                                                    <div class="small text-muted mb-1">URL & Method</div>
                                                                    <div class="small fw-bold"><?= $log['method'] ?>: <?= e($log['url']) ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded-4 border h-100">
                                                                    <div class="small text-muted mb-1">IP Address</div>
                                                                    <div class="small fw-bold"><?= e($log['ip_address']) ?></div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="p-3 bg-light rounded-4 border h-100">
                                                                    <div class="small text-muted mb-1">User Agent</div>
                                                                    <div class="small fw-bold text-truncate" title="<?= e($log['user_agent']) ?>"><?= e($log['user_agent']) ?></div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-0">
                                                            <label class="form-label fw-bold small text-muted text-uppercase">Stack Trace</label>
                                                            <div class="bg-dark text-light p-3 rounded-4 small overflow-auto" style="max-height: 400px; font-family: monospace; white-space: pre-wrap;">
                                                                <?= e($log['trace']) ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Đóng</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($totalPages > 1): ?>
            <div class="card-footer bg-white border-0 py-3">
                <nav>
                    <ul class="pagination pagination-sm justify-content-center mb-0">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                <a class="page-link rounded-pill mx-1" href="<?= url('/admin/error-logs?page=' . $i . '&search=' . urlencode($currentSearch)) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.table thead th {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.modal-content {
    background-color: #fcfcfc;
}
</style>

<?php
$content = ob_get_clean();
$pageTitle = 'Nhật ký lỗi hệ thống';
require_once __DIR__ . '/../layouts/admin.php';
?>
