<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-balance-scale"></i> Khiếu nại của tôi</h2>
        <a href="<?= url('/user/orders') ?>" class="btn btn-outline-primary">
            <i class="fas fa-shopping-bag me-1"></i> Đơn hàng của tôi
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Mã khiếu nại</th>
                            <th>Đơn hàng</th>
                            <th>Người bán</th>
                            <th>Lý do</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($disputes)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-balance-scale fa-3x mb-3 opacity-25"></i>
                                <p>Bạn chưa có khiếu nại nào.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($disputes as $d): ?>
                        <tr>
                            <td class="fw-bold text-primary">#<?= e($d['dispute_code']) ?></td>
                            <td>
                                <a href="<?= url('/user/orders/' . $d['order_id']) ?>" class="text-decoration-none fw-bold">
                                    #<?= e($d['order_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold"><?= e($d['seller_name']) ?></div>
                                <div class="small text-muted">@<?= e($d['seller_username']) ?></div>
                            </td>
                            <td>
                                <?php
                                $reasonMap = [
                                    'not_received' => 'Chưa nhận được hàng',
                                    'wrong_item' => 'Sai sản phẩm',
                                    'not_working' => 'Sản phẩm lỗi/không hoạt động',
                                    'scam' => 'Lừa đảo',
                                    'other' => 'Lý do khác'
                                ];
                                echo $reasonMap[$d['reason']] ?? 'Khác';
                                ?>
                            </td>
                            <td class="fw-bold text-danger"><?= money($d['amount']) ?></td>
                            <td>
                                <?php
                                $statusMap = [
                                    'open' => ['danger', 'Đã gửi'],
                                    'under_review' => ['warning', 'Đang xử lý'],
                                    'resolved_refund' => ['success', 'Đã hoàn tiền'],
                                    'resolved_partial' => ['info', 'Hoàn tiền 1 phần'],
                                    'resolved_rejected' => ['secondary', 'Đã từ chối'],
                                    'closed' => ['dark', 'Đã đóng']
                                ];
                                [$color, $label] = $statusMap[$d['status']] ?? ['primary', $d['status']];
                                ?>
                                <span class="badge bg-<?= $color ?>"><?= $label ?></span>
                            </td>
                            <td class="small"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></td>
                            <td>
                                <a href="<?= url('/user/orders/' . $d['order_id']) ?>" class="btn btn-sm btn-outline-primary">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
